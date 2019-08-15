<?php declare(strict_types=1);

namespace Tests\Integration\Reposity\Entity;

use Exception;
use Hanaboso\AclBundle\Entity\Group;
use Hanaboso\AclBundle\Repository\Entity\GroupRepository;
use Hanaboso\UserBundle\Entity\User;
use Tests\DatabaseTestCaseAbstract;

/**
 * Class GroupRepositoryTest
 *
 * @package Tests\Integration\Reposity\Entity
 */
final class GroupRepositoryTest extends DatabaseTestCaseAbstract
{

    /**
     * @covers GroupRepository::getUserGroups()
     *
     * @throws Exception
     */
    public function testUserGroups(): void
    {
        $em = self::$container->get('doctrine.orm.default_entity_manager');

        $user  = (new User())->setPassword('pwd')->setEmail('a@a.com');
        $user2 = (new User())->setPassword('pwd2')->setEmail('a2@a.com');
        $em->persist($user);
        $em->flush($user);
        $em->persist($user2);
        $em->flush($user2);

        $group3 = (new Group(NULL))->setName('qwe');
        $group  = (new Group($user))->addUser($user)->addUser($user2)->setName('asd')->addParent($group3);
        $group2 = (new Group($user2))->addUser($user2)->setName('asd');
        $em->persist($group);
        $em->persist($group2);
        $em->persist($group3);
        $em->flush();

        $em->clear();
        /** @var User $user */
        $user = $em->getRepository(User::class)->find($user->getId());
        /** @var GroupRepository $repo */
        $repo = $em->getRepository(Group::class);
        $res  = $repo->getUserGroups($user);
        self::assertEquals(2, count($res));

        /** @var GroupRepository $repo */
        $repo = $em->getRepository(Group::class);
        self::assertTrue($repo->exists('qwe'));
        self::assertFalse($repo->exists('eee'));
    }

}
