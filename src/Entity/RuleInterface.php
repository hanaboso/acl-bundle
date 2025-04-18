<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Entity;

/**
 * Interface RuleInterface
 *
 * @package Hanaboso\AclBundle\Entity
 */
interface RuleInterface
{

    public const string ID            = 'id';
    public const string RESOURCE      = 'resource';
    public const string PROPERTY_MASK = 'property_mask';
    public const string ACTION_MASK   = 'action_mask';

    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @return string
     */
    public function getResource(): string;

    /**
     * @param string $resource
     *
     * @return RuleInterface
     */
    public function setResource(string $resource): self;

    /**
     * @return GroupInterface
     */
    public function getGroup(): GroupInterface;

    /**
     * @param GroupInterface $group
     *
     * @return RuleInterface
     */
    public function setGroup(GroupInterface $group): self;

    /**
     * @return int
     */
    public function getActionMask(): int;

    /**
     * @param int $actionMask
     *
     * @return RuleInterface
     */
    public function setActionMask(int $actionMask): self;

    /**
     * @return int
     */
    public function getPropertyMask(): int;

    /**
     * @param int $propertyMask
     *
     * @return RuleInterface
     */
    public function setPropertyMask(int $propertyMask): self;

    /**
     * @param mixed[] $data
     *
     * @return RuleInterface
     */
    public function fromArrayAcl(array $data): self;

    /**
     * @return mixed[]
     */
    public function toArrayAcl(): array;

}
