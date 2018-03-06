<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Provider\Impl;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Hanaboso\AclBundle\Entity\RuleInterface;
use Hanaboso\AclBundle\Enum\ResourceEnum;
use Hanaboso\AclBundle\Provider\ProviderInterface;
use Hanaboso\AclBundle\Repository\Document\GroupRepository as OdmRepo;
use Hanaboso\AclBundle\Repository\Entity\GroupRepository as OrmRepo;
use Hanaboso\CommonsBundle\DatabaseManager\DatabaseManagerLocator;
use Hanaboso\UserBundle\Entity\UserInterface;
use Hanaboso\UserBundle\Exception\UserException;
use Hanaboso\UserBundle\Provider\ResourceProvider;

/**
 * Class DatabaseProvider
 *
 * @package Hanaboso\AclBundle\Provider\Impl
 */
class DatabaseProvider implements ProviderInterface
{

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
     * DatabaseProvider constructor.
     *
     * @param DatabaseManagerLocator $dml
     * @param ResourceProvider       $provider
     * @param string                 $resourceEnum
     */
    public function __construct(DatabaseManagerLocator $dml, ResourceProvider $provider, string $resourceEnum)
    {
        $this->dm       = $dml->get();
        $this->provider = $provider;
        $this->resourceEnum = $resourceEnum;
    }

    /**
     * @param UserInterface $user
     *
     * @return RuleInterface[]
     * @throws UserException
     */
    public function getRules(UserInterface $user): array
    {
        /** @var OrmRepo|OdmRepo $groupRepository */
        $groupRepository = $this->dm->getRepository($this->provider->getResource(($this->resourceEnum)::GROUP));

        $rules = [];
        foreach ($groupRepository->getUserGroups($user) as $group) {
            foreach ($group->getRules() as $rule) {
                $rules[] = $rule;
            }
        }

        return $rules;
    }

}