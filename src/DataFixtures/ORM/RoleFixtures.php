<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Hanaboso\AclBundle\Entity\Group;
use Hanaboso\AclBundle\Entity\Rule;
use Hanaboso\AclBundle\Factory\MaskFactory;
use Hanaboso\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;

/**
 * Class RoleFixtures
 *
 * @package Hanaboso\AclBundle\DataFixtures
 */
class RoleFixtures implements FixtureInterface, ContainerAwareInterface
{

    /**
     * @var ContainerInterface|null
     */
    private $container;

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(?ContainerInterface $container = NULL): void
    {
        $this->container = $container;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        if (!$this->container) {
            return;
        }

        $encoder    = new BCryptPasswordEncoder(12);
        $rules      = $this->container->getParameter('acl_rule')['fixture_groups'];
        $ownerRules = $this->container->getParameter('acl_rule')['owner'];

        foreach ($rules as $key => $val) {
            $group = new Group(NULL);
            $group
                ->setName($key)
                ->setLevel($val['level']);
            $manager->persist($group);

            if (is_array($val['users'])) {
                foreach ($val['users'] as $row) {
                    $user = new User();
                    $user
                        ->setPassword($encoder->encodePassword($row['password'], ''))
                        ->setEmail($row['email']);
                    $manager->persist($user);
                    $group->addUser($user);
                }
            }
            if (is_array($val['rules'])) {
                foreach ($val['rules'] as $res => $rights) {
                    $this->createRule($manager, $group, $rights, $res);
                }
            }
            if (is_array($ownerRules)) {
                foreach ($ownerRules as $res => $rights) {
                    $this->createRule($manager, $group, $rights, $res);
                }
            }

        }

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @param Group         $group
     * @param array         $rights
     * @param string        $res
     */
    private function createRule(ObjectManager $manager, Group $group, array $rights, string $res): void
    {
        $rule = new Rule();
        $rule
            ->setGroup($group)
            ->setActionMask(MaskFactory::maskActionFromYmlArray($rights))
            ->setResource($res)
            ->setPropertyMask(2);
        $manager->persist($rule);
        $group->addRule($rule);
    }

}