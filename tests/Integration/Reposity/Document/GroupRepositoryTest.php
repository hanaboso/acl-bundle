<?php declare(strict_types=1);

namespace Tests\Integration\Reposity\Document;

use Hanaboso\AclBundle\Document\Group;
use Hanaboso\AclBundle\Repository\Document\GroupRepository;
use Hanaboso\UserBundle\Document\User;
use Hanaboso\UserBundle\Repository\Document\UserRepository;
use Tests\DatabaseTestCaseAbstract;

/**
 * Class GroupRepositoryTest
 *
 * @package Tests\Integration\Reposity\Document
 */
class GroupRepositoryTest extends DatabaseTestCaseAbstract
{

    /**
     * @covers GroupRepository::getUserGroups()
     */
    public function testUserGroups(): void
    {
        $user  = new User();
        $user2 = new User();
        $this->persistAndFlush($user);
        $this->persistAndFlush($user2);

        $group  = (new Group($user))->addUser($user);
        $group2 = (new Group($user))->addUser($user2);
        $this->persistAndFlush($group);
        $this->persistAndFlush($group2);

        $this->dm->clear();
        /** @var UserRepository $rep */
        $rep = $this->dm->getRepository(User::class);
        /** @var User $user */
        $user = $rep->find($user->getId());
        /** @var GroupRepository $rep */
        $rep = $this->dm->getRepository(Group::class);
        $res = $rep->getUserGroups($user);
        self::assertEquals(1, count($res));
    }

}