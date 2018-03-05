<?php declare(strict_types=1);

namespace Tests\Integration\Reposity\Entity;

use Hanaboso\AclBundle\Entity\Group;
use Hanaboso\UserBundle\Entity\User;
use Tests\DatabaseTestCaseAbstract;

/**
 * Class GroupRepositoryTest
 *
 * @package Tests\Integration\Reposity\Entity
 */
class GroupRepositoryTest extends DatabaseTestCaseAbstract
{

    /**
     * @covers GroupRepository::getUserGroups()
     */
    public function testUserGroups(): void
    {
        $em = $this->container->get('doctrine.orm.default_entity_manager');

        $user  = (new User())->setPassword('pwd')->setEmail('a@a.com');
        $user2 = (new User())->setPassword('pwd2')->setEmail('a2@a.com');
        $em->persist($user);
        $em->flush($user);
        $em->persist($user2);
        $em->flush($user2);

        $group  = (new Group($user))->addUser($user)->addUser($user2)->setName('asd');
        $group2 = (new Group($user2))->addUser($user2)->setName('asd');
        $em->persist($group);
        $em->flush($group);
        $em->persist($group2);
        $em->flush($group2);

        $em->clear();
        /** @var User $user */
        $user = $em->getRepository(User::class)->find($user->getId());
        $res  = $em->getRepository(Group::class)->getUserGroups($user);
        self::assertEquals(1, count($res));
    }

}