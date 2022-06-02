<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Hanaboso\AclBundle\Annotation\OwnerAnnotation as OWNER;
use Hanaboso\UserBundle\Entity\UserInterface;

/**
 * Class EntityAbstract
 *
 * @package Hanaboso\AclBundle\Entity
 */
abstract class EntityAbstract
{

    /**
     * @var ArrayCollection<int, UserInterface>
     * @OWNER()
     */
    protected $owner;

    /**
     * EntityAbstract constructor.
     *
     * @param UserInterface|null $owner
     */
    function __construct(?UserInterface $owner)
    {
        $this->owner = new ArrayCollection();
        if (!is_null($owner)) {
            $this->owner->add($owner);
        }
    }

    /**
     * @return UserInterface|null
     */
    public function getOwner(): ?UserInterface
    {
        if ($this->owner->isEmpty()) {
            return NULL;
        }

        return $this->owner[0];
    }

    /**
     * @param UserInterface|null $owner
     *
     * @return $this
     */
    public function setOwner(?UserInterface $owner): self
    {
        if (is_null($owner)) {
            $this->owner->clear();
        } else {
            $this->owner[0] = $owner;
        }

        return $this;
    }

}
