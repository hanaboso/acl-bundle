<?php declare(strict_types=1);

namespace AclBundleTests\testApp;

use Hanaboso\AclBundle\Enum\ActionEnum;

/**
 * Class ExtActionEnum
 *
 * @package AclBundleTests\testApp
 */
final class ExtActionEnum extends ActionEnum
{

    public const string TEST  = 'test';
    public const string TEST2 = 'test2';

    /**
     * @var string[]
     */
    // @codingStandardsIgnoreStart
    protected static array $choices = [
        self::READ   => self::READ,
        self::WRITE  => self::WRITE,
        self::DELETE => self::DELETE,
        self::TEST   => self::TEST,
        self::TEST2  => self::TEST2,
    ];
    // @codingStandardsIgnoreEnd

    /**
     * @var mixed[]
     */
    // @codingStandardsIgnoreStart
    protected static array $globalActions = [
        self::WRITE => self::WRITE,
        self::TEST  => self::TEST,
    ];
    // @codingStandardsIgnoreEnd

}
