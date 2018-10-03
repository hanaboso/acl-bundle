<?php declare(strict_types=1);

namespace Tests\Integration\Manager;

use Exception;
use Hanaboso\AclBundle\Document\Group;
use Hanaboso\AclBundle\Document\Rule;
use Hanaboso\AclBundle\Dto\GroupDto;
use Hanaboso\AclBundle\Entity\Group as ORMGroup;
use Hanaboso\AclBundle\Exception\AclException;
use Hanaboso\AclBundle\Manager\AccessManager;
use Hanaboso\CommonsBundle\Exception\EnumException;
use Hanaboso\CommonsBundle\FileStorage\Document\File;
use Hanaboso\UserBundle\Document\User;
use Tests\DatabaseTestCaseAbstract;
use Tests\PrivateTrait;

/**
 * Class AccessManagerTest
 *
 * @package Tests\Integration\Manager
 */
final class AccessManagerTest extends DatabaseTestCaseAbstract
{

    use PrivateTrait;

    /**
     * @covers AccessManager::isAllowed()
     * @covers AccessManager::throwPermissionException()
     *
     * @throws Exception
     */
    public function testWrongObjectArray(): void
    {
        $user = $this->createUser('wrongOne');
        $this->createRule($user);
        $this->expect();
        $this->c->get('hbpf.access.manager')->isAllowed('write', 'group', $user, []);
    }

    /**
     * @covers AccessManager::isAllowed()
     *
     * @throws Exception
     */
    public function testWrongObjectBool(): void
    {
        $user = $this->createUser('wrongOne');
        $this->createRule($user);
        $this->expect();
        $this->c->get('hbpf.access.manager')->isAllowed('read', 'group', $user, TRUE);
    }

    /**
     * @covers AccessManager::isAllowed()
     * @covers AccessManager::checkParams()
     *
     * @throws Exception
     */
    public function testWrongResourceAction(): void
    {
        $user = $this->createUser('resource');
        $this->createRule($user);
        $this->expect(EnumException::INVALID_CHOICE, EnumException::class);
        $this->c->get('hbpf.access.manager')->isAllowed('writdde', 'group', $user, NULL);
    }

    /**
     * @covers AccessManager::isAllowed()
     * @covers AccessManager::checkParams()
     *
     * @throws Exception
     */
    public function testWrongResourceResource(): void
    {
        $user = $this->createUser('resource');
        $this->createRule($user);
        $this->expect(EnumException::INVALID_CHOICE, EnumException::class);
        $this->c->get('hbpf.access.manager')->isAllowed('write', 'grosdup', $user, NULL);
    }

    /**
     * @covers AccessManager::isAllowed()
     * @covers AccessManager::checkParams()
     * @covers AccessManager::hasRight()
     * @covers AccessManager::selectRule()
     *
     * @throws Exception
     */
    public function testReadPermission(): void
    {
        $user = $this->createUser('readPer');
        $this->createRule($user, 3, 'group', 2);
        self::assertTrue($this->c->get('hbpf.access.manager')->isAllowed('read', 'group', $user, NULL));
    }

    /**
     * @covers AccessManager::isAllowed()
     * @covers AccessManager::checkParams()
     * @covers AccessManager::hasRight()
     * @covers AccessManager::selectRule()
     *
     * @throws Exception
     */
    public function testReadPermissionNotAllowed(): void
    {
        $user = $this->createUser('nullReadPer');
        $this->createRule($user, 3, 'group', 1);
        $this->expect();
        $this->c->get('hbpf.access.manager')->isAllowed('read', 'group', $user, NULL);
    }

    /**
     * @covers AccessManager::isAllowed()
     * @covers AccessManager::checkParams()
     * @covers AccessManager::hasRight()
     * @covers AccessManager::selectRule()
     *
     * @throws Exception
     */
    public function testWritePermission(): void
    {
        $user = $this->createUser('writePer');
        $this->createRule($user, 2, 'group', 1);
        self::assertTrue($res = $this->c->get('hbpf.access.manager')->isAllowed('write', 'group', $user, NULL));
    }

    /**
     * @covers AccessManager::isAllowed()
     * @covers AccessManager::checkParams()
     * @covers AccessManager::hasRight()
     * @covers AccessManager::selectRule()
     *
     * @throws Exception
     */
    public function testWritePermissionNotAllowed(): void
    {
        $user = $this->createUser('nullWritePer');
        $this->createRule($user, 1, 'group', 2);
        $this->expect();
        $this->c->get('hbpf.access.manager')->isAllowed('write', 'group', $user, NULL);
    }

    /**
     * @covers AccessManager::isAllowed()
     * @covers AccessManager::checkParams()
     * @covers AccessManager::hasRight()
     * @covers AccessManager::selectRule()
     *
     * @throws Exception
     */
    public function testDeletePermission(): void
    {
        $user = $this->createUser('deletePer');
        $this->createRule($user, 6, 'group', 2);
        self::assertTrue($this->c->get('hbpf.access.manager')->isAllowed('delete', 'group', $user, NULL));
    }

    /**
     * @covers AccessManager::isAllowed()
     * @covers AccessManager::checkParams()
     * @covers AccessManager::hasRight()
     * @covers AccessManager::selectRule()
     *
     * @throws Exception
     */
    public function testDeletePermissionNotAllowed(): void
    {
        $user = $this->createUser('nullDeletePer');
        $this->createRule($user, 6, 'group', 1);
        $this->expect();
        $this->c->get('hbpf.access.manager')->isAllowed('delete', 'group', $user, NULL);
    }

    /**
     * @covers AccessManager::isAllowed()
     * @covers AccessManager::checkParams()
     * @covers AccessManager::hasRight()
     * @covers AccessManager::selectRule()
     * @covers AccessManager::checkObjectPermission()
     *
     * @throws Exception
     */
    public function testObjNonOwnerRight(): void
    {
        $tser  = $this->createUser('objTwner');
        $group = new Group($tser);
        $this->persistAndFlush($group);
        $user = $this->createUser('objOwner');

        $this->createRule($user, 7, 'group', 1);
        $this->expect();
        $this->c->get('hbpf.access.manager')->isAllowed('read', 'group', $user, $group);
    }

    /**
     * @covers AccessManager::isAllowed()
     * @covers AccessManager::checkParams()
     * @covers AccessManager::hasRight()
     * @covers AccessManager::selectRule()
     * @covers AccessManager::checkObjectPermission()
     * @covers AccessManager::getObjectById()
     *
     * @throws Exception
     */
    public function testIdNonOwnerRight(): void
    {
        $tser  = $this->createUser('objTidner');
        $group = new Group($tser);
        $this->persistAndFlush($group);
        $user = $this->createUser('objOwner');

        $this->createRule($user, 7, 'group', 1);
        $this->expect();
        $this->c->get('hbpf.access.manager')->isAllowed('read', 'group', $user, $group->getId());
    }

    /**
     * @covers AccessManager::isAllowed()
     * @covers AccessManager::checkParams()
     * @covers AccessManager::hasRight()
     * @covers AccessManager::selectRule()
     * @covers AccessManager::checkObjectPermission()
     * @covers AccessManager::getObjectById()
     *
     * @throws Exception
     */
    public function testObjOwnerRight(): void
    {
        $user  = $this->createUser('objOwner');
        $group = new Group($user);
        $this->persistAndFlush($group);
        $this->createRule($user, 7, 'group', 1);
        $res = $this->c->get('hbpf.access.manager')->isAllowed('read', 'group', $user, $group);
        self::assertInstanceOf(Group::class, $res);
        $res = $this->c->get('hbpf.access.manager')->isAllowed('read', 'group', $user, $group->getId());
        self::assertInstanceOf(Group::class, $res);
    }

    /**
     * @covers AccessManager::isAllowed()
     * @covers AccessManager::checkParams()
     * @covers AccessManager::hasRight()
     * @covers AccessManager::selectRule()
     * @covers AccessManager::checkObjectPermission()
     * @covers AccessManager::getObjectById()
     *
     * @throws Exception
     */
    public function testObjWithoutOwnerAllowed(): void
    {
        $user = $this->createUser('noOwnerAllow');
        $this->createRule($user, 7, 'file', 1);
        $file = new File();
        $this->persistAndFlush($file);
        $res = $this->c->get('hbpf.access.manager')->isAllowed('read', 'file', $user, $file);
        self::assertInstanceOf(File::class, $res);
        $res = $this->c->get('hbpf.access.manager')->isAllowed('read', 'file', $user, $file->getId());
        self::assertInstanceOf(File::class, $res);
    }

    /**
     * @covers AccessManager::isAllowed()
     * @covers AccessManager::checkParams()
     * @covers AccessManager::hasRight()
     * @covers AccessManager::selectRule()
     * @covers AccessManager::checkObjectPermission()
     *
     * @throws Exception
     */
    public function testObjWithoutOwnerNotAllowed(): void
    {
        $user = $this->createUser('noOwnerAllow');
        $this->createRule($user, 2, 'file', 2);
        $file = new File();
        $this->persistAndFlush($file);
        $this->expect();
        $this->c->get('hbpf.access.manager')->isAllowed('read', 'file', $user, $file->getId());
    }

    /**
     * @covers AccessManager::isAllowed()
     * @covers AccessManager::checkParams()
     * @covers AccessManager::hasRight()
     * @covers AccessManager::selectRule()
     * @covers AccessManager::checkObjectPermission()
     * @covers AccessManager::getObjectById()
     * @covers AccessManager::checkGroupLvl()
     *
     * @throws Exception
     */
    public function testGroupAllowed(): void
    {
        $user = $this->createUser('groupAllowed');
        $this->createRule($user, 1, 'group', 2);
        $group = new Group(NULL);
        $this->persistAndFlush($group);
        $res = $this->c->get('hbpf.access.manager')->isAllowed('read', 'group', $user, $group);
        self::assertInstanceOf(Group::class, $res);
        $res = $this->c->get('hbpf.access.manager')->isAllowed('read', 'group', $user, $group->getId());
        self::assertInstanceOf(Group::class, $res);
    }

    /**
     * @covers AccessManager::isAllowed()
     * @covers AccessManager::checkParams()
     * @covers AccessManager::hasRight()
     * @covers AccessManager::selectRule()
     * @covers AccessManager::checkObjectPermission()
     * @covers AccessManager::checkGroupLvl()
     *
     * @throws Exception
     */
    public function testGroupNotAllowed(): void
    {
        $user = $this->createUser('groupAllowed');
        $this->createRule($user, 1, 'group', 2);
        $group = new Group($user);
        $this->persistAndFlush($group);
        $this->expect();
        $this->c->get('hbpf.access.manager')->isAllowed('write', 'group', $user, $group);
    }

    /**
     * @covers AccessManager::isAllowed()
     * @covers AccessManager::checkParams()
     * @covers AccessManager::hasRight()
     * @covers AccessManager::selectRule()
     * @covers AccessManager::checkObjectPermission()
     * @covers AccessManager::hasRightForGroup()
     *
     * @throws Exception
     */
    public function testGroupLvlAllowed(): void
    {
        $user = $this->createUser('groupAllowed');
        $this->createRule($user, 7, 'group', 2);
        $group = new Group($user);
        $this->persistAndFlush($group->setLevel(8));
        $res = $this->c->get('hbpf.access.manager')->isAllowed('write', 'group', $user, $group);
        self::assertInstanceOf(Group::class, $res);
    }

    /**
     * @covers AccessManager::isAllowed()
     * @covers AccessManager::checkParams()
     * @covers AccessManager::selectRule()
     * @covers AccessManager::checkObjectPermission()
     * @covers AccessManager::hasRightForGroup()
     *
     * @throws Exception
     */
    public function testGroupLvlNotAllowed(): void
    {
        $user = $this->createUser('groupNotAllowed');
        $this->createRule($user, 7, 'group', 2);
        $group = new Group($user);
        $this->persistAndFlush($group->setLevel(0));
        $this->expect();
        $this->c->get('hbpf.access.manager')->isAllowed('write', 'group', $user, $group);
    }

    /**
     * @covers AccessManager::isAllowed
     * @covers AccessManager::checkParams()
     * @covers AccessManager::selectRule()
     * @covers AccessManager::checkObjectPermission()
     * @covers AccessManager::hasRightForUser()
     *
     * @throws Exception
     */
    public function testUserLvlAllowed(): void
    {
        $user = $this->createUser('userAllo');
        $this->createRule($user, 7, 'user', 2);
        $tser = $this->createUserLvl('tserAllo', 55);
        $res  = $this->c->get('hbpf.access.manager')->isAllowed('write', 'user', $user, $tser);
        self::assertInstanceOf(User::class, $res);
    }

    /**
     * @covers AccessManager::isAllowed()
     * @covers AccessManager::checkParams()
     * @covers AccessManager::selectRule()
     * @covers AccessManager::checkObjectPermission()
     * @covers AccessManager::hasRightForUser()
     *
     * @throws Exception
     */
    public function testUserLvlNotAllowed(): void
    {
        $user = $this->createUser('userNotAllo');
        $this->createRule($user, 7, 'user', 2);
        $tser = $this->createUserLvl('tserNotAllo', 0);
        $this->expect();
        $this->c->get('hbpf.access.manager')->isAllowed('write', 'user', $user, $tser);
    }

    /**
     * @covers AccessManager::isAllowed()
     * @covers AccessManager::checkParams()
     * @covers AccessManager::selectRule()
     * @covers AccessManager::checkObjectPermission()
     *
     * @throws Exception
     */
    public function testClassPermision(): void
    {
        $user = $this->createUser('class');
        $this->createRule($user, 7, 'group', 2);
        $this->expect();
        $this->c->get('hbpf.access.manager')->isAllowed('write', 'group', $user, Group::class);
    }

    /**
     * @covers AccessManager::addGroup()
     * @covers AccessManager::updateGroup()
     *
     * @throws Exception
     */
    public function testAddAndUpdateGroup(): void
    {
        $user = new User();
        $user
            ->setEmail('test@test.com')
            ->setPassword('pwd');
        $this->persistAndFlush($user);

        $this->createRule($user);

        $access = $this->c->get('hbpf.access.manager');
        $access->addGroup('newGroup');
        /** @var Group $group */
        $group = $this->dm->getRepository(Group::class)->findOneBy(['name' => 'newGroup']);
        self::assertInstanceOf(Group::class, $group);

        $data = new GroupDto($group);
        $data
            ->addUser($user)
            ->addRule(Rule::class, [
                [
                    'resource'      => 'user',
                    'action_mask'   => [
                        'write'  => 1,
                        'read'   => 1,
                        'delete' => 1,
                    ],
                    'property_mask' => [
                        'owner' => 1,
                        'group' => 1,
                    ],
                ],
                [
                    'resource'      => 'group',
                    'action_mask'   => [
                        'write'  => 1,
                        'read'   => 1,
                        'delete' => 1,
                    ],
                    'property_mask' => [
                        'owner' => 1,
                        'group' => 1,
                    ],
                ],
            ]);
        $group = $access->updateGroup($data);

        self::assertInstanceOf(Group::class, $group);
        self::assertEquals(2, count($group->getRules()));
    }

    /**
     * @covers AccessManager::removeGroup()
     *
     * @throws Exception
     */
    public function testRemoveGroup(): void
    {
        $rule = $this->createRule();

        $group = new Group(NULL);
        $group->setName('gtest');
        $this->dm->persist($group);
        $group->addParent($rule->getGroup());
        $this->dm->flush();
        $this->dm->clear();

        $this->c->get('hbpf.access.manager')->removeGroup(
            $this->dm->find(Group::class, $rule->getGroup()->getId())
        );
        self::assertEmpty($this->dm->getRepository(Rule::class)->findAll());
    }

    /**
     * @covers AccessManager::removeGroup()
     *
     * @throws Exception
     */
    public function testRemoveGroupsMysql(): void
    {
        $this->clearMysql();

        $group = new ORMGroup(NULL);
        $group2 = new ORMGroup(NULL);
        $group->setName('gtest');
        $group2->setName('gtest2');
        $this->em->persist($group);
        $this->em->persist($group2);
        $group->addParent($group2);
        $this->em->flush();
        $this->em->clear();

        /** @var AccessManager $access */
        $access = $this->c->get('hbpf.access.manager');
        $this->setProperty($access, 'dm', $this->em);

        $access->removeGroup(
            $this->em->find(ORMGroup::class, $group2->getId())
        );
        self::assertEmpty($this->em->find(ORMGroup::class, $group->getId())->getParents());
    }

    /**
     * @param User|null $user
     * @param int       $act
     * @param string    $res
     * @param int       $prop
     *
     * @return Rule
     */
    private function createRule(?User $user = NULL, int $act = 7, string $res = 'group', int $prop = 2): Rule
    {
        $group = new Group($user);
        $rule  = new Rule();

        $rule
            ->setGroup($group->setLevel(5))
            ->setResource($res)
            ->setActionMask($act)
            ->setPropertyMask($prop);
        $group
            ->addRule($rule)
            ->setName('group');
        if ($user) {
            $group->addUser($user);
        }

        $this->dm->persist($rule);
        $this->dm->persist($group);
        $this->dm->flush();

        return $rule;
    }

    /**
     * @param string $usr
     *
     * @return User
     */
    private function createUser(string $usr = 'test@test.com'): User
    {
        $user = new User();
        $user
            ->setEmail($usr)
            ->setPassword('pwd');
        $this->persistAndFlush($user);

        return $user;
    }

    /**
     * @param string $usr
     * @param int    $lvl
     *
     * @return User
     */
    private function createUserLvl(string $usr, int $lvl): User
    {
        $user = new User();
        $user
            ->setEmail($usr)
            ->setPassword('pwd');
        $this->persistAndFlush($user);

        $group = new Group($user);
        $group->setLevel($lvl)->addUser($user);

        $this->persistAndFlush($group);

        return $user;
    }

    /**
     * @param int    $code
     * @param string $class
     */
    private function expect(int $code = AclException::PERMISSION, string $class = AclException::class): void
    {
        $this->expectException($class);
        $this->expectExceptionCode($code);
    }

}