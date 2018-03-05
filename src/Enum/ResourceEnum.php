<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Enum;

use Hanaboso\CommonsBundle\Enum\EnumAbstract;

/**
 * Class ResourceEnum
 *
 * @package Hanaboso\AclBundle\Enum
 */
class ResourceEnum extends EnumAbstract
{

    public const GROUP    = 'group';
    public const USER     = 'user';
    public const TMP_USER = 'tmp_user';
    public const TOKEN    = 'token';
    public const FILE     = 'file';

    /**
     * @var string[]
     */
    protected static $choices = [
        self::GROUP    => 'Group entity',
        self::USER     => 'User entity',
        self::TMP_USER => 'TmpUser entity',
        self::TOKEN    => 'Token entity',
        self::FILE     => 'File',
    ];

}