<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\DataFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Hanaboso\AclBundle\Document\Group as DmGroup;
use Hanaboso\AclBundle\Document\Rule as DmRule;
use Hanaboso\AclBundle\Entity\Group;
use Hanaboso\AclBundle\Entity\Rule;
use Hanaboso\AclBundle\Enum\PropertyEnum;
use Hanaboso\AclBundle\Enum\ResourceEnum;
use Hanaboso\AclBundle\Factory\MaskFactory;
use Hanaboso\AclBundle\Repository\Entity\GroupRepository;
use Hanaboso\UserBundle\Document\User as DmUser;
use Hanaboso\UserBundle\Entity\User;
use Hanaboso\UserBundle\Provider\ResourceProvider;
use LogicException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;

/**
 * Class RoleFixtureAbstract
 *
 * @package Hanaboso\AclBundle\DataFixtures
 */
abstract class RoleFixtureAbstract implements FixtureInterface
{

    /**
     * @var ContainerInterface|null
     */
    protected ?ContainerInterface $container = NULL;

    /**
     * @var MaskFactory
     */
    protected MaskFactory $maskFactory;

    /**
     * @var int
     */
    protected int $encoderLevel = 12;

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(?ContainerInterface $container = NULL): void
    {
        /** @var ContainerInterface $cont */
        $cont            = $container;
        $this->container = $container;
        /** @var MaskFactory $factory */
        $factory           = $cont->get('hbpf.factory.mask');
        $this->maskFactory = $factory;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     *
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        if (!$this->container) {
            return;
        }

        $actEnum = $this->container->getParameter('action_enum');
        if (count($actEnum::getChoices()) > 32) {
            throw new LogicException('Amount of actions exceeded allowed 32!');
        }

        $encoder    = new NativePasswordHasher($this->encoderLevel);
        $rules      = $this->container->getParameter('acl_rule')['fixture_groups'];
        $ownerRules = $this->container->getParameter('acl_rule')['owner'];
        $config     = $this->container->getParameter('db_res');
        /** @var ResourceEnum $enum */
        $enum = $this->container->getParameter('resource_enum');

        $provider = new ResourceProvider($config);
        /** @phpstan-var class-string<Group|DmGroup> $groupClass */
        $groupClass = $provider->getResource($enum::GROUP);
        /** @phpstan-var class-string<User|DmUser> $userClass */
        $userClass = $provider->getResource($enum::USER);
        /** @phpstan-var class-string<Rule|DmRule> $ruleClass */
        $ruleClass = $provider->getResource($enum::RULE);

        $parentMap = [];

        /** @var GroupRepository $repo */
        $repo = $manager->getRepository($groupClass);
        foreach ($rules as $key => $val) {
            if ($repo->exists($key)) {
                continue;
            }

            /** @var Group|DmGroup $group */
            $group = new $groupClass(NULL);
            $group
                ->setName($key)
                ->setLevel($val['level'] ?? 999);
            $manager->persist($group);

            if (is_array($val['users'] ?? NULL)) {
                foreach ($val['users'] as $row) {
                    /** @var User|DmUser $user */
                    $user = new $userClass();
                    $user
                        ->setPassword($encoder->hash($row['password']))
                        ->setEmail($row['email']);
                    $manager->persist($user);
                    $group->addUser($user);
                }
            }
            if (is_array($val['rules'] ?? NULL)) {
                foreach ($val['rules'] as $res => $rights) {
                    $this->createRule(
                        $manager,
                        $group,
                        $rights,
                        $res,
                        $ruleClass,
                        MaskFactory::maskProperty(
                            [
                                PropertyEnum::GROUP => TRUE,
                                PropertyEnum::OWNER => TRUE,
                            ],
                        ),
                    );
                }
            }
            if (is_array($ownerRules)) {
                foreach ($ownerRules as $res => $rights) {
                    $this->createRule(
                        $manager,
                        $group,
                        $rights,
                        $res,
                        $ruleClass,
                        MaskFactory::maskProperty(
                            [
                                PropertyEnum::GROUP => FALSE,
                                PropertyEnum::OWNER => TRUE,
                            ],
                        ),
                    );
                }
            }

            $parentMap[$key]['pointer'] = $group;
            $parentMap[$key]['parents'] = [];
            if (is_array($val['extends'] ?? NULL)) {
                $parentMap[$key]['parents'] = $val['extends'];
            }

        }

        foreach ($parentMap as $data) {
            /** @var Group|DmGroup $group */
            $group = $data['pointer'];
            foreach ($data['parents'] as $parentName) {
                if (isset($parentMap[$parentName]['pointer'])) {
                    /** @var Group|DmGroup|null $parent */
                    $parent = $parentMap[$parentName]['pointer'];
                } else {
                    /** @var Group|DmGroup|null $parent */
                    $parent = $manager->getRepository($groupClass)->findOneBy(['name' => $parentName]);
                }

                if ($parent) {
                    $group->addParent($parent);
                }
            }
        }

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @param Group|DmGroup $group
     * @param mixed[]       $rights
     * @param string        $res
     * @param string        $ruleClass
     * @param int           $propertyMask
     */
    private function createRule(
        ObjectManager $manager,
        Group|DmGroup $group,
        array $rights,
        string $res,
        string $ruleClass,
        int $propertyMask,
    ): void
    {
        /** @var Rule|DmRule $rule */
        $rule = new $ruleClass();
        $rule
            ->setGroup($group)
            ->setActionMask($this->maskFactory->maskActionFromYmlArray($rights, $res))
            ->setResource($res)
            ->setPropertyMask($propertyMask);
        $manager->persist($rule);
        $group->addRule($rule);
    }

}
