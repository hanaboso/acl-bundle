<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Hanaboso\UserBundle\Entity\UserInterface;

/**
 * Interface GroupInterface
 *
 * @package Hanaboso\AclBundle\Entity
 */
interface GroupInterface extends EntityInterface
{

    public const TYPE_ODM = 'odm';
    public const TYPE_ORM = 'orm';

    public const ID    = 'id';
    public const OWNER = 'owner';
    public const LEVEL = 'level';
    public const NAME  = 'name';
    public const RULES = 'rules';

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     *
     * @return GroupInterface
     */
    public function setName(string $name): GroupInterface;

    /**
     * @return RuleInterface[]|Collection<int, RuleInterface>
     */
    public function getRules(): iterable;

    /**
     * @param mixed[] $rules
     *
     * @return GroupInterface
     */
    public function setRules(array $rules): GroupInterface;

    /**
     * @param RuleInterface $rule
     *
     * @return GroupInterface
     */
    public function addRule(RuleInterface $rule): GroupInterface;

    /**
     * @return UserInterface[]|Collection<int, UserInterface>
     */
    public function getUsers(): iterable;

    /**
     * @param UserInterface[] $users
     *
     * @return GroupInterface
     */
    public function setUsers(array $users): GroupInterface;

    /**
     * @param UserInterface $user
     *
     * @return GroupInterface
     */
    public function addUser(UserInterface $user): GroupInterface;

    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @return int
     */
    public function getLevel(): int;

    /**
     * @param int $level
     *
     * @return GroupInterface
     */
    public function setLevel(int $level): GroupInterface;

    /**
     * @return UserInterface[]|Collection<int, UserInterface>
     */
    public function getTmpUsers(): iterable;

    /**
     * @param UserInterface $tmpUser
     *
     * @return GroupInterface
     */
    public function addTmpUser(UserInterface $tmpUser): GroupInterface;

    /**
     * @param UserInterface[] $tmpUsers
     *
     * @return GroupInterface
     */
    public function setTmpUsers(array $tmpUsers): GroupInterface;

    /**
     * @return GroupInterface[]|Collection<int, GroupInterface>
     */
    public function getParents(): iterable;

    /**
     * @param GroupInterface $group
     *
     * @return GroupInterface
     */
    public function addParent(GroupInterface $group): GroupInterface;

    /**
     * @param GroupInterface $group
     *
     * @return GroupInterface
     */
    public function removeParent(GroupInterface $group): GroupInterface;

    /**
     * @return GroupInterface[]|Collection<int, GroupInterface>
     */
    public function getChildren(): iterable;

    /**
     * @param GroupInterface $child
     *
     * @return GroupInterface
     */
    public function addChild(GroupInterface $child): GroupInterface;

    /**
     * @param mixed[] $data
     * @param string  $ruleClass
     * @param mixed[] $rules
     *
     * @return GroupInterface
     */
    public function fromArrayAcl(array $data, string $ruleClass, array &$rules): GroupInterface;

    /**
     * @param mixed[] $links
     *
     * @return mixed[]
     */
    public function toArrayAcl(array &$links): array;

}
