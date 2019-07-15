<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Hanaboso\AclBundle\Entity\GroupInterface;
use Hanaboso\AclBundle\Enum\ResourceEnum;
use Hanaboso\AclBundle\Exception\AclException;
use Hanaboso\AclBundle\Provider\AclRuleProviderInterface;
use Hanaboso\AclBundle\Repository\Document\GroupRepository as GroupRepositoryDocument;
use Hanaboso\AclBundle\Repository\Entity\GroupRepository as GroupRepositoryEntity;
use Hanaboso\CommonsBundle\Database\Locator\DatabaseManagerLocator;
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
    protected $dm;

    /**
     * @var ResourceProvider
     */
    protected $resourceProvider;

    /**
     * @var AclRuleProviderInterface
     */
    protected $aclProvider;

    /**
     * GroupManager constructor.
     *
     * @param DatabaseManagerLocator   $dml
     * @param ResourceProvider         $resourceProvider
     * @param AclRuleProviderInterface $aclProvider
     */
    public function __construct(
        DatabaseManagerLocator $dml,
        ResourceProvider $resourceProvider,
        AclRuleProviderInterface $aclProvider
    )
    {
        $this->dm               = $dml->get();
        $this->resourceProvider = $resourceProvider;
        $this->aclProvider      = $aclProvider;
    }

    /**
     * @param UserInterface $user
     * @param null|string   $id
     * @param string        $groupName
     *
     * @throws AclException
     * @throws ORMException
     * @throws UserException
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

        if (empty($query)) {
            throw new AclException('Insert [name] or [id] of Group!', AclException::GROUP_NOT_FOUND);
        }

        /** @var GroupInterface|null $group */
        $group = $this->dm->getRepository($this->resourceProvider->getResource(ResourceEnum::GROUP))->findOneBy($query);

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
     * @param null|string   $id
     * @param string        $groupName
     *
     * @throws AclException
     * @throws ORMException
     * @throws UserException
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

        if (empty($query)) {
            throw new AclException('Insert [name] or [id] of Group!', AclException::GROUP_NOT_FOUND);
        }

        /** @var GroupInterface|null $group */
        $group = $this->dm->getRepository($this->resourceProvider->getResource(ResourceEnum::GROUP))->findOneBy($query);

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
     * @return array
     * @throws UserException
     * @throws MongoDBException
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
            $res[] = ['name' => $group->getName(), 'id' => $group->getId(), 'level' => $group->getLevel()];
        }

        usort($res, function (array $a, array $b): int {
            if ($a['level'] == $b['level']) {
                return 0;
            }

            return $a['level'] > $b['level'] ? -1 : 1;
        });

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
