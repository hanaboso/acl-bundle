<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\DataFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Exception;
use Hanaboso\AclBundle\Entity\GroupInterface;
use Hanaboso\AclBundle\Entity\RuleInterface;
use Hanaboso\AclBundle\Enum\PropertyEnum;
use Hanaboso\AclBundle\Factory\MaskFactory;
use Hanaboso\AclBundle\Repository\Entity\GroupRepository;
use Hanaboso\UserBundle\Entity\UserInterface;
use Hanaboso\UserBundle\Provider\ResourceProvider;
use LogicException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\NativePasswordEncoder;

/**
 * Class RoleFixtureAbstract
 *
 * @package Hanaboso\AclBundle\DataFixtures
 */
abstract class RoleFixtureAbstract implements FixtureInterface, ContainerAwareInterface
{

    /**
     * @var ContainerInterface|null
     */
    protected $container;

    /**
     * @var MaskFactory
     */
    protected $maskFactory;

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

        $encoder    = new NativePasswordEncoder(12);
        $rules      = $this->container->getParameter('acl_rule')['fixture_groups'];
        $ownerRules = $this->container->getParameter('acl_rule')['owner'];
        $config     = $this->container->getParameter('db_res');
        $enum       = $this->container->getParameter('resource_enum');

        $provider   = new ResourceProvider($config);
        $groupClass = $provider->getResource($enum::GROUP);
        $userClass  = $provider->getResource($enum::USER);
        $ruleClass  = $provider->getResource($enum::RULE);

        $parentMap = [];

        /** @var GroupRepository $repo */
        $repo = $manager->getRepository($groupClass);
        foreach ($rules as $key => $val) {
            if ($repo->exists($key)) {
                continue;
            }

            /** @var GroupInterface $group */
            $group = new $groupClass(NULL);
            $group
                ->setName($key)
                ->setLevel($val['level'] ?? 999);
            $manager->persist($group);

            if (is_array($val['users'] ?? NULL)) {
                foreach ($val['users'] as $row) {
                    /** @var UserInterface $user */
                    $user = new $userClass();
                    $user
                        ->setPassword($encoder->encodePassword($row['password'], ''))
                        ->setEmail($row['email']);
                    $manager->persist($user);
                    $group->addUser($user);
                }
            }
            if (is_array($val['rules'] ?? NULL)) {
                foreach ($val['rules'] as $res => $rights) {
                    $this->createRule($manager, $group, $rights, $res, $ruleClass, MaskFactory::maskProperty([
                        PropertyEnum::GROUP => TRUE,
                        PropertyEnum::OWNER => TRUE,
                    ]));
                }
            }
            if (is_array($ownerRules)) {
                foreach ($ownerRules as $res => $rights) {
                    $this->createRule($manager, $group, $rights, $res, $ruleClass, MaskFactory::maskProperty([
                        PropertyEnum::GROUP => FALSE,
                        PropertyEnum::OWNER => TRUE,
                    ]));
                }
            }

            $parentMap[$key]['pointer'] = $group;
            $parentMap[$key]['parents'] = [];
            if (is_array($val['extends'] ?? NULL)) {
                $parentMap[$key]['parents'] = $val['extends'];
            }

        }

        /** @var array $data */
        foreach ($parentMap as $data) {
            /** @var GroupInterface $group */
            $group = $data['pointer'];
            foreach ($data['parents'] as $parentName) {
                if (isset($parentMap[$parentName]['pointer'])) {
                    /** @var GroupInterface|null $parent */
                    $parent = $parentMap[$parentName]['pointer'];
                } else {
                    /** @var GroupInterface|null $parent */
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
     * @param ObjectManager  $manager
     * @param GroupInterface $group
     * @param array          $rights
     * @param string         $res
     * @param string         $ruleClass
     * @param int            $propertyMask
     */
    private function createRule(
        ObjectManager $manager,
        GroupInterface $group,
        array $rights,
        string $res,
        string $ruleClass,
        int $propertyMask
    ): void
    {
        /** @var RuleInterface $rule */
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
