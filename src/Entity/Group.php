<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Hanaboso\CommonsBundle\Traits\Entity\IdTrait;
use Hanaboso\UserBundle\Entity\TmpUser;
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
     * @var ArrayCollection|TmpUser[]|array
     *
     * @ORM\ManyToMany(targetEntity="Hanaboso\UserBundle\Entity\TmpUser")
     * @ORM\JoinColumn(name="tmp_user_id", referencedColumnName="id", nullable=true)
     * @ORM\JoinTable(name="group_tmp_user")
     */
    private $tmpUsers = [];

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
     * @return array|Collection|array
     */
    public function getTmpUsers()
    {
        return $this->tmpUsers;
    }

    /**
     * @param TmpUser $tmpUser
     *
     * @return Group
     */
    public function addTmpUser(TmpUser $tmpUser): Group
    {
        $this->tmpUsers[] = $tmpUser;

        return $this;
    }

}