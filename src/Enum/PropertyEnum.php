<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Enum;

use Hanaboso\CommonsBundle\Enum\EnumAbstract;

/**
 * Class PropertyEnum
 *
 * @package Hanaboso\AclBundle\Enum
 */
final class PropertyEnum extends EnumAbstract
{

    public const OWNER = 'owner';
    public const GROUP = 'group';

    /**
     * @var string[]
     */
    protected static $choices = [
        self::OWNER => 'Owner',
        self::GROUP => 'Group',
    ];

}