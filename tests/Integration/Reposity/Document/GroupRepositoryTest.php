<?php declare(strict_types=1);

namespace AclBundleTests\Integration\Reposity\Document;

use AclBundleTests\DatabaseTestCaseAbstract;
use Exception;
use Hanaboso\AclBundle\Document\Group;
use Hanaboso\AclBundle\Repository\Document\GroupRepository;
use Hanaboso\UserBundle\Document\User;
use Hanaboso\UserBundle\Repository\Document\UserRepository;

/**
 * Class GroupRepositoryTest
 *
 * @package AclBundleTests\Integration\Reposity\Document
 *
 * @covers  \Hanaboso\AclBundle\Repository\Document\GroupRepository
 */
final class GroupRepositoryTest extends DatabaseTestCaseAbstract
{

    /**
     * @covers \Hanaboso\AclBundle\Repository\Document\GroupRepository::getUserGroups
     *
     * @throws Exception
     */
    public function testUserGroups(): void
    {
        $user  = new User();
        $user2 = new User();
        $this->pfd($user);
        $this->pfd($user2);

        $group4 = (new Group(NULL));
        $group3 = (new Group(NULL))->addParent($group4)->setName('qwe');
        $group  = (new Group($user))->addUser($user)->addParent($group3)->addParent($group4);
        $group2 = (new Group($user))->addUser($user2);
        $this->dm->persist($group);
        $this->dm->persist($group2);
        $this->dm->persist($group3);
        $this->dm->persist($group4);
        $this->dm->flush();

        $this->dm->clear();
        /** @var UserRepository $rep */
        $rep = $this->dm->getRepository(User::class);
        /** @var User $user */
        $user = $rep->find($user->getId());
        /** @var GroupRepository $rep */
        $rep = $this->dm->getRepository(Group::class);
        $res = $rep->getUserGroups($user);
        self::assertEquals(3, count($res));

        self::assertTrue($rep->exists('qwe'));
        self::assertFalse($rep->exists('eee'));
    }

}
