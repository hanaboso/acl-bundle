<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Entity;

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
     * @var UserInterface[]|null
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
        if (!is_null($owner)) {
            $this->owner[0] = $owner;
        }
    }

    /**
     * @return UserInterface|null
     */
    public function getOwner(): ?UserInterface
    {
        if (is_null($this->owner)) {
            return NULL;
        }

        return $this->owner[0];
    }

    /**
     * @param UserInterface|null $owner
     *
     * @return EntityAbstract
     */
    public function setOwner(?UserInterface $owner): ?EntityAbstract
    {
        if (is_null($owner)) {
            $this->owner = NULL;
        } else {
            $this->owner[0] = $owner;
        }

        return $this;
    }

}