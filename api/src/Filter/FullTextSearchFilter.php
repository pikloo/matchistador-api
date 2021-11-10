<?php


namespace App\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use Doctrine\ORM\QueryBuilder;

/**
 * inspired from :
 *      - https://gist.github.com/masseelch/47931f3a745409f8f44c69efa9ecb05c
 *      - https://gist.github.com/renta/b6ece3fec7896440fe52a9ec0e76571a
 *      - https://gist.github.com/masacc/94df641b3cb9814cbdaeb3f158d2e1f7
 *
 * how to use :
 *     - add classAnnotation :
 *         ApiFilter(FullTextSearchFilter::class, properties={
 *             "search_example1"={
 *                 "property1": "partial",
 *                 "property2": "exact"
 *             },
 *             "search_example2"={
 *                 "property1": "partial",
 *                 "property3": "partial"
 *             }
 *         })
 *     - use filter in query string as:
 *          + `/api/myresources?search_example1=String%20with%20spaces` => this will search "String with spaces"
 *          + `/api/myresources?search_example1%5B%5D=String%20with%20spaces` => this will search "String with spaces"
 *          + `/api/myresources?search_example1%5B%5D=String&search_example1%5B%5D=with&search_example1%5B%5D=spaces` => this will search "String" or "with" or "spaces"
 */
class FullTextSearchFilter extends SearchFilter
{
    private const PROPERTY_NAME_PREFIX = 'search_';

    /**
     * {@inheritdoc}
     */
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null): void
    {
        if (!str_starts_with($property, self::PROPERTY_NAME_PREFIX)) {
            return;
        }

        if (!isset($this->properties[$property])) {
            return;
        }

        $values = $this->normalizeValues((array) $value, $property);
        if (null === $values) {
            return;
        }

        $orExpressions = [];
        
        foreach ($values as $value) {
            foreach ($this->properties[$property] as $propertyName => $strategy) {
                $strategy = $strategy ?? self::STRATEGY_EXACT;
                $alias = $queryBuilder->getRootAliases()[0];
                $field = $propertyName;

                $associations = [];
                if ($this->isPropertyNested($propertyName, $resourceClass)) {
                    [$alias, $field, $associations] = $this->addJoinsForNestedProperty($propertyName, $alias, $queryBuilder, $queryNameGenerator, $resourceClass);
                }

                $caseSensitive = true;
                $metadata = $this->getNestedMetadata($resourceClass, $associations);

                if ($metadata->hasField($field)) {
                    if ('id' === $field) {
                        $value = $this->getIdFromValue($value);
                    }

                    if (!$this->hasValidValues((array)$value, $this->getDoctrineFieldType($propertyName, $resourceClass))) {
                        $this->logger->notice('Invalid filter ignored', [
                            'exception' => new InvalidArgumentException(sprintf('Values for field "%s" are not valid according to the doctrine type.', $field)),
                        ]);
                        continue;
                    }

                    // prefixing the strategy with i makes it case insensitive
                    if (str_starts_with($strategy, 'i')) {
                        $strategy = substr($strategy, 1);
                        $caseSensitive = false;
                    }

                    $orExpressions[] = $this->addWhereByStrategy($strategy, $queryBuilder, $queryNameGenerator, $alias, $field, $value, $caseSensitive);
                }
            }
        }

        $queryBuilder->andWhere($queryBuilder->expr()->orX(...$orExpressions));
    }

    /**
     * {@inheritDoc}
     * @return Comparison|Orx|void
     */
    protected function addWhereByStrategy(string $strategy, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $alias, string $field, $value, bool $caseSensitive)
    {
        $wrapCase = $this->createWrapCase($caseSensitive);
        $valueParameter = $queryNameGenerator->generateParameterName($field);
        $exprBuilder = $queryBuilder->expr();

        $queryBuilder->setParameter($valueParameter, $value);

        return match ($strategy) {
            null, self::STRATEGY_EXACT => $exprBuilder->eq($wrapCase("$alias.$field"), $wrapCase(":$valueParameter")),
            self::STRATEGY_PARTIAL => $exprBuilder->like($wrapCase("$alias.$field"), $exprBuilder->concat("'%'", $wrapCase(":$valueParameter"), "'%'")),
            self::STRATEGY_START => $exprBuilder->like($wrapCase("$alias.$field"), $exprBuilder->concat($wrapCase(":$valueParameter"), "'%'")),
            self::STRATEGY_END => $exprBuilder->like($wrapCase("$alias.$field"), $exprBuilder->concat("'%'", $wrapCase(":$valueParameter"))),
            self::STRATEGY_WORD_START => $exprBuilder->orX(
                $exprBuilder->like($wrapCase("$alias.$field"), $exprBuilder->concat($wrapCase(":$valueParameter"), "'%'")),
                $exprBuilder->like($wrapCase("$alias.$field"), $exprBuilder->concat("'%'", $wrapCase(":$valueParameter")))
            ),
            default => throw new InvalidArgumentException(sprintf('strategy %s does not exist.', $strategy)),
        };
    }

    /**
     * {@inheritdoc}
     * @return array<string, array<string, mixed>>
     */
    public function getDescription(string $resourceClass): array
    {
        $descriptions = [];

        foreach ($this->properties as $filterName => $properties) {
            $propertyNames = [];

            foreach ($properties as $property => $strategy) {
                if (!$this->isPropertyMapped($property, $resourceClass, true)) {
                    continue;
                }

                $propertyNames[] = $this->normalizePropertyName($property);
            }

            $filterParameterName = $filterName . '[]';
            $descriptions[$filterParameterName] = [
                'property' => $filterName,
                'type' => 'string',
                'required' => false,
                'is_collection' => true,
                'openapi' => [
                    'description' => 'Search involves the fields: ' . implode(', ', $propertyNames),
                ],
            ];
        }

        return $descriptions;
    }
}