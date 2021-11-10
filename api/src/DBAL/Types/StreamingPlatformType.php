<?php
namespace App\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

final class StreamingPlatformType extends AbstractEnumType
{
    public const SPOTIFY = 'spotify';
    public const DEEZER = 'deezer';

    protected static $choices = [
        self::SPOTIFY => 'spotify',
        self::DEEZER => 'deezer',
    ];
}