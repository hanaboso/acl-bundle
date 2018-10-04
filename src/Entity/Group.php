<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Hanaboso\CommonsBundle\Traits\Entity\IdTrait;
use Hanaboso\UserBundle\Entity\UserInterface;

/**
 * Class Group
 *
 * @package Hanaboso\AclBundle\Entity
 *
 * @ORM\Table(name="`group`")
 * @ORM\Entity(repositoryClass="Hanaboso\AclBundle\Repository\Entity\GroupRepository")
 */
class Group extends EntityAbstract implements GroupInterface
{

    use IdTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @var RuleInterface[]|ArrayCollection|array
     *
     * @ORM\OneToMany(targetEntity="Hanaboso\AclBundle\Entity\Rule", mappedBy="group")
     */
    private $rules = [];

    /**
     * @var UserInterface[]|ArrayCollection|array
     *
     * @ORM\ManyToMany(targetEntity="Hanaboso\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $users = [];

    /**
     * @var UserInterface[]|ArrayCollection|array
     *
     * @ORM\ManyToMany(targetEntity="Hanaboso\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", nullable=true)
     * @ORM\JoinTable(name="group_owner")
     */
    protected $owner = [];

    /**
     * @var UserInterface[]|ArrayCollection|array
     *
     * @ORM\ManyToMany(targetEntity="Hanaboso\UserBundle\Entity\TmpUser")
     * @ORM\JoinColumn(name="tmp_user_id", referencedColumnName="id", nullable=true)
     * @ORM\JoinTable(name="group_tmp_user")
     */
    private $tmpUsers = [];

    /**
     * @var Collection|GroupInterface[]
     *
     * @ORM\ManyToMany(targetEntity="Hanaboso\AclBundle\Entity\Group", inversedBy="children")
     * @ORM\JoinTable(name="group_inheritance",
     *      joinColumns={@ORM\JoinColumn(name="parent_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="child_id", referencedColumnName="id")}
     *      )
     */
    protected $parents;

    /**
     * @var Collection|GroupInterface[]
     *
     * @ORM\ManyToMany(targetEntity="Hanaboso\AclBundle\Entity\Group", mappedBy="parents")
     */
    protected $children;

    /**
     * Group constructor.
     *
     * @param UserInterface|null $owner
     */
    public function __construct(?UserInterface $owner)
    {
        parent::__construct($owner);
        $this->parents  = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
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
        return self::TYPE_ORM;
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
     * @return Group
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
    public function setTmpUsers($tmpUsers): GroupInterface
    {
        $this->tmpUsers = $tmpUsers;

        return $this;
    }

    /**
     * @return iterable
     */
    public function getParents(): iterable
    {
        return $this->parents;
    }

    /**
     * @param GroupInterface $parent
     *
     * @return Group
     */
    public function addParent(GroupInterface $parent): GroupInterface
    {
        if (!$this->parents->contains($parent)) {
            $this->parents->add($parent);
        }

        return $this;
    }

    /**
     * @param GroupInterface $parent
     *
     * @return Group
     */
    public function removeParent(GroupInterface $parent): GroupInterface
    {
        $this->parents->removeElement($parent);

        return $this;
    }

    /**
     * @return iterable
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
     * @param array  $data
     * @param string $ruleClass
     * @param array  $rules
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
            $rule->fromArrayAcl($ruleData);
            $this->addRule($rule);
            $rules[$rule->getId()] = $rule;
        }

        return $this;
    }

    /**
     * @param array $links
     *
     * @return array
     */
    public function toArrayAcl(&$links): array
    {
        $owner = $this->getOwner();
        $rules = [];
        foreach ($this->rules as $rule) {
            $rules[] = $rule->toArrayAcl();
            $links[$rule->getId()] = $rule->getGroup()->getId();
        }

        return [
            self::ID    => $this->id,
            self::LEVEL => $this->level,
            self::NAME  => $this->name,
            self::RULES => $rules,
            self::OWNER => $owner ? $owner->getId() : NULL,
        ];
    }

}