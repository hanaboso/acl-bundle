<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Hanaboso\AclBundle\Entity\GroupInterface;
use Hanaboso\AclBundle\Entity\RuleInterface;
use Hanaboso\CommonsBundle\Database\Traits\Document\IdTrait;

/**
 * Class Rule
 *
 * @package Hanaboso\AclBundle\Document
 *
 * @ODM\Document(repositoryClass="Hanaboso\AclBundle\Repository\Document\RuleRepository")
 */
class Rule implements RuleInterface
{

    use IdTrait;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $resource;

    /**
     * @var GroupInterface
     *
     * @ODM\ReferenceOne(targetDocument="Hanaboso\AclBundle\Document\Group")
     */
    private $group;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     */
    private $actionMask;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     */
    private $propertyMask;

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
     * @return RuleInterface
     */
    public function setResource(string $resource): RuleInterface
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * @return GroupInterface
     */
    public function getGroup(): GroupInterface
    {
        return $this->group;
    }

    /**
     * @param GroupInterface $group
     *
     * @return RuleInterface
     */
    public function setGroup(GroupInterface $group): RuleInterface
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
     * @return RuleInterface
     */
    public function setActionMask(int $actionMask): RuleInterface
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
     * @return RuleInterface
     */
    public function setPropertyMask(int $propertyMask): RuleInterface
    {
        $this->propertyMask = $propertyMask;

        return $this;
    }

    /**
     * @param array $data
     *
     * @return RuleInterface
     */
    public function fromArrayAcl(array $data): RuleInterface
    {
        $this->id           = $data[self::ID];
        $this->propertyMask = $data[self::PROPERTY_MASK];
        $this->actionMask   = $data[self::ACTION_MASK];
        $this->resource     = $data[self::RESOURCE];

        return $this;
    }

    /**
     * @return array
     */
    public function toArrayAcl(): array
    {
        return [
            self::ID            => $this->id,
            self::PROPERTY_MASK => $this->propertyMask,
            self::ACTION_MASK   => $this->actionMask,
            self::RESOURCE      => $this->resource,
        ];
    }

}
