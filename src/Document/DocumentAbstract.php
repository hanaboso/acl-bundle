<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Hanaboso\AclBundle\Attribute\OwnerAttribute;
use Hanaboso\UserBundle\Document\User;

/**
 * Class DocumentAbstract
 *
 * @package Hanaboso\AclBundle\Document
 */
abstract class DocumentAbstract
{

    /**
     * @var User|null
     */
    #[ODM\ReferenceOne(strategy: 'set', targetDocument: 'Hanaboso\UserBundle\Document\User')]
    #[OwnerAttribute]
    protected ?User $owner;

    /**
     * DocumentAbstract constructor.
     *
     * @param User|null $owner
     */
    function __construct(?User $owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return User|null
     */
    public function getOwner(): ?User
    {
        return $this->owner;
    }

    /**
     * @param User|null $owner
     *
     * @return $this
     */
    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

}
