<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Hanaboso\AclBundle\Document\Group as DmGroup;
use Hanaboso\AclBundle\Entity\Group;
use Hanaboso\AclBundle\Enum\ResourceEnum;
use Hanaboso\AclBundle\Exception\AclException;
use Hanaboso\AclBundle\Provider\AclRuleProviderInterface;
use Hanaboso\AclBundle\Repository\Document\GroupRepository as GroupRepositoryDocument;
use Hanaboso\AclBundle\Repository\Entity\GroupRepository as GroupRepositoryEntity;
use Hanaboso\CommonsBundle\Database\Locator\DatabaseManagerLocator;
use Hanaboso\UserBundle\Document\TmpUser as DmTmpUser;
use Hanaboso\UserBundle\Document\User as DmUser;
use Hanaboso\UserBundle\Entity\TmpUser;
use Hanaboso\UserBundle\Entity\User;
use Hanaboso\UserBundle\Enum\UserTypeEnum;
use Hanaboso\UserBundle\Provider\ResourceProvider;
use Hanaboso\UserBundle\Provider\ResourceProviderException;

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
    protected DocumentManager|EntityManager $dm;

    /**
     * GroupManager constructor.
     *
     * @param DatabaseManagerLocator   $dml
     * @param ResourceProvider         $resourceProvider
     * @param AclRuleProviderInterface $aclProvider
     */
    public function __construct(
        DatabaseManagerLocator $dml,
        protected ResourceProvider $resourceProvider,
        protected AclRuleProviderInterface $aclProvider,
    )
    {
        $this->dm = $dml->get();
    }

    /**
     * @param User|DmUser|TmpUser|DmTmpUser $user
     * @param string|null                   $id
     * @param string|null                   $groupName
     *
     * @throws AclException
     * @throws MongoDBException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function addUserIntoGroup(
        User|DmUser|TmpUser|DmTmpUser $user,
        ?string $id = NULL,
        ?string $groupName = NULL,
    ): void
    {
        $query = [];

        if ($id) {
            $query['id'] = $id;
        }

        if ($groupName) {
            $query['name'] = $groupName;
        }

        if ($query === []) {
            throw new AclException('Insert [name] or [id] of Group!', AclException::GROUP_NOT_FOUND);
        }

        try {
            /** @phpstan-var class-string<Group|DmGroup> $groupClass */
            $groupClass = $this->resourceProvider->getResource(ResourceEnum::GROUP);
            /** @var Group|DmGroup|null $group */
            $group = $this->dm->getRepository($groupClass)->findOneBy($query);
        } catch (ResourceProviderException $e) {
            throw new AclException($e->getMessage(), $e->getCode());
        }

        if (!$group) {
            throw new AclException(sprintf('Group [%s] was not found!', $groupName), AclException::GROUP_NOT_FOUND);
        }

        if ($user->getType() === UserTypeEnum::TMP_USER) {
            /** @var TmpUser|DmTmpUser $u */
            $u = $user;
            $group->addTmpUser($u);
        }

        if ($user->getType() === UserTypeEnum::USER) {
            /** @var User|DmUser $u */
            $u = $user;
            $group->addUser($u);
        }

        $this->aclProvider->invalid([$user->getId()]);

        $this->dm->flush();
    }

    /**
     * @param User|DmUser|TmpUser|DmTmpUser $user
     * @param string|null                   $id
     * @param string|null                   $groupName
     *
     * @throws AclException
     * @throws MongoDBException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function removeUserFromGroup(
        User|DmUser|TmpUser|DmTmpUser $user,
        ?string $id = NULL,
        ?string $groupName = NULL,
    ): void
    {
        $query = [];

        if ($id) {
            $query['id'] = $id;
        }

        if ($groupName) {
            $query['name'] = $groupName;
        }

        if ($query === []) {
            throw new AclException('Insert [name] or [id] of Group!', AclException::GROUP_NOT_FOUND);
        }

        try {
            /** @phpstan-var class-string<Group|DmGroup> $groupClass */
            $groupClass = $this->resourceProvider->getResource(ResourceEnum::GROUP);
            /** @var Group|DmGroup|null $group */
            $group = $this->dm->getRepository($groupClass)->findOneBy($query);
        } catch (ResourceProviderException $e) {
            throw new AclException($e->getMessage(), $e->getCode());
        }

        if (!$group) {
            throw new AclException(sprintf('Group [%s] was not found!', $groupName), AclException::GROUP_NOT_FOUND);
        }

        if ($user->getType() === UserTypeEnum::TMP_USER) {
            $users = $group->getTmpUsers()->toArray();
            $group->getTmpUsers()->clear();
            $this->dm->flush();
            $this->removeItem($users, $user);
            $group->setTmpUsers($users);
        }

        if ($user->getType() === UserTypeEnum::USER) {
            $users = $group->getUsers()->toArray();
            $group->getUsers()->clear();
            $this->dm->flush();
            $this->removeItem($users, $user);
            $group->setUsers($users);
        }

        if (count($group->getTmpUsers()) == 0 && count($group->getUsers()) == 0 && $group->getOwner()) {
            $group->setOwner(NULL);
            foreach ($group->getRules() as $rule) {
                $this->dm->remove($rule);
            }
            $group->getRules()->clear();
            $this->dm->remove($group);
        }

        $this->aclProvider->invalid([$user->getId()]);

        $this->dm->flush();
    }

    /**
     * @param User|DmUser|TmpUser|DmTmpUser $user
     *
     * @return mixed[]
     * @throws MongoDBException
     * @throws AclException
     */
    public function getUserGroups(User|DmUser|TmpUser|DmTmpUser $user): array
    {
        try {
            /** @phpstan-var class-string<Group|DmGroup> $groupClass */
            $groupClass = $this->resourceProvider->getResource(ResourceEnum::GROUP);
            /** @var GroupRepositoryEntity|GroupRepositoryDocument $repo */
            $repo = $this->dm->getRepository($groupClass);
        } catch (ResourceProviderException $e) {
            throw new AclException($e->getMessage(), $e->getCode());
        }

        if ($user->getType() === UserTypeEnum::USER) {
            /** @var User|DmUser $u */
            $u      = $user;
            $groups = $repo->getUserGroups($u);
        } else {
            /** @var TmpUser|DmTmpUser $u */
            $u      = $user;
            $groups = $repo->getTmpUserGroups($u);
        }

        $res = [];
        foreach ($groups as $group) {
            $res[] = ['name' => $group->getName(), 'id' => $group->getId(), 'level' => $group->getLevel()];
        }
        usort($res, static fn(array $a, array $b): int => $b['level'] <=> $a['level']);

        return $res;
    }

    /**
     * ----------------------------------------- HELPERS ----------------------------------------
     */

    /**
     * @param mixed[]                       $users
     * @param User|DmUser|TmpUser|DmTmpUser $user
     */
    private function removeItem(array &$users, User|DmUser|TmpUser|DmTmpUser $user): void
    {
        foreach ($users as $key => $item) {
            if ($item->getId() == $user->getId()) {
                unset($users[$key]);

                break;
            }
        }
    }

}
