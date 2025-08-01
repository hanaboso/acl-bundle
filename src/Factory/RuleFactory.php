<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Factory;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Hanaboso\AclBundle\Document\Group as DmGroup;
use Hanaboso\AclBundle\Document\Rule as DmRule;
use Hanaboso\AclBundle\Entity\Group;
use Hanaboso\AclBundle\Entity\Rule;
use Hanaboso\AclBundle\Exception\AclException;
use Hanaboso\CommonsBundle\Database\Locator\DatabaseManagerLocator;
use Hanaboso\UserBundle\Provider\ResourceProvider;
use Hanaboso\UserBundle\Provider\ResourceProviderException;

/**
 * Class RuleFactory
 *
 * @package Hanaboso\AclBundle\Factory
 */
class RuleFactory
{

    /**
     * @var mixed[]
     */
    private array $rules;

    /**
     * @var DocumentManager|EntityManager
     */
    private DocumentManager|EntityManager $dm;

    /**
     * @var mixed
     */
    private mixed $resource;

    /**
     * RuleFactory constructor.
     *
     * @param DatabaseManagerLocator $userDml
     * @param ResourceProvider       $provider
     * @param MaskFactory            $maskFactory
     * @param mixed[]                $rules
     * @param mixed                  $resEnum
     *
     * @throws AclException
     */
    function __construct(
        DatabaseManagerLocator $userDml,
        private readonly ResourceProvider $provider,
        private readonly MaskFactory $maskFactory,
        array $rules,
        mixed $resEnum,
    )
    {
        if (!array_key_exists('owner', $rules)) {
            throw new AclException(
                'Missing \'owner\' key in acl_rules.yml for default ruleset.',
                AclException::MISSING_DEFAULT_RULES,
            );
        }

        $this->dm       = $userDml->get();
        $this->rules    = $rules['owner'];
        $this->resource = $resEnum;
    }

    /**
     * @param Group|DmGroup $group
     *
     * @return Rule[]|DmRule[]
     * @throws AclException
     */
    public function getDefaultRules(Group|DmGroup $group): array
    {
        try {
            $this->dm->persist($group);

            // TODO ošetřit následnou změnu defaultních práv
            $rules = [];
            foreach ($this->rules as $key => $rule) {
                $this->resource::isValid($key);
                $ruleClass = $this->provider->getResource($this->resource::RULE);
                $actMask   = $this->maskFactory->maskActionFromYmlArray($rule, $this->resource::RULE);
                $rule      = self::createRule($key, $group, $actMask, 1, $ruleClass);
                $group->addRule($rule);
                $this->dm->persist($rule);

                $rules[] = $rule;
            }

            return $rules;
        } catch (ResourceProviderException | ORMException $e) {
            throw new AclException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param string        $resource
     * @param Group|DmGroup $group
     * @param int           $actMask
     * @param int           $propMask
     * @param string        $ruleClass
     *
     * @return Rule|DmRule
     */
    public static function createRule(
        string $resource,
        Group|DmGroup $group,
        int $actMask,
        int $propMask,
        string $ruleClass,
    ): Rule|DmRule
    {
        /** @var Rule|DmRule $rule */
        $rule = new $ruleClass();

        $rule
            ->setResource($resource)
            ->setGroup($group)
            ->setActionMask($actMask)
            ->setPropertyMask($propMask);

        $group->addRule($rule);

        return $rule;
    }

}
