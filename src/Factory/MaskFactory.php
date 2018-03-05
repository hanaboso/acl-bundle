<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Factory;

use Hanaboso\AclBundle\Enum\ActionEnum;
use Hanaboso\AclBundle\Enum\PropertyEnum;
use Hanaboso\AclBundle\Exception\AclException;

/**
 * Class MaskFactory
 *
 * @package Hanaboso\AclBundle\Factory
 */
class MaskFactory
{

    /**
     * @param string[] $data
     *
     * @return int
     * @throws AclException
     */
    public static function maskAction(array $data): int
    {
        if (!isset($data[ActionEnum::DELETE]) || !isset($data[ActionEnum::READ]) || !isset($data[ActionEnum::WRITE])
        ) {
            throw new AclException(
                'Missing data',
                AclException::MISSING_DATA
            );
        }

        $mask = boolval($data[ActionEnum::DELETE]) << 2 | boolval($data[ActionEnum::WRITE]) << 1 | boolval($data[ActionEnum::READ]);
        if ($mask === 0) {
            throw new AclException(
                'Sent mask has no value',
                AclException::ZERO_MASK
            );
        }

        return $mask;
    }

    /**
     * @param string[] $rule
     *
     * @return int
     */
    public static function maskActionFromYmlArray(array $rule): int
    {
        return in_array(ActionEnum::DELETE, $rule) << 2 | in_array(ActionEnum::WRITE, $rule) << 1 |
            in_array(ActionEnum::READ, $rule);
    }

    /**
     * @param string $act
     *
     * @return int
     * @throws AclException
     */
    public static function getActionByte(string $act): int
    {
        switch ($act) {
            case ActionEnum::DELETE:
                return 2;
            case ActionEnum::WRITE:
                return 1;
            case ActionEnum::READ:
                return 0;
            default:
                throw new AclException(sprintf('Wrong action type "%s"', $act), AclException::INVALID_ACTION);
        }
    }

    /**
     * @param string[] $data
     *
     * @return int
     * @throws AclException
     */
    public static function maskProperty(array $data): int
    {
        if (!isset($data[PropertyEnum::OWNER]) || !isset($data[PropertyEnum::GROUP])) {
            throw new AclException(
                'Missing data',
                AclException::MISSING_DATA
            );
        }

        $mask = boolval($data[PropertyEnum::GROUP]) ? 2 : (boolval($data[PropertyEnum::OWNER]) ? 1 : 0);
        if ($mask === 0) {
            throw new AclException(
                'Sent mask has no value',
                AclException::ZERO_MASK
            );
        }

        return $mask;
    }

}