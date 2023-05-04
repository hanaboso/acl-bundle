<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Enum;

use Hanaboso\UserBundle\Enum\EnumAbstract;

/**
 * Class PropertyEnum
 *
 * @package Hanaboso\AclBundle\Enum
 */
final class PropertyEnum extends EnumAbstract
{

    // phpcs:disable SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable

    public const OWNER = 'owner';
    public const GROUP = 'group';

    /**
     * @var string[]
     */
    protected static array $choices = [
        self::GROUP => 'Group',
        self::OWNER => 'Owner',
    ];

}
