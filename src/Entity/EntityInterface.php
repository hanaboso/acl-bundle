<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Entity;

use Hanaboso\AclBundle\Document\DocumentAbstract;
use Hanaboso\UserBundle\Entity\UserInterface;

/**
 * Interface EntityInterface
 *
 * @package Hanaboso\AclBundle\Entity
 */
interface EntityInterface
{

    /**
     * @return UserInterface|null
     */
    public function getOwner(): ?UserInterface;

    /**
     * @param UserInterface|null $owner
     *
     * @return EntityAbstract|DocumentAbstract|null
     */
    public function setOwner(?UserInterface $owner);

}