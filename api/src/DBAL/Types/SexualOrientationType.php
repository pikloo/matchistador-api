<?php
namespace App\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

final class SexualOrientationType extends AbstractEnumType
{
    public const MALE = 'male';
    public const FEMALE = 'female';
    public const BOTH = 'both';

    protected static $choices = [
        self::MALE => 'male',
        self::FEMALE => 'female',
        self::BOTH => 'both',
    ];
}