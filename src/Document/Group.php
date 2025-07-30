<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Hanaboso\AclBundle\Entity\Group as EntityGroup;
use Hanaboso\CommonsBundle\Database\Traits\Document\IdTrait;
use Hanaboso\UserBundle\Document\TmpUser;
use Hanaboso\UserBundle\Document\User;

/**
 * Class Group
 *
 * @package Hanaboso\AclBundle\Document
 */
#[ODM\Document(repositoryClass: 'Hanaboso\AclBundle\Repository\Document\GroupRepository')]
class Group extends DocumentAbstract
{

    use IdTrait;

    /**
     * @var self[]|Collection<int, self>
     */
    #[ODM\ReferenceMany(targetDocument: 'Hanaboso\AclBundle\Document\Group', inversedBy: 'children')]
    protected $parents;

    /**
     * @var self[]|Collection<int, self>
     */
    #[ODM\ReferenceMany(targetDocument: 'Hanaboso\AclBundle\Document\Group', inversedBy: 'parents')]
    protected $children;

    /**
     * @var int
     */
    #[ODM\Field(type: 'int')]
    protected int $level = 999;

    /**
     * @var string
     */
    #[ODM\Field(type: 'string')]
    private string $name;

    /**
     * @var Rule[]|Collection<int, Rule>
     */
    #[ODM\ReferenceMany(strategy: 'set', targetDocument: 'Hanaboso\AclBundle\Document\Rule')]
    private $rules;

    /**
     * @var User[]|Collection<int, User>
     */
    #[ODM\ReferenceMany(strategy: 'set', targetDocument: 'Hanaboso\UserBundle\Document\User')]
    private $users;

    /**
     * @var TmpUser[]|Collection<int, TmpUser>
     */
    #[ODM\ReferenceMany(strategy: 'set', targetDocument: 'Hanaboso\UserBundle\Document\TmpUser')]
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
        return EntityGroup::TYPE_ODM;
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
            $group->addChild($this);
        }

        return $this;
    }

    /**
     * @param self $group
     *
     * @return self
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
        $this->id    = $data[EntityGroup::ID];
        $this->name  = $data[EntityGroup::NAME];
        $this->level = $data[EntityGroup::LEVEL];
        foreach ($data[EntityGroup::RULES] as $ruleData) {
            /** @var Rule $rule */
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
            EntityGroup::ID    => $this->id,
            EntityGroup::LEVEL => $this->level,
            EntityGroup::NAME  => $this->name,
            EntityGroup::OWNER => $owner?->getId(),
            EntityGroup::RULES => $rules,
        ];
    }

}
