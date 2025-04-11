<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Enum;

use Hanaboso\UserBundle\Enum\EnumAbstract;

/**
 * Class ResourceEnum
 *
 * @package Hanaboso\AclBundle\Enum
 */
class ResourceEnum extends EnumAbstract
{

    // phpcs:disable SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable

    public const string GROUP    = 'group';
    public const string USER     = 'user';
    public const string TMP_USER = 'tmp_user';
    public const string TOKEN    = 'token';
    public const string FILE     = 'file';
    public const string RULE     = 'rule';

    /**
     * @var string[]
     */
    protected static array $choices = [
        self::FILE     => 'File',
        self::GROUP    => 'Group entity',
        self::RULE     => 'Rule',
        self::TMP_USER => 'TmpUser entity',
        self::TOKEN    => 'Token entity',
        self::USER     => 'User entity',
    ];

}
