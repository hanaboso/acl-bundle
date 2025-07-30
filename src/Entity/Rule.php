<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Hanaboso\CommonsBundle\Database\Traits\Entity\IdTrait;

/**
 * Class Rule
 *
 * @package Hanaboso\AclBundle\Entity
 */
#[ORM\Entity(repositoryClass: 'Hanaboso\AclBundle\Repository\Entity\RuleRepository')]
#[ORM\Table(name: 'rule')]
class Rule
{

    use IdTrait;

    public const string ID            = 'id';
    public const string RESOURCE      = 'resource';
    public const string PROPERTY_MASK = 'property_mask';
    public const string ACTION_MASK   = 'action_mask';

    /**
     * @var string
     */
    #[ORM\Column(type: 'string')]
    private string $resource;

    /**
     * @var Group
     */
    #[ORM\ManyToOne(targetEntity: 'Hanaboso\AclBundle\Entity\Group', inversedBy: 'rules')]
    private Group $group;

    /**
     * @var int

     */
    #[ORM\Column(type: 'integer')]
    private int $actionMask;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    private int $propertyMask;

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
        $this->id           = $data[self::ID];
        $this->propertyMask = $data[self::PROPERTY_MASK];
        $this->actionMask   = $data[self::ACTION_MASK];
        $this->resource     = $data[self::RESOURCE];

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function toArrayAcl(): array
    {
        return [
            self::ACTION_MASK   => $this->actionMask,
            self::ID            => $this->id,
            self::PROPERTY_MASK => $this->propertyMask,
            self::RESOURCE      => $this->resource,
        ];
    }

}
