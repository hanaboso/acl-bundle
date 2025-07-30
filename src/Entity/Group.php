<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Hanaboso\CommonsBundle\Database\Traits\Entity\IdTrait;
use Hanaboso\UserBundle\Entity\TmpUser;
use Hanaboso\UserBundle\Entity\User;

/**
 * Class Group
 *
 * @package Hanaboso\AclBundle\Entity
 */
#[ORM\Entity(repositoryClass: 'Hanaboso\AclBundle\Repository\Entity\GroupRepository')]
#[ORM\Table(name: '`group`')]
class Group extends EntityAbstract
{

    use IdTrait;

    public const string TYPE_ODM = 'odm';
    public const string TYPE_ORM = 'orm';

    public const string ID    = 'id';
    public const string OWNER = 'owner';
    public const string LEVEL = 'level';
    public const string NAME  = 'name';
    public const string RULES = 'rules';

    /**
     * @var ArrayCollection<int, User>
     */
    #[ORM\JoinTable(name: 'group_owner')]
    #[ORM\JoinColumn(name: 'group_id', referencedColumnName: 'id', nullable: TRUE)]
    #[ORM\ManyToMany(targetEntity: 'Hanaboso\UserBundle\Entity\User')]
    protected $owner;

    /**
     * @var self[]|Collection<int, self>
     */
    #[ORM\JoinTable(
        name: 'group_inheritance',
        joinColumns: [
            new ORM\JoinColumn(
                name: 'parent_id',
                referencedColumnName: 'id',
            ),
        ],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'child_id', referencedColumnName: 'id')],
    )]
    #[ORM\ManyToMany(targetEntity: 'Hanaboso\AclBundle\Entity\Group', inversedBy: 'children')]
    protected $parents;

    /**
     * @var self[]|Collection<int, self>
     */
    #[ORM\ManyToMany(targetEntity: 'Hanaboso\AclBundle\Entity\Group', mappedBy: 'parents')]
    protected $children;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    protected int $level = 999;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string')]
    private string $name;

    /**
     * @var Rule[]|Collection<int, Rule>
     */
    #[ORM\OneToMany(mappedBy: 'group', targetEntity: 'Hanaboso\AclBundle\Entity\Rule')]
    private $rules;

    /**
     * @var User[]|Collection<int, User>
     */
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'Hanaboso\UserBundle\Entity\User')]
    private $users;

    /**
     * @var TmpUser[]|Collection<int, TmpUser>
     */
    #[ORM\JoinTable(name: 'group_tmp_user')]
    #[ORM\JoinColumn(name: 'tmp_user_id', referencedColumnName: 'id', nullable: TRUE)]
    #[ORM\ManyToMany(targetEntity: 'Hanaboso\UserBundle\Entity\TmpUser')]
    private $tmpUsers;

    /**
     * Group constructor.
     *
     * @param User|null $owner
     */
    public function __construct(?User $owner)
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
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Rule[]|Collection<int, Rule>
     */
    public function getRules(): iterable
    {
        return $this->rules;
    }

    /**
     * @param mixed[] $rules
     *
     * @return self
     */
    public function setRules(array $rules): self
    {
        $this->rules = new ArrayCollection($rules);

        return $this;
    }

    /**
     * @param Rule $rule
     *
     * @return self
     */
    public function addRule(Rule $rule): self
    {
        $this->rules[] = $rule;

        return $this;
    }

    /**
     * @return User[]|Collection<int, User>
     */
    public function getUsers(): iterable
    {
        return $this->users;
    }

    /**
     * @param User[] $users
     *
     * @return self
     */
    public function setUsers(array $users): self
    {
        $this->users = new ArrayCollection($users);

        return $this;
    }

    /**
     * @param User $user
     *
     * @return self
     */
    public function addUser(User $user): self
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
     * @return self
     */
    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return TmpUser[]|Collection<int, TmpUser>
     */
    public function getTmpUsers(): iterable
    {
        return $this->tmpUsers;
    }

    /**
     * @param TmpUser $tmpUser
     *
     * @return self
     */
    public function addTmpUser(TmpUser $tmpUser): self
    {
        $this->tmpUsers[] = $tmpUser;

        return $this;
    }

    /**
     * @param TmpUser[] $tmpUsers
     *
     * @return self
     */
    public function setTmpUsers(array $tmpUsers): self
    {
        $this->tmpUsers = new ArrayCollection($tmpUsers);

        return $this;
    }

    /**
     * @return self[]|Collection<int, self>
     */
    public function getParents(): iterable
    {
        return $this->parents;
    }

    /**
     * @param self $group
     *
     * @return Group
     */
    public function addParent(self $group): self
    {
        if (!$this->parents->contains($group)) {
            $this->parents->add($group);
        }

        return $this;
    }

    /**
     * @param self $group
     *
     * @return Group
     */
    public function removeParent(self $group): self
    {
        $this->parents->removeElement($group);

        return $this;
    }

    /**
     * @return self[]|Collection<int, self>
     */
    public function getChildren(): iterable
    {
        return $this->children;
    }

    /**
     * @param self $child
     *
     * @return self
     */
    public function addChild(self $child): self
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
     * @return self
     */
    public function fromArrayAcl(array $data, string $ruleClass, array &$rules): self
    {
        $this->id    = $data[self::ID];
        $this->name  = $data[self::NAME];
        $this->level = $data[self::LEVEL];
        foreach ($data[self::RULES] as $ruleData) {
            /** @var Rule $rule */
            $rule = new $ruleClass();
            $rule->fromArrayAcl($ruleData);
            $this->addRule($rule);
            $rule->setGroup($this);
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
            self::OWNER => $owner?->getId(),
            self::RULES => $rules,
        ];
    }

}
