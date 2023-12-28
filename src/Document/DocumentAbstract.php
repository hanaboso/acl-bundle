<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Hanaboso\AclBundle\Annotation\OwnerAnnotation as OWNER;
use Hanaboso\UserBundle\Entity\UserInterface;

/**
 * Class DocumentAbstract
 *
 * @package Hanaboso\AclBundle\Document
 */
abstract class DocumentAbstract
{

    /**
     * @var UserInterface|null
     *
     * @OWNER()
     */
    #[ODM\ReferenceOne(strategy: 'set', targetDocument: 'Hanaboso\UserBundle\Document\User')]
    protected ?UserInterface $owner;

    /**
     * DocumentAbstract constructor.
     *
     * @param UserInterface|null $owner
     */
    function __construct(?UserInterface $owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return UserInterface|null
     */
    public function getOwner(): ?UserInterface
    {
        return $this->owner;
    }

    /**
     * @param UserInterface|null $owner
     *
     * @return $this
     */
    public function setOwner(?UserInterface $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

}
