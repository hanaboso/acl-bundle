<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Enum;

use Hanaboso\Utils\Enum\EnumAbstract;

/**
 * Class ResourceEnum
 *
 * @package Hanaboso\AclBundle\Enum
 */
class ResourceEnum extends EnumAbstract
{

    // phpcs:disable SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable

    public const GROUP    = 'group';
    public const USER     = 'user';
    public const TMP_USER = 'tmp_user';
    public const TOKEN    = 'token';
    public const FILE     = 'file';
    public const RULE     = 'rule';

    /**
     * @var string[]
     */
    protected static array $choices = [
        self::GROUP    => 'Group entity',
        self::USER     => 'User entity',
        self::TMP_USER => 'TmpUser entity',
        self::TOKEN    => 'Token entity',
        self::FILE     => 'File',
        self::RULE     => 'Rule',
    ];

}