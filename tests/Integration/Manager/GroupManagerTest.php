<?php declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: radek.jirsa
 * Date: 11.5.18
 * Time: 17:30
 */

namespace Tests\Integration\Manager;

use Exception;
use Hanaboso\AclBundle\Document\Group;
use Hanaboso\AclBundle\Manager\GroupManager;
use Hanaboso\UserBundle\Document\TmpUser;
use Tests\DatabaseTestCaseAbstract;

/**
 * Class GroupManagerTest
 *
 * @package Tests\Integration\Manager
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
        $this->persistAndFlush($group);

        $this->dm->clear();

        $tmpUser = new TmpUser();
        $tmpUser->setEmail('a@b.c');
        $this->persistAndFlush($tmpUser);

        /** @var GroupManager $man */
        $man = $this->container->get('hbpf.manager.group');

        $man->addUserIntoGroup('a', $tmpUser);
        $this->dm->clear();

        /** @var Group $gr */
        $gr = $this->dm->getRepository(Group::class)->find($group->getId());
        self::assertCount(1, $gr->getTmpUsers());
    }

    /**
     * @throws Exception
     */
    public function testRemoveUserFromGroup(): void
    {
        $group = new Group(NULL);
        $group->setName('a');
        $this->persistAndFlush($group);

        $tmpUser = new TmpUser();
        $tmpUser->setEmail('a@b.c');
        $this->persistAndFlush($tmpUser);

        $group->addTmpUser($tmpUser);

        $tmpUser = new TmpUser();
        $tmpUser->setEmail('aa@b.c');
        $this->persistAndFlush($tmpUser);

        $group->addTmpUser($tmpUser);
        $this->dm->flush();


        self::assertCount(2, $group->getTmpUsers());

        $this->dm->clear();

        /** @var GroupManager $man */
        $man = $this->container->get('hbpf.manager.group');

        $man->removeUserFromGroup('a', $tmpUser);

        /** @var Group $gr */
        $gr = $this->dm->getRepository(Group::class)->find($group->getId());
        self::assertCount(1, $gr->getTmpUsers());
    }

    /**
     * @throws Exception
     */
    public function testGetUserGroups(): void
    {
        $group = new Group(NULL);
        $group->setName('a');
        $this->persistAndFlush($group);

        $tmpUser = new TmpUser();
        $tmpUser->setEmail('a@b.c');
        $this->persistAndFlush($tmpUser);

        $group->addTmpUser($tmpUser);

        $this->dm->flush();
        $this->dm->clear();
        self::assertCount(1, $group->getTmpUsers());


        /** @var TmpUser $u */
        $u = $this->dm->getRepository(TmpUser::class)->find($tmpUser->getId());

        /** @var Group $gr */
        $gr = $this->dm->getRepository(Group::class)->find($group->getId());
        self::assertCount(1, $gr->getTmpUsers());

        /** @var GroupManager $man */
        $man = $this->container->get('hbpf.manager.group');

        $res = $man->getUserGroups($u);
        self::assertEquals(['a'], $res);
    }

}