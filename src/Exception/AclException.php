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

    public const MISSING_DATA          = self::OFFSET + 1;
    public const ZERO_MASK             = self::OFFSET + 2;
    public const MISSING_DEFAULT_RULES = self::OFFSET + 3;
    public const PERMISSION            = self::OFFSET + 4;
    public const INVALID_RESOURCE      = self::OFFSET + 5;
    public const INVALID_ACTION        = self::OFFSET + 6;
    public const GROUP_NOT_FOUND       = self::OFFSET + 7;

    protected const OFFSET = 2_100;

}
