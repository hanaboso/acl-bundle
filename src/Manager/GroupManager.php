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
use Hanaboso\AclBundle\Repository\Document\GroupRepository as GroupRepositoryDocument;
use Hanaboso\AclBundle\Repository\Entity\GroupRepository as GroupRepositoryEntity;
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

    /**
     * @param string        $groupName
     * @param UserInterface $user
     *
     * @throws AclException
     * @throws UserException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function removeUserFromGroup(string $groupName, UserInterface $user): void
    {
        /** @var GroupInterface $group */
        $group = $this->dm->getRepository($this->resourceProvider->getResource(ResourceEnum::GROUP))
            ->findOneBy(['name' => $groupName]);

        if (!$group) {
            throw new AclException(sprintf('Group [%s] was not found!', $groupName), AclException::GROUP_NOT_FOUND);
        }

        if ($user->getType() === UserTypeEnum::TMP_USER) {
            $users = $group->getTmpUsers()->toArray();
            $this->removeItem($users, $user);
            $group->setTmpUsers($users);
        }

        if ($user->getType() === UserTypeEnum::USER) {
            $users = $group->getUsers()->toArray();
            $this->removeItem($users, $user);
            $group->setUsers($users);
        }

        $this->dm->persist($group);

        if (count($group->getTmpUsers()) == 0 && count($group->getUsers()) == 0) {
            $this->dm->remove($group);
        }

        $this->dm->flush();
    }

    /**
     * @param UserInterface $user
     *
     * @return array
     * @throws UserException
     * @throws ORMException
     */
    public function getUserGroups(UserInterface $user): array
    {
        /** @var GroupRepositoryEntity|GroupRepositoryDocument $repo */
        $repo = $this->dm->getRepository($this->resourceProvider->getResource(ResourceEnum::GROUP));

        if ($user->getType() === UserTypeEnum::USER) {
            $groups = $repo->getUserGroups($user) ?? [];
        } else {
            $groups = $repo->getTmpUserGroups($user) ?? [];
        }

        $res = [];
        foreach ($groups as $group) {
            $res[] = $group->getName();
        };

        return $res;
    }

    /**
     * ----------------------------------------- HELPERS ----------------------------------------
     */

    /**
     * @param array         $users
     * @param UserInterface $user
     */
    private function removeItem(array &$users, UserInterface $user): void
    {
        foreach ($users as $key => $item) {
            if ($item->getId() == $user->getId()) {
                unset($users[$key]);
                break;
            }
        }
    }

}