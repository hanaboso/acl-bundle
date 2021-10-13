<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Hanaboso\AclBundle\Entity\GroupInterface;
use Hanaboso\AclBundle\Entity\RuleInterface;
use Hanaboso\CommonsBundle\Database\Traits\Document\IdTrait;
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
     * @var GroupInterface[]|Collection<int, GroupInterface>
     *
     * @ODM\ReferenceMany(targetDocument="Hanaboso\AclBundle\Document\Group", inversedBy="children")
     */
    protected $parents;

    /**
     * @var GroupInterface[]|Collection<int, GroupInterface>
     *
     * @ODM\ReferenceMany(targetDocument="Hanaboso\AclBundle\Document\Group", mappedBy="parents")
     */
    protected $children;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     */
    protected int $level = 999;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private string $name;

    /**
     * @var RuleInterface[]|Collection<int, RuleInterface>
     *
     * @ODM\ReferenceMany(targetDocument="Hanaboso\AclBundle\Document\Rule", strategy="set")
     */
    private $rules;

    /**
     * @var UserInterface[]|Collection<int, UserInterface>
     *
     * @ODM\ReferenceMany(targetDocument="Hanaboso\UserBundle\Document\User", strategy="set")
     */
    private $users;

    /**
     * @var UserInterface[]|Collection<int, UserInterface>
     *
     * @ODM\ReferenceMany(targetDocument="Hanaboso\UserBundle\Document\TmpUser", strategy="set")
     */
    private $tmpUsers;

    /**
     * Group constructor.
     *
     * @param UserInterface|null $owner
     */
    public function __construct(?UserInterface $owner)
    {
        parent::__construct($owner);

        $this->rules    = new ArrayCollection();
        $this->users    = new ArrayCollection();
        $this->tmpUsers = new ArrayCollection();
        $this->parents  = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

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
     * @return RuleInterface[]|Collection<int, RuleInterface>
     */
    public function getRules(): iterable
    {
        return $this->rules;
    }

    /**
     * @param mixed[] $rules
     *
     * @return GroupInterface
     */
    public function setRules(array $rules): GroupInterface
    {
        $this->rules = new ArrayCollection($rules);

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
     * @return UserInterface[]|Collection<int, UserInterface>
     */
    public function getUsers(): iterable
    {
        return $this->users;
    }

    /**
     * @param UserInterface[] $users
     *
     * @return GroupInterface
     */
    public function setUsers(array $users): GroupInterface
    {
        $this->users = new ArrayCollection($users);

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
     * @return UserInterface[]|Collection<int, UserInterface>
     */
    public function getTmpUsers(): iterable
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

    /**
     * @param UserInterface[] $tmpUsers
     *
     * @return GroupInterface
     */
    public function setTmpUsers(array $tmpUsers): GroupInterface
    {
        $this->tmpUsers = new ArrayCollection($tmpUsers);

        return $this;
    }

    /**
     * @return GroupInterface[]|Collection<int, GroupInterface>
     */
    public function getParents(): iterable
    {
        return $this->parents;
    }

    /**
     * @param GroupInterface $group
     *
     * @return Group
     */
    public function addParent(GroupInterface $group): GroupInterface
    {
        if (!$this->parents->contains($group)) {
            $this->parents->add($group);
            $group->addChild($this);
        }

        return $this;
    }

    /**
     * @param GroupInterface $group
     *
     * @return Group
     */
    public function removeParent(GroupInterface $group): GroupInterface
    {
        $this->parents->removeElement($group);

        return $this;
    }

    /**
     * @return GroupInterface[]|Collection<int, GroupInterface>
     */
    public function getChildren(): iterable
    {
        return $this->children;
    }

    /**
     * @param GroupInterface $child
     *
     * @return GroupInterface
     */
    public function addChild(GroupInterface $child): GroupInterface
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->addParent($this);
        }

        return $this;
    }

    /**
     * @param mixed[] $data
     * @param string  $ruleClass
     * @param mixed[] $rules
     *
     * @return GroupInterface
     */
    public function fromArrayAcl(array $data, string $ruleClass, array &$rules): GroupInterface
    {
        $this->id    = $data[self::ID];
        $this->name  = $data[self::NAME];
        $this->level = $data[self::LEVEL];
        foreach ($data[self::RULES] as $ruleData) {
            /** @var RuleInterface $rule */
            $rule = new $ruleClass();
            $rule->fromArrayAcl($ruleData)
                ->setGroup($this);
            $this->addRule($rule);
            $rules[$rule->getId()] = $rule;
        }

        return $this;
    }

    /**
     * @param mixed[] $links
     *
     * @return mixed[]
     */
    public function toArrayAcl(array &$links): array
    {
        $owner = $this->getOwner();
        $rules = [];
        foreach ($this->rules as $rule) {
            $rules[]               = $rule->toArrayAcl();
            $links[$rule->getId()] = $rule->getGroup()->getId();
        }

        return [
            self::ID    => $this->id,
            self::LEVEL => $this->level,
            self::NAME  => $this->name,
            self::RULES => $rules,
            self::OWNER => $owner?->getId(),
        ];
    }

}
