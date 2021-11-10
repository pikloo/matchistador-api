<?php
namespace App\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

final class GenderType extends AbstractEnumType
{
    public const MALE = 'male';
    public const FEMALE = 'female';

    protected static $choices = [
        self::MALE => 'male',
        self::FEMALE => 'female',
    ];
}