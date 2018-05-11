<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Hanaboso\AclBundle\Entity\GroupInterface;
use Hanaboso\AclBundle\Entity\RuleInterface;
use Hanaboso\CommonsBundle\Traits\Document\IdTrait;
use Hanaboso\UserBundle\Entity\UserInterface;

/**
 * Class Group
 *
 * @package Hanaboso\AclBundle\Document
 *
 * @ODM\Document(repositoryClass="Hanaboso\AclBundle\Repository\Document\GroupRepository")
 */
class Group extends DocumentAbstract implements GroupInterface
{

    use IdTrait;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $name;

    /**
     * @var RuleInterface[]|ArrayCollection|array
     *
     * @ODM\ReferenceMany(targetDocument="Hanaboso\AclBundle\Document\Rule", strategy="set")
     */
    private $rules = [];

    /**
     * @var UserInterface[]|ArrayCollection|array
     *
     * @ODM\ReferenceMany(targetDocument="Hanaboso\UserBundle\Document\User", strategy="set")
     */
    private $users = [];

    /**
     * @var UserInterface[]|ArrayCollection|array
     *
     * @ODM\ReferenceMany(targetDocument="Hanaboso\UserBundle\Document\TmpUser", strategy="set")
     */
    private $tmpUsers = [];

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     */
    protected $level = 999;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return GroupInterface
     */
    public function setName(string $name): GroupInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return RuleInterface[]|ArrayCollection|array
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @param array $rules
     *
     * @return GroupInterface
     */
    public function setRules(array $rules): GroupInterface
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * @param RuleInterface $rule
     *
     * @return GroupInterface
     */
    public function addRule(RuleInterface $rule): GroupInterface
    {
        $this->rules[] = $rule;

        return $this;
    }

    /**
     * @return UserInterface[]|ArrayCollection|array
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param UserInterface[] $users
     *
     * @return GroupInterface
     */
    public function setUsers($users): GroupInterface
    {
        $this->users = $users;

        return $this;
    }

    /**
     * @param UserInterface $user
     *
     * @return GroupInterface
     */
    public function addUser(UserInterface $user): GroupInterface
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE_ODM;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param int $level
     *
     * @return GroupInterface
     */
    public function setLevel(int $level): GroupInterface
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return UserInterface[]|ArrayCollection|array
     */
    public function getTmpUsers()
    {
        return $this->tmpUsers;
    }

    /**
     * @param UserInterface $tmpUser
     *
     * @return GroupInterface
     */
    public function addTmpUser(UserInterface $tmpUser): GroupInterface
    {
        $this->tmpUsers[] = $tmpUser;

        return $this;
    }

}