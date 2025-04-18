<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Hanaboso\AclBundle\Document\Group as DmGroup;
use Hanaboso\AclBundle\Entity\Group;
use Hanaboso\AclBundle\Entity\GroupInterface;
use Hanaboso\AclBundle\Enum\ResourceEnum;
use Hanaboso\AclBundle\Exception\AclException;
use Hanaboso\AclBundle\Provider\AclRuleProviderInterface;
use Hanaboso\AclBundle\Repository\Document\GroupRepository as GroupRepositoryDocument;
use Hanaboso\AclBundle\Repository\Entity\GroupRepository as GroupRepositoryEntity;
use Hanaboso\CommonsBundle\Database\Locator\DatabaseManagerLocator;
use Hanaboso\UserBundle\Entity\UserInterface;
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
     * @param UserInterface $user
     * @param string|null   $id
     * @param string|null   $groupName
     *
     * @throws AclException
     * @throws MongoDBException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function addUserIntoGroup(UserInterface $user, ?string $id = NULL, ?string $groupName = NULL): void
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
            /** @var GroupInterface|null $group */
            $group = $this->dm->getRepository($groupClass)->findOneBy($query);
        } catch (ResourceProviderException $e) {
            throw new AclException($e->getMessage(), $e->getCode());
        }

        if (!$group) {
            throw new AclException(sprintf('Group [%s] was not found!', $groupName), AclException::GROUP_NOT_FOUND);
        }

        if ($user->getType() === UserTypeEnum::TMP_USER) {
            $group->addTmpUser($user);
        }

        if ($user->getType() === UserTypeEnum::USER) {
            $group->addUser($user);
        }

        $this->aclProvider->invalid([$user->getId()]);

        $this->dm->flush();
    }

    /**
     * @param UserInterface $user
     * @param string|null   $id
     * @param string|null   $groupName
     *
     * @throws AclException
     * @throws MongoDBException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function removeUserFromGroup(UserInterface $user, ?string $id = NULL, ?string $groupName = NULL): void
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
            /** @var GroupInterface|null $group */
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
     * @param UserInterface $user
     *
     * @return mixed[]
     * @throws MongoDBException
     * @throws AclException
     */
    public function getUserGroups(UserInterface $user): array
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
            $groups = $repo->getUserGroups($user);
        } else {
            $groups = $repo->getTmpUserGroups($user);
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
     * @param mixed[]       $users
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
