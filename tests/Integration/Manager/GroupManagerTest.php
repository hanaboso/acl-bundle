<?php declare(strict_types=1);

namespace AclBundleTests\Integration\Manager;

use AclBundleTests\DatabaseTestCaseAbstract;
use Exception;
use Hanaboso\AclBundle\Document\Group;
use Hanaboso\AclBundle\Document\Rule;
use Hanaboso\AclBundle\Exception\AclException;
use Hanaboso\AclBundle\Manager\GroupManager;
use Hanaboso\UserBundle\Document\TmpUser;
use Hanaboso\UserBundle\Document\User;

/**
 * Class GroupManagerTest
 *
 * @package AclBundleTests\Integration\Manager
 */
final class GroupManagerTest extends DatabaseTestCaseAbstract
{

    /**
     * @throws Exception
     */
    public function testAddUserIntoGroup(): void
    {
        $group = new Group(NULL);
        $group->setName('a');
        $this->pfd($group);

        $this->dm->clear();

        $tmpUser = new TmpUser();
        $tmpUser->setEmail('a@b.c');
        $this->pfd($tmpUser);

        /** @var GroupManager $man */
        $man = self::$container->get('hbpf.manager.group');

        $man->addUserIntoGroup($tmpUser, NULL, 'a');
        $this->dm->clear();

        /** @var Group $gr */
        $gr = $this->dm->getRepository(Group::class)->find($group->getId());
        self::assertCount(1, $gr->getTmpUsers());
    }

    /**
     * @throws Exception
     */
    public function testAddUserIntoGroup2(): void
    {
        $group = new Group(NULL);
        $group->setName('b');
        $this->pfd($group);

        $this->dm->clear();

        $user = new User();
        $user->setEmail('a@b.c');
        $this->pfd($user);

        /** @var GroupManager $man */
        $man = self::$container->get('hbpf.manager.group');

        $man->addUserIntoGroup($user, $group->getId());
        $this->dm->clear();

        /** @var Group $gr */
        $gr = $this->dm->getRepository(Group::class)->find($group->getId());
        self::assertCount(1, $gr->getUsers());
    }

    /**
     * @throws Exception
     */
    public function testAddUserIntoGroupFailed(): void
    {
        $group = new Group(NULL);
        $group->setName('b');
        $this->pfd($group);

        $this->dm->clear();

        $tmpUser = new TmpUser();
        $tmpUser->setEmail('a@b.c');
        $this->pfd($tmpUser);

        /** @var GroupManager $man */
        $man = self::$container->get('hbpf.manager.group');

        $this->expectException(AclException::class);
        $man->addUserIntoGroup($tmpUser);
    }

    /**
     * @throws Exception
     */
    public function testRemoveUserFromGroup(): void
    {
        $group = new Group(NULL);
        $group->setName('c');
        $this->pfd($group);

        $tmpUser = new TmpUser();
        $tmpUser->setEmail('a@b.c');
        $this->pfd($tmpUser);

        $group->addTmpUser($tmpUser);

        $tmpUser = new TmpUser();
        $tmpUser->setEmail('aa@b.c');
        $this->pfd($tmpUser);

        $group->addTmpUser($tmpUser);
        $this->dm->flush();

        self::assertCount(2, $group->getTmpUsers());

        $this->dm->clear();

        /** @var GroupManager $man */
        $man = self::$container->get('hbpf.manager.group');

        $man->removeUserFromGroup($tmpUser, $group->getId());

        /** @var Group $gr */
        $gr = $this->dm->getRepository(Group::class)->find($group->getId());
        self::assertCount(1, $gr->getTmpUsers());
    }

    /**
     * @throws Exception
     */
    public function testRemoveUserFromGroup2(): void
    {
        $user = new User();
        $user->setEmail('a@b.c');
        $this->pfd($user);

        $group = new Group($user);
        $group->setName('d');
        $this->pfd($group);

        $r = new Rule();
        $this->pfd($r);
        $group->addRule($r);
        $this->dm->flush();

        $this->dm->clear();

        /** @var GroupManager $man */
        $man = self::$container->get('hbpf.manager.group');

        $man->removeUserFromGroup($user, NULL, 'd');

        self::assertEmpty($this->dm->getRepository(Group::class)->findAll());
    }

    /**
     * @throws Exception
     */
    public function testRemoveUserFromGroupFailed(): void
    {
        $group = new Group(NULL);
        $group->setName('c');
        $this->pfd($group);

        $tmpUser = new TmpUser();
        $tmpUser->setEmail('a@b.c');
        $this->pfd($tmpUser);

        $group->addTmpUser($tmpUser);

        $tmpUser = new TmpUser();
        $tmpUser->setEmail('aa@b.c');
        $this->pfd($tmpUser);

        $group->addTmpUser($tmpUser);
        $this->dm->flush();

        self::assertCount(2, $group->getTmpUsers());

        $this->dm->clear();

        /** @var GroupManager $man */
        $man = self::$container->get('hbpf.manager.group');

        $this->expectException(AclException::class);
        $man->removeUserFromGroup($tmpUser);
    }

    /**
     * @throws Exception
     */
    public function testGetUserGroups(): void
    {
        $group = new Group(NULL);
        $group->setName('a');
        $this->pfd($group);

        $group2 = new Group(NULL);
        $group2->setName('b')->setLevel(1);
        $this->pfd($group2);

        $tmpUser = new TmpUser();
        $tmpUser->setEmail('a@b.c');
        $this->pfd($tmpUser);

        $group->addTmpUser($tmpUser);
        $group2->addTmpUser($tmpUser);

        $this->dm->flush();
        $this->dm->clear();
        self::assertCount(1, $group->getTmpUsers());

        /** @var TmpUser $u */
        $u = $this->dm->getRepository(TmpUser::class)->find($tmpUser->getId());

        /** @var Group $gr */
        $gr = $this->dm->getRepository(Group::class)->find($group->getId());
        self::assertCount(1, $gr->getTmpUsers());

        /** @var GroupManager $man */
        $man = self::$container->get('hbpf.manager.group');

        $res = $man->getUserGroups($u);
        self::assertNotEmpty($res);
        self::assertEquals(
            ['name' => $group->getName(), 'id' => $group->getId(), 'level' => $group->getLevel()],
            $res[0],
        );
    }

    /**
     * @throws Exception
     */
    public function testGetUserGroups2(): void
    {
        $group = new Group(NULL);
        $group->setName('a');
        $this->pfd($group);

        $group2 = new Group(NULL);
        $group2->setName('b')->setLevel(999);
        $this->pfd($group2);

        $user = new User();
        $user->setEmail('a@b.c');
        $this->pfd($user);

        $group->addUser($user);
        $group2->addUser($user);

        $this->dm->flush();
        $this->dm->clear();
        self::assertCount(1, $group->getUsers());

        /** @var TmpUser $u */
        $u = $this->dm->getRepository(User::class)->find($user->getId());

        /** @var Group $gr */
        $gr = $this->dm->getRepository(Group::class)->find($group->getId());
        self::assertCount(1, $gr->getUsers());

        /** @var GroupManager $man */
        $man = self::$container->get('hbpf.manager.group');

        $res = $man->getUserGroups($u);
        self::assertNotEmpty($res);
        self::assertEquals(
            ['name' => $group->getName(), 'id' => $group->getId(), 'level' => $group->getLevel()],
            $res[0],
        );
    }

}
