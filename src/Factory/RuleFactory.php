<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Factory;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Hanaboso\AclBundle\Entity\GroupInterface;
use Hanaboso\AclBundle\Entity\RuleInterface;
use Hanaboso\AclBundle\Exception\AclException;
use Hanaboso\CommonsBundle\DatabaseManager\DatabaseManagerLocator;
use Hanaboso\UserBundle\Exception\UserException;
use Hanaboso\UserBundle\Provider\ResourceProvider;

/**
 * Class RuleFactory
 *
 * @package Hanaboso\AclBundle\Factory
 */
class RuleFactory
{

    /**
     * @var array
     */
    private $rules;

    /**
     * @var DocumentManager|EntityManager
     */
    private $dm;

    /**
     * @var mixed
     */
    private $resource;

    /**
     * @var ResourceProvider
     */
    private $provider;

    /**
     * RuleFactory constructor.
     *
     * @param DatabaseManagerLocator $userDml
     * @param ResourceProvider       $provider
     * @param array                  $rules
     * @param mixed                  $resEnum
     *
     * @throws AclException
     */
    function __construct(
        DatabaseManagerLocator $userDml,
        ResourceProvider $provider,
        array $rules,
        $resEnum
    )
    {
        if (!is_array($rules) || !array_key_exists('owner', $rules)) {
            throw new AclException(
                'Missing \'owner\' key in acl_rules.yml for default ruleset.',
                AclException::MISSING_DEFAULT_RULES
            );
        }

        $this->dm       = $userDml->get();
        $this->rules    = $rules['owner'];
        $this->resource = $resEnum;
        $this->provider = $provider;
    }

    /**
     * @param string         $resource
     * @param GroupInterface $group
     * @param int            $actMask
     * @param int            $propMask
     * @param string         $ruleClass
     *
     * @return RuleInterface
     */
    public static function createRule(
        string $resource,
        GroupInterface $group,
        int $actMask,
        int $propMask,
        string $ruleClass
    ): RuleInterface
    {
        /** @var RuleInterface $rule */
        $rule = new $ruleClass();

        $rule
            ->setResource($resource)
            ->setGroup($group)
            ->setActionMask($actMask)
            ->setPropertyMask($propMask);

        $group->addRule($rule);

        return $rule;
    }

    /**
     * @param GroupInterface $group
     *
     * @return array|RuleInterface[]
     * @throws AclException
     * @throws UserException
     */
    public function getDefaultRules(GroupInterface $group): array
    {
        $this->dm->persist($group);

        // TODO ošetřit následnou změnu defaultních práv
        $rules = [];
        foreach ($this->rules as $key => $rule) {
            if (!($this->resource)::isValid($key)) {
                throw new AclException(
                    sprintf('[%s] is not a valid resource', $key),
                    AclException::INVALID_RESOURCE
                );
            }

            $ruleClass = $this->provider->getResource(($this->resource)::RULE);
            $actMask = MaskFactory::maskActionFromYmlArray($rule);
            $rule    = self::createRule($key, $group, $actMask, 1, $ruleClass);
            $group->addRule($rule);
            $this->dm->persist($rule);

            $rules[] = $rule;
        }

        return $rules;
    }

}