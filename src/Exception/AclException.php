<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Exception;

use Hanaboso\Utils\Exception\PipesFrameworkExceptionAbstract;

/**
 * Class AclException
 *
 * @package Hanaboso\AclBundle\Exception
 */
final class AclException extends PipesFrameworkExceptionAbstract
{

    public const int MISSING_DATA          = self::OFFSET + 1;
    public const int ZERO_MASK             = self::OFFSET + 2;
    public const int MISSING_DEFAULT_RULES = self::OFFSET + 3;
    public const int PERMISSION            = self::OFFSET + 4;
    public const int INVALID_RESOURCE      = self::OFFSET + 5;
    public const int INVALID_ACTION        = self::OFFSET + 6;
    public const int GROUP_NOT_FOUND       = self::OFFSET + 7;

    protected const int OFFSET = 2_100;

}
