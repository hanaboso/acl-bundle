<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Factory;

use Hanaboso\AclBundle\Enum\PropertyEnum;
use Hanaboso\AclBundle\Exception\AclException;

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
     * @var string
     */
    private $actionEnum;

    /**
     * @var array
     */
    private $allowedActions;

    /**
     * @var string
     */
    private $resourceEnum;

    /**
     * MaskFactory constructor.
     *
     * @param string     $actionEnum
     * @param string     $resourceEnum
     * @param array|null $allowedActions
     */
    public function __construct(string $actionEnum, string $resourceEnum, $allowedActions)
    {
        $this->actionEnum = $actionEnum;
        $this->resourceEnum = $resourceEnum;
        if (!is_array($allowedActions)) {
            $allowedActions = [];
        }

        if (!array_key_exists(self::DEFAULT_ACTIONS, $allowedActions)) {
            $allowedActions[self::DEFAULT_ACTIONS] = ['read', 'write', 'delete'];
        }
        if (!array_key_exists(self::RESOURCE_LIST, $allowedActions)) {
            $allowedActions[self::RESOURCE_LIST] = [];
        }
        $this->allowedActions = $allowedActions;
    }

    /**
     * @param string[] $data
     * @param string   $resource
     *
     * @return int
     * @throws AclException
     */
    public function maskAction(array $data, string $resource): int
    {
        $mask = 0;
        foreach ($data as $name => $allow) {
            if (boolval($allow) && $this->isActionAllowed($name, $resource)) {
                $bit  = $this->actionEnum::getActionBit($name);
                $mask |= (1 << $bit);
            }
        }
        if ($mask === 0) {
            throw new AclException(
                'Sent mask resulted in 0 value.',
                AclException::ZERO_MASK
            );
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
                $bit  = $this->actionEnum::getActionBit($rule);
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
        if (in_array($action, $this->allowedActions[self::DEFAULT_ACTIONS])) {
            return TRUE;
        }
        if (array_key_exists($resource, $this->allowedActions[self::RESOURCE_LIST])) {
            return in_array($action, $this->allowedActions[self::RESOURCE_LIST][$resource]);
        }

        return FALSE;
    }

    /**
     * @param bool $fillWithDefaults
     *
     * @return array
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