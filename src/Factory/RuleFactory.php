<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Factory;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Hanaboso\AclBundle\Document\Rule as OdmRule;
use Hanaboso\AclBundle\Entity\GroupInterface;
use Hanaboso\AclBundle\Entity\Rule as OrmRule;
use Hanaboso\AclBundle\Entity\RuleInterface;
use Hanaboso\AclBundle\Exception\AclException;
use Hanaboso\CommonsBundle\DatabaseManager\DatabaseManagerLocator;

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
     * RuleFactory constructor.
     *
     * @param DatabaseManagerLocator $userDml
     * @param array                  $rules
     * @param mixed                  $resEnum
     *
     * @throws AclException
     */
    function __construct(DatabaseManagerLocator $userDml, array $rules, $resEnum)
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
    }

    /**
     * @param string         $resource
     * @param GroupInterface $group
     * @param int            $actMask
     * @param int            $propMask
     *
     * @return RuleInterface
     * @throws AclException
     */
    public static function createRule(string $resource, GroupInterface $group, int $actMask,
                                      int $propMask): RuleInterface
    {
        if ($group->getType() === GroupInterface::TYPE_ORM) {
            $rule = new OrmRule();
        } else {
            $rule = new OdmRule();
        }

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

            $actMask = MaskFactory::maskActionFromYmlArray($rule);
            $rule    = self::createRule($key, $group, $actMask, 1);
            $group->addRule($rule);
            $this->dm->persist($rule);

            $rules[] = $rule;
        }

        return $rules;
    }

}