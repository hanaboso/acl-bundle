<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Factory;

use Hanaboso\AclBundle\Enum\PropertyEnum;
use Hanaboso\AclBundle\Exception\AclException;
use LogicException;

/**
 * Class MaskFactory
 *
 * @package Hanaboso\AclBundle\Factory
 */
class MaskFactory
{

    public const DEFAULT_ACTIONS = 'default_actions';
    public const RESOURCE_LIST   = 'resources';

    /**
     * MaskFactory constructor.
     *
     * @param string  $actionEnum
     * @param string  $resourceEnum
     * @param mixed[] $allowedActions
     */
    public function __construct(
        private string $actionEnum,
        private string $resourceEnum,
        private array $allowedActions = []
    )
    {
        $cloneAllowedActions = $allowedActions;
        if (!array_key_exists(self::DEFAULT_ACTIONS, $cloneAllowedActions)) {
            $cloneAllowedActions[self::DEFAULT_ACTIONS] = ['read', 'write', 'delete'];
        }
        if (!array_key_exists(self::RESOURCE_LIST, $cloneAllowedActions)) {
            $cloneAllowedActions[self::RESOURCE_LIST] = [];
        }

        $this->allowedActions = $cloneAllowedActions;
    }

    /**
     * @param mixed[] $data
     * @param string  $resource
     *
     * @return int
     * @throws AclException
     */
    public function maskAction(array $data, string $resource): int
    {
        $mask = 0;
        foreach ($data as $name => $allow) {
            /** @var string $actName */
            $actName   = $name;
            $isAllowed = TRUE;

            if (is_string($name)) {
                $isAllowed = $allow;
            } else {
                $actName = $allow;
            }

            if ($isAllowed && $this->isActionAllowed($actName, $resource)) {
                $bit   = $this->actionEnum::getActionBit($actName);
                $mask |= (1 << $bit);
            }
        }
        if ($mask === 0) {
            throw new AclException('Sent mask resulted in 0 value.', AclException::ZERO_MASK);
        }

        return $mask;
    }

    /**
     * @param string[] $rules
     * @param string   $resource
     *
     * @return int
     */
    public function maskActionFromYmlArray(array $rules, string $resource): int
    {
        $mask = 0;
        foreach ($rules as $rule) {
            if ($this->isActionAllowed($rule, $resource)) {
                $bit   = $this->actionEnum::getActionBit($rule);
                $mask |= (1 << $bit);
            }
        }

        return $mask;
    }

    /**
     * @param string $action
     * @param string $resource
     *
     * @return bool
     */
    public function isActionAllowed(string $action, string $resource): bool
    {
        if (in_array($action, $this->allowedActions[self::DEFAULT_ACTIONS], TRUE)) {
            return TRUE;
        }
        if (array_key_exists($resource, $this->allowedActions[self::RESOURCE_LIST])) {
            return in_array($action, $this->allowedActions[self::RESOURCE_LIST][$resource], TRUE);
        }

        return FALSE;
    }

    /**
     * @param bool $fillWithDefaults
     *
     * @return mixed[]
     */
    public function getAllowedList(bool $fillWithDefaults = TRUE): array
    {
        if (!$fillWithDefaults) {
            return $this->allowedActions;
        }

        $res = [];
        $def = $this->allowedActions[self::DEFAULT_ACTIONS];
        foreach (array_keys($this->resourceEnum::getChoices()) as $resource) {
            $res[$resource] = array_merge($def, $this->allowedActions[self::RESOURCE_LIST][$resource] ?? []);
        }

        return $res;
    }

    /**
     * @param int $mask
     *
     * @return string[]
     */
    public function getActionsFromMask(int $mask): array
    {
        return $this->getActionsFromMaskStatic($mask, $this->actionEnum::getChoices());
    }

    /**
     * @param mixed[] $data
     *
     * @return int
     * @throws AclException
     */
    public static function maskProperty(array $data): int
    {
        if (!isset($data[PropertyEnum::OWNER]) || !isset($data[PropertyEnum::GROUP])) {
            throw new AclException('Missing data', AclException::MISSING_DATA);
        }

        $mask = $data[PropertyEnum::GROUP] ? 2 : ($data[PropertyEnum::OWNER] ? 1 : 0);
        if ($mask === 0) {
            throw new AclException('Sent mask has no value', AclException::ZERO_MASK);
        }

        return $mask;
    }

    /**
     * @param int $mask
     *
     * @return string
     */
    public static function getPropertyFromMask(int $mask): string
    {
        if ($mask === 1) {
            return PropertyEnum::OWNER;
        } else {
            if ($mask === 2) {
                return PropertyEnum::GROUP;
            }
        }

        throw new LogicException('Given mask must be either 1 or 2.');
    }

    /**
     * @param int     $mask
     * @param mixed[] $enum
     *
     * @return mixed[]
     */
    public static function getActionsFromMaskStatic(int $mask, array $enum): array
    {
        $choices = array_keys($enum);
        $limit   = count($choices);
        $res     = [];

        for ($i = 0; $i < $limit; $i++) {
            if (($mask >> $i) & 1) {
                $res[] = $choices[$i];
            }
        }

        return $res;
    }

}
