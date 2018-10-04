<?php declare(strict_types=1);

namespace Tests\testApp;

use Hanaboso\AclBundle\Enum\ActionEnum;

/**
 * Class ExtActionEnum
 *
 * @package Tests\testApp
 */
class ExtActionEnum extends ActionEnum
{

    public const TEST  = 'test';
    public const TEST2 = 'test2';

    /**
     * @var array
     */
    protected static $choices = [
        self::READ   => self::READ,
        self::WRITE  => self::WRITE,
        self::DELETE => self::DELETE,
        self::TEST   => self::TEST,
        self::TEST2  => self::TEST2,
    ];

    /**
     * @var array
     */
    protected static $globalActions = [
        self::WRITE => self::WRITE,
        self::TEST  => self::TEST,
    ];

}