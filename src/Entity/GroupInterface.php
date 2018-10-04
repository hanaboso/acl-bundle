<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
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
     * @return RuleInterface[]|ArrayCollection
     */
    public function getRules();

    /**
     * @param array $rules
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
     * @return UserInterface[]|ArrayCollection
     */
    public function getUsers();

    /**
     * @param UserInterface[] $users
     *
     * @return GroupInterface
     */
    public function setUsers($users): GroupInterface;

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
     * @return UserInterface[]|ArrayCollection
     */
    public function getTmpUsers();

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
    public function setTmpUsers($tmpUsers): GroupInterface;

    /**
     * @return iterable
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
     * @return iterable
     */
    public function getChildren(): iterable;

    /**
     * @param GroupInterface $child
     *
     * @return GroupInterface
     */
    public function addChild(GroupInterface $child): GroupInterface;

    /**
     * @param array  $data
     * @param string $ruleClass
     * @param array  $rules
     *
     * @return GroupInterface
     */
    public function fromArrayAcl(array $data, string $ruleClass, array &$rules): GroupInterface;

    /**
     * @param array $links
     *
     * @return array
     */
    public function toArrayAcl(array &$links): array;

}