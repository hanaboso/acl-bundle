<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Dto;

use Hanaboso\AclBundle\Document\Rule;
use Hanaboso\AclBundle\Entity\GroupInterface;
use Hanaboso\AclBundle\Entity\RuleInterface;
use Hanaboso\AclBundle\Exception\AclException;
use Hanaboso\AclBundle\Factory\RuleFactory;
use Hanaboso\UserBundle\Entity\UserInterface;

/**
 * Class GroupDto
 *
 * @package Hanaboso\AclBundle\Dto
 */
final class GroupDto
{

    /**
     * @var UserInterface[]
     */
    private array $users = [];

    /**
     * @var RuleInterface[]
     */
    private array $rules = [];

    /**
     * @var GroupInterface
     */
    private GroupInterface $group;

    /**
     * @var string|null
     */
    private ?string $name = NULL;

    /**
     * GroupDto constructor.
     *
     * @param GroupInterface $group
     * @param string|null    $name
     */
    function __construct(GroupInterface $group, ?string $name = NULL)
    {
        $this->group = $group;
        $this->name  = $name;
    }

    /**
     * @return UserInterface[]
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    /**
     * @param UserInterface $user
     *
     * @return GroupDto
     */
    public function addUser(UserInterface $user): GroupDto
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * @return RuleInterface[]
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
    public function addRule(string $ruleClass, array $data): GroupDto
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
                sprintf('%s', $ruleClass)
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
    public function setName(string $name): GroupDto
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return GroupInterface
     */
    public function getGroup(): GroupInterface
    {
        return $this->group;
    }

}
