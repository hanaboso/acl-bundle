<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Provider\Impl;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ORM\EntityManager;
use Hanaboso\AclBundle\Cache\ProviderCacheInterface;
use Hanaboso\AclBundle\Document\Group as DmGroup;
use Hanaboso\AclBundle\Entity\Group;
use Hanaboso\AclBundle\Entity\GroupInterface;
use Hanaboso\AclBundle\Entity\RuleInterface;
use Hanaboso\AclBundle\Exception\AclException;
use Hanaboso\AclBundle\Provider\AclRuleProviderInterface;
use Hanaboso\AclBundle\Repository\Document\GroupRepository as OdmRepo;
use Hanaboso\AclBundle\Repository\Entity\GroupRepository as OrmRepo;
use Hanaboso\CommonsBundle\Database\Locator\DatabaseManagerLocator;
use Hanaboso\UserBundle\Entity\UserInterface;
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
     * @param UserInterface $user
     * @param int           $userLvl
     *
     * @return RuleInterface[]
     * @throws AclException
     */
    public function getRules(UserInterface $user, int &$userLvl): array
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
     * @param UserInterface $user
     *
     * @return GroupInterface[]
     * @throws AclException
     */
    public function getGroups(UserInterface $user): array
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
     * @param UserInterface    $user
     * @param GroupInterface[] $groups
     *
     * @throws LogicException
     */
    protected function store(UserInterface $user, array $groups): void
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
     * @param UserInterface $user
     *
     * @return GroupInterface[]|null
     * @throws AclException
     */
    protected function load(UserInterface $user): ?array
    {
        try {
            $arr = $this->cache->get($this->getKey($user));
            if ($arr === NULL) {
                return NULL;
            }

            $groups = [];

            $groupClass = $this->provider->getResource($this->resourceEnum::GROUP);
            $ruleClass  = $this->provider->getResource($this->resourceEnum::RULE);
            /** @var RuleInterface[] $rulesList */
            $rulesList = [];

            foreach ($arr[self::GROUPS] as $groupData) {
                $owner = $groupData[GroupInterface::OWNER] === $user->getId() ? $user : NULL;
                /** @var GroupInterface $g */
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
     * @param UserInterface $user
     *
     * @return string
     */
    protected function getKey(UserInterface $user): string
    {
        return $this->getKeyById($user->getId());
    }

    /**
     * @param string $id
     *
     * @return string
     */
    protected function getKeyById(string $id): string
    {
        return sprintf('%s_%s', self::PREFIX, $id);
    }

}
