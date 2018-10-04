<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Provider\Impl;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Hanaboso\AclBundle\Entity\GroupInterface;
use Hanaboso\AclBundle\Entity\RuleInterface;
use Hanaboso\AclBundle\Provider\AclRuleProviderInterface;
use Hanaboso\AclBundle\Repository\Document\GroupRepository as OdmRepo;
use Hanaboso\AclBundle\Repository\Entity\GroupRepository as OrmRepo;
use Hanaboso\CommonsBundle\DatabaseManager\DatabaseManagerLocator;
use Hanaboso\UserBundle\Entity\UserInterface;
use Hanaboso\UserBundle\Exception\UserException;
use Hanaboso\UserBundle\Provider\ResourceProvider;
use LogicException;
use Predis\Client;

/**
 * Class AclProvider
 *
 * @package Hanaboso\AclBundle\Provider\Impl
 */
class AclProvider implements AclRuleProviderInterface
{

    private const GROUPS = 'groups';
    private const LINKS  = 'links';

    /**
     * @var DocumentManager|EntityManager
     */
    private $dm;

    /**
     * @var ResourceProvider
     */
    private $provider;

    /**
     * @var string
     */
    private $resourceEnum;
    /**
     * @var bool
     */
    private $useCache;

    /**
     * @var string
     */
    private $redisHost;

    /**
     * @var int
     */
    private $redisPort;

    /**
     * AclProvider constructor.
     *
     * @param DatabaseManagerLocator $dml
     * @param ResourceProvider       $provider
     * @param string                 $resourceEnum
     * @param string                 $useCache
     * @param string                 $redisHost
     * @param string                 $redisPort
     */
    public function __construct(
        DatabaseManagerLocator $dml,
        ResourceProvider $provider,
        string $resourceEnum,
        string $useCache,
        string $redisHost,
        string $redisPort
    )
    {
        $this->dm           = $dml->get();
        $this->provider     = $provider;
        $this->resourceEnum = $resourceEnum;
        $this->useCache     = boolval($useCache);
        $this->redisHost    = $redisHost;
        $this->redisPort    = (int) $redisPort;
    }

    /**
     * @param UserInterface $user
     *
     * @return RuleInterface[]
     * @throws UserException
     * @throws LogicException
     */
    public function getRules(UserInterface $user): array
    {
        $rules  = [];
        $groups = $this->getGroups($user);
        foreach ($groups as $group) {
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
     * @throws UserException
     * @throws LogicException
     */
    public function getGroups(UserInterface $user): array
    {
        if ($this->useCache) {
            $res = $this->load($user);
            if ($res !== NULL) {
                return $res;
            }
        }

        /** @var OrmRepo|OdmRepo $repo */
        $repo   = $this->dm->getRepository($this->provider->getResource($this->resourceEnum::GROUP));
        $groups = $repo->getUserGroups($user);

        if ($this->useCache) {
            $this->store($user, $groups);
        }

        return $groups;
    }

    /**
     * @param array $userIds
     *
     * @throws LogicException
     */
    public function invalid(array $userIds): void
    {
        if ($this->useCache) {
            $redis = $this->getClient();
            foreach ($userIds as $userId) {
                $redis->del([$this->getKeyById($userId)]);
            }
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

        $redis = $this->getClient();
        $redis->setex($this->getKey($user), 86400, (string) json_encode([
            self::GROUPS => $arr,
            self::LINKS  => $parentList,
        ]));
    }

    /**
     * @param UserInterface $user
     *
     * @return GroupInterface[]|null
     * @throws UserException
     * @throws LogicException
     */
    protected function load(UserInterface $user): ?array
    {
        $redis = $this->getClient();
        $key   = $this->getKey($user);
        if (!$redis->exists($key)) {
            return NULL;
        }

        $json   = $redis->get($key);
        $arr    = json_decode($json, TRUE);
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

    /**
     * @return Client
     * @throws LogicException
     */
    protected function getClient(): Client
    {
        $redis = new Client([
            'host' => $this->redisHost,
            'port' => $this->redisPort,
        ]);
        $redis->connect();
        if (!$redis->isConnected()) {
            throw new LogicException('Failed to connect to redis.');
        }

        return $redis;
    }

}