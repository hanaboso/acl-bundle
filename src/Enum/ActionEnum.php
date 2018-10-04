<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Enum;

use Hanaboso\CommonsBundle\Enum\EnumAbstract;
use Hanaboso\CommonsBundle\Exception\EnumException;
use LogicException;

/**
 * Class ActionEnum
 *
 * @package Hanaboso\AclBundle\Enum
 */
class ActionEnum extends EnumAbstract
{

    public const READ   = 'read';
    public const WRITE  = 'write';
    public const DELETE = 'delete';

    /**
     * !! Important !!
     * Current limit is at 32 different actions due to int approach
     * Keep Read|Write|Delete in as default actions or rewrite default_action parameter in configuration
     *
     * @var string[]
     */
    protected static $choices = [
        self::READ   => self::READ,
        self::WRITE  => self::WRITE,
        self::DELETE => self::DELETE,
    ];

    /**
     * @var array
     */
    protected static $globalActions = [
        self::WRITE => self::WRITE,
    ];

    /**
     * @param string $action
     *
     * @return int
     * @throws EnumException
     */
    public static function getActionBit(string $action): int
    {
        self::isValid($action);

        $i = 0;
        foreach (static::$choices as $act) {
            if ($act === $action) {
                if ($i > 31) {
                    throw new LogicException('Amount of actions exceeded 32.');
                }

                return $i;
            }
            $i++;
        }

        throw new LogicException('Missing action.');
    }

    /**
     * @return array
     */
    public static function getGlobalActions(): array
    {
        return static::$globalActions;
    }

}