<?php declare(strict_types=1);

namespace AclBundleTests\Unit\Enum;

use Hanaboso\AclBundle\Enum\ActionEnum;

/**
 * Class TestActionEnum
 *
 * @package AclBundleTests\Unit\Enum
 */
final class TestActionEnum extends ActionEnum
{

    public const CONST = 'CONST';

    /**
     * @var string[]
     */
    protected static array $choices = [
        self::CONST => self::CONST,
    ];

    /**
     * @param string $val
     *
     * @return string
     */
    public static function isValid(string $val): string
    {
        return $val;
    }

}
