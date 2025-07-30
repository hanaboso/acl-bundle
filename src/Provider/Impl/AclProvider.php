<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Provider\Impl;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ORM\EntityManager;
use Hanaboso\AclBundle\Cache\ProviderCacheInterface;
use Hanaboso\AclBundle\Document\Group as DmGroup;
use Hanaboso\AclBundle\Document\Rule as DmRule;
use Hanaboso\AclBundle\Entity\Group;
use Hanaboso\AclBundle\Entity\Rule;
use Hanaboso\AclBundle\Exception\AclException;
use Hanaboso\AclBundle\Provider\AclRuleProviderInterface;
use Hanaboso\AclBundle\Repository\Document\GroupRepository as OdmRepo;
use Hanaboso\AclBundle\Repository\Entity\GroupRepository as OrmRepo;
use Hanaboso\CommonsBundle\Database\Locator\DatabaseManagerLocator;
use Hanaboso\UserBundle\Document\User as DmUser;
use Hanaboso\UserBundle\Entity\User;
use Hanaboso\UserBundle\Provider\ResourceProvider;
use Hanaboso\UserBundle\Provider\ResourceProviderException;
use LogicException;

/**
 * Class AclProvider
 *
 * @package Hanaboso\AclBundle\Provider\Impl
 */
final class AclProvider implements AclRuleProviderInterface
{

    protected const string GROUPS = 'groups';
    protected const string LINKS  = 'links';

    /**
     * @var DocumentManager|EntityManager
     */
    protected DocumentManager|EntityManager $dm;

    /**
     * AclProvider constructor.
     *
     * @param DatabaseManagerLocator $dml
     * @param ResourceProvider       $provider
     * @param string                 $resourceEnum
     * @param ProviderCacheInterface $cache
     */
    public function __construct(
        DatabaseManagerLocator $dml,
        protected ResourceProvider $provider,
        protected string $resourceEnum,
        private readonly ProviderCacheInterface $cache,
    )
    {
        $this->dm = $dml->get();
    }

    /**
     * @param User|DmUser $user
     * @param int         $userLvl
     *
     * @return Rule[]|DmRule[]
     * @throws AclException
     */
    public function getRules(User|DmUser $user, int &$userLvl): array
    {
        $rules  = [];
        $groups = $this->getGroups($user);
        foreach ($groups as $group) {
            if ($group->getLevel() < $userLvl) {
                $userLvl = $group->getLevel();
            }

            foreach ($group->getRules() as $rule) {
                $rules[] = $rule;
            }
        }

        return $rules;
    }

    /**
     * @param User|DmUser $user
     *
     * @return Group[]|DmGroup[]
     * @throws AclException
     */
    public function getGroups(User|DmUser $user): array
    {
        try {
            $res = $this->load($user);
            if ($res !== NULL) {
                return $res;
            }

            /** @phpstan-var class-string<Group|DmGroup> $groupClass */
            $groupClass = $this->provider->getResource($this->resourceEnum::GROUP);
            /** @var OrmRepo|OdmRepo $repo */
            $repo   = $this->dm->getRepository($groupClass);
            $groups = $repo->getUserGroups($user);

            $this->store($user, $groups);

            return $groups;
        } catch (ResourceProviderException | LogicException | MongoDBException $e) {
            throw new AclException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param mixed[] $users
     *
     * @throws LogicException
     */
    public function invalid(array $users): void
    {
        foreach ($users as $userId) {
            $this->cache->delete($this->getKeyById($userId));
        }
    }

    /**
     * @param User|DmUser       $user
     * @param Group[]|DmGroup[] $groups
     *
     * @throws LogicException
     */
    protected function store(User|DmUser $user, array $groups): void
    {
        $arr        = [];
        $parentList = [];
        foreach ($groups as $group) {
            $arr[] = $group->toArrayAcl($parentList);
        }

        $this->cache->set(
            $this->getKey($user),
            86_400,
            [self::GROUPS => $arr, self::LINKS => $parentList],
        );
    }

    /**
     * @param User|DmUser $user
     *
     * @return Group[]|DmGroup[]|null
     * @throws AclException
     */
    protected function load(User|DmUser $user): ?array
    {
        try {
            $arr = $this->cache->get($this->getKey($user));
            if ($arr === NULL) {
                return NULL;
            }

            $groups = [];

            $groupClass = $this->provider->getResource($this->resourceEnum::GROUP);
            $ruleClass  = $this->provider->getResource($this->resourceEnum::RULE);
            /** @var Rule[]|DmRule[] $rulesList */
            $rulesList = [];

            foreach ($arr[self::GROUPS] as $groupData) {
                $owner = $groupData[Group::OWNER] === $user->getId() ? $user : NULL;
                /** @var Group|DmGroup $g */
                $g = new $groupClass($owner);
                $g->fromArrayAcl($groupData, $ruleClass, $rulesList);
                $groups[$g->getId()] = $g;
            }
            foreach ($arr[self::LINKS] as $ruleId => $groupId) {
                $rulesList[$ruleId]->setGroup($groups[$groupId]);
            }

            return $groups;
        } catch (LogicException | ResourceProviderException $e) {
            throw new AclException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param User|DmUser $user
     *
     * @return string
     */
    protected function getKey(User|DmUser $user): string
    {
        return $this->getKeyById($user->getId());
    }

    /**
     * @param string|int $id
     *
     * @return string
     */
    protected function getKeyById(string|int $id): string
    {
        return sprintf('%s_%s', self::PREFIX, $id);
    }

}
