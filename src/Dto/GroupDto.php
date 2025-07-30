<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Dto;

use Hanaboso\AclBundle\Document\Group as DmGroup;
use Hanaboso\AclBundle\Document\Rule as DmRule;
use Hanaboso\AclBundle\Entity\Group;
use Hanaboso\AclBundle\Entity\Rule;
use Hanaboso\AclBundle\Exception\AclException;
use Hanaboso\AclBundle\Factory\RuleFactory;
use Hanaboso\UserBundle\Document\User as DmUser;
use Hanaboso\UserBundle\Entity\User;

/**
 * Class GroupDto
 *
 * @package Hanaboso\AclBundle\Dto
 */
final class GroupDto
{

    /**
     * @var User[]|DmUser[]
     */
    private array $users = [];

    /**
     * @var Rule[]|DmRule[]
     */
    private array $rules = [];

    /**
     * GroupDto constructor.
     *
     * @param Group|DmGroup $group
     * @param string|null   $name
     */
    function __construct(private readonly Group|DmGroup $group, private ?string $name = NULL)
    {
    }

    /**
     * @return User[]|DmUser[]
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    /**
     * @param User|DmUser $user
     *
     * @return GroupDto
     */
    public function addUser(User|DmUser $user): self
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * @return Rule[]|DmRule[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * @param string  $ruleClass
     * @param mixed[] $data
     *
     * @return GroupDto
     * @throws AclException
     */
    public function addRule(string $ruleClass, array $data): self
    {
        foreach ($data as $rule) {
            if (!isset($rule['resource']) || !isset($rule['action_mask']) || !isset($rule['property_mask'])) {
                throw new AclException('Missing data in sent rules', AclException::MISSING_DATA);
            }
            $this->rules[] = RuleFactory::createRule(
                $rule[Rule::RESOURCE],
                $this->group,
                $rule[Rule::ACTION_MASK],
                $rule[Rule::PROPERTY_MASK],
                sprintf('%s', $ruleClass),
            );
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return GroupDto
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Group|DmGroup
     */
    public function getGroup(): Group|DmGroup
    {
        return $this->group;
    }

}
