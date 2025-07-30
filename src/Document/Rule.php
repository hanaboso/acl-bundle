<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Hanaboso\AclBundle\Entity\Rule as EntityRule;
use Hanaboso\CommonsBundle\Database\Traits\Document\IdTrait;

/**
 * Class Rule
 *
 * @package Hanaboso\AclBundle\Document
 */
#[ODM\Document(repositoryClass: 'Hanaboso\AclBundle\Repository\Document\RuleRepository')]
class Rule
{

    use IdTrait;

    /**
     * @var string
     */
    #[ODM\Field(type: 'string')]
    private string $resource;

    /**
     * @var Group
     */
    #[ODM\ReferenceOne(targetDocument: 'Hanaboso\AclBundle\Document\Group')]
    private Group $group;

    /**
     * @var int
     */
    #[ODM\Field(type: 'int')]
    private int $actionMask = 0;

    /**
     * @var int
     */
    #[ODM\Field(type: 'int')]
    private int $propertyMask = 0;

    /**
     * @return string
     */
    public function getResource(): string
    {
        return $this->resource;
    }

    /**
     * @param string $resource
     *
     * @return self
     */
    public function setResource(string $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * @return Group
     */
    public function getGroup(): Group
    {
        return $this->group;
    }

    /**
     * @param Group $group
     *
     * @return self
     */
    public function setGroup(Group $group): self
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return int
     */
    public function getActionMask(): int
    {
        return $this->actionMask;
    }

    /**
     * @param int $actionMask
     *
     * @return self
     */
    public function setActionMask(int $actionMask): self
    {
        $this->actionMask = $actionMask;

        return $this;
    }

    /**
     * @return int
     */
    public function getPropertyMask(): int
    {
        return $this->propertyMask;
    }

    /**
     * @param int $propertyMask
     *
     * @return self
     */
    public function setPropertyMask(int $propertyMask): self
    {
        $this->propertyMask = $propertyMask;

        return $this;
    }

    /**
     * @param mixed[] $data
     *
     * @return self
     */
    public function fromArrayAcl(array $data): self
    {
        $this->id           = $data[EntityRule::ID];
        $this->propertyMask = $data[EntityRule::PROPERTY_MASK];
        $this->actionMask   = $data[EntityRule::ACTION_MASK];
        $this->resource     = $data[EntityRule::RESOURCE];

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function toArrayAcl(): array
    {
        return [
            EntityRule::ACTION_MASK   => $this->actionMask,
            EntityRule::ID            => $this->id,
            EntityRule::PROPERTY_MASK => $this->propertyMask,
            EntityRule::RESOURCE      => $this->resource,
        ];
    }

}
