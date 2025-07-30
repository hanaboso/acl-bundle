<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Hanaboso\AclBundle\Attribute\OwnerAttribute;
use Hanaboso\UserBundle\Entity\User;

/**
 * Class EntityAbstract
 *
 * @package Hanaboso\AclBundle\Entity
 */
abstract class EntityAbstract
{

    /**
     * @var ArrayCollection<int, User>
     */
    #[OwnerAttribute]
    protected $owner;

    /**
     * EntityAbstract constructor.
     *
     * @param User|null $owner
     */
    function __construct(?User $owner)
    {
        $this->owner = new ArrayCollection();
        if (!is_null($owner)) {
            $this->owner->add($owner);
        }
    }

    /**
     * @return User|null
     */
    public function getOwner(): ?User
    {
        if ($this->owner->isEmpty()) {
            return NULL;
        }

        return $this->owner[0];
    }

    /**
     * @param User|null $owner
     *
     * @return $this
     */
    public function setOwner(?User $owner): self
    {
        if (is_null($owner)) {
            $this->owner->clear();
        } else {
            $this->owner[0] = $owner;
        }

        return $this;
    }

}
