<?php declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: radek.jirsa
 * Date: 11.5.18
 * Time: 17:00
 */

namespace Hanaboso\AclBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Hanaboso\AclBundle\Entity\GroupInterface;
use Hanaboso\AclBundle\Enum\ResourceEnum;
use Hanaboso\AclBundle\Exception\AclException;
use Hanaboso\CommonsBundle\DatabaseManager\DatabaseManagerLocator;
use Hanaboso\UserBundle\Entity\UserInterface;
use Hanaboso\UserBundle\Enum\UserTypeEnum;
use Hanaboso\UserBundle\Exception\UserException;
use Hanaboso\UserBundle\Provider\ResourceProvider;

/**
 * Class GroupManager
 *
 * @package Hanaboso\AclBundle\Manager
 */
class GroupManager
{

    /**
     * @var DocumentManager|EntityManager
     */
    private $dm;

    /**
     * @var ResourceProvider
     */
    private $resourceProvider;

    /**
     * GroupManager constructor.
     *
     * @param DatabaseManagerLocator $dml
     * @param ResourceProvider       $resourceProvider
     */
    public function __construct(DatabaseManagerLocator $dml, ResourceProvider $resourceProvider)
    {
        $this->dm               = $dml->get();
        $this->resourceProvider = $resourceProvider;
    }

    /**
     * @param string        $groupName
     * @param UserInterface $user
     *
     * @throws AclException
     * @throws UserException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function addUserIntoGroup(string $groupName, UserInterface $user): void
    {
        /** @var GroupInterface $group */
        $group = $this->dm->getRepository($this->resourceProvider->getResource(ResourceEnum::GROUP))
            ->findOneBy(['name' => $groupName]);

        if (!$group) {
            throw new AclException(sprintf('Group [%s] was not found!', $groupName), AclException::GROUP_NOT_FOUND);
        }

        if ($user->getType() === UserTypeEnum::TMP_USER) {
            $group->addTmpUser($user);
        }

        if ($user->getType() === UserTypeEnum::USER) {
            $group->addUser($user);
        }

        $this->dm->flush();
    }

}