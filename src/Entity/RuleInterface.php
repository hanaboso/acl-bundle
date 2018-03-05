<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Entity;

/**
 * Interface RuleInterface
 *
 * @package Hanaboso\AclBundle\Entity
 */
interface RuleInterface
{

    /**
     * @return string
     */
    public function getResource(): string;

    /**
     * @param string $resource
     *
     * @return RuleInterface
     */
    public function setResource(string $resource): RuleInterface;

    /**
     * @return GroupInterface
     */
    public function getGroup(): GroupInterface;

    /**
     * @param GroupInterface $group
     *
     * @return RuleInterface
     */
    public function setGroup(GroupInterface $group): RuleInterface;

    /**
     * @return int
     */
    public function getActionMask(): int;

    /**
     * @param int $actionMask
     *
     * @return RuleInterface
     */
    public function setActionMask(int $actionMask): RuleInterface;

    /**
     * @return int
     */
    public function getPropertyMask(): int;

    /**
     * @param int $propertyMask
     *
     * @return RuleInterface
     */
    public function setPropertyMask(int $propertyMask): RuleInterface;

}