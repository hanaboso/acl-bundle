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

}