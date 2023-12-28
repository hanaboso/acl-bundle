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
class Rule implements RuleInterface
{

    use IdTrait;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string')]
    private string $resource;

    /**
     * @var GroupInterface
     */
    #[ORM\ManyToOne(targetEntity: 'Hanaboso\AclBundle\Entity\Group', inversedBy: 'rules')]
    private GroupInterface $group;

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
     * @param mixed[] $data
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
