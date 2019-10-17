<?php declare(strict_types=1);

namespace AclBundleTests\Integration\Manager;

use AclBundleTests\DatabaseTestCaseAbstract;
use Exception;
use Hanaboso\AclBundle\Document\Group;
use Hanaboso\AclBundle\Document\Rule;
use Hanaboso\AclBundle\Dto\GroupDto;
use Hanaboso\AclBundle\Entity\Group as ORMGroup;
use Hanaboso\AclBundle\Entity\GroupInterface;
use Hanaboso\AclBundle\Enum\ActionEnum;
use Hanaboso\AclBundle\Enum\PropertyEnum;
use Hanaboso\AclBundle\Enum\ResourceEnum;
use Hanaboso\AclBundle\Exception\AclException;
use Hanaboso\AclBundle\Factory\MaskFactory;
use Hanaboso\AclBundle\Manager\AccessManager;
use Hanaboso\CommonsBundle\Exception\EnumException;
use Hanaboso\CommonsBundle\FileStorage\Document\File;
use Hanaboso\UserBundle\Document\User;
use Predis\Client;

/**
 * Class AccessManagerTest
 *
 * @package AclBundleTests\Integration\Manager
 */
final class AccessManagerTest extends DatabaseTestCaseAbstract
{

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->em = self::$container->get('doctrine.orm.default_entity_manager');
        $this->clearMysql();

        $redis = new Client([
            'host' => self::$container->getParameter('redis_host'),
            'port' => self::$container->getParameter('redis_port'),
        ]);
        $redis->connect();
        $redis->flushall();
    }

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
        self::$container->get('hbpf.access.manager')->isAllowed('write', 'group', $user, []);
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
        self::$container->get('hbpf.access.manager')->isAllowed('read', 'group', $user, TRUE);
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
        self::$container->get('hbpf.access.manager')->isAllowed('writdde', 'group', $user, NULL);
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
        self::$container->get('hbpf.access.manager')->isAllowed('write', 'grosdup', $user, NULL);
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
        self::assertTrue(self::$container->get('hbpf.access.manager')->isAllowed('read', 'group', $user, NULL));
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
        self::$container->get('hbpf.access.manager')->isAllowed('read', 'group', $user, NULL);
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
        self::assertTrue($res = self::$container->get('hbpf.access.manager')->isAllowed('write', 'group', $user, NULL));
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
        self::$container->get('hbpf.access.manager')->isAllowed('write', 'group', $user, NULL);
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
        self::assertTrue(self::$container->get('hbpf.access.manager')->isAllowed('delete', 'group', $user, NULL));
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
        self::$container->get('hbpf.access.manager')->isAllowed('delete', 'group', $user, NULL);
    }

    /**
     * @covers AccessManager::isAllowed()
     * @covers AccessManager::checkParams()
     * @covers AccessManager::hasRight()
     * @covers AccessManager::selectRule()
     *
     * @throws Exception
     */
    public function testMissingResourceAction(): void
    {
        $user = $this->createUser('nullReadPer');
        $this->createRule($user, 3, 'group', 2);
        $this->expect(AclException::INVALID_ACTION);
        self::$container->get('hbpf.access.manager')->isAllowed('test2', 'group', $user, NULL);
    }

    /**
     * @covers AccessManager::isAllowed()
     * @covers AccessManager::checkParams()
     * @covers AccessManager::hasRight()
     * @covers AccessManager::selectRule()
     *
     * @throws Exception
     */
    public function testExtraDefaultResourceAction(): void
    {
        $user = $this->createUser('nullReadPer');
        $this->createRule($user, 11, 'group', 2);
        $r = self::$container->get('hbpf.access.manager')->isAllowed('test', 'group', $user, NULL);
        self::assertNotEmpty($r);
    }

    /**
     * @covers AccessManager::isAllowed()
     * @covers AccessManager::checkParams()
     * @covers AccessManager::hasRight()
     * @covers AccessManager::selectRule()
     *
     * @throws Exception
     */
    public function testExtraResourceAction(): void
    {
        $user = $this->createUser('nullReadPer');
        $this->createRule($user, 19, 'token', 2);
        $r = self::$container->get('hbpf.access.manager')->isAllowed('test2', 'token', $user, NULL);
        self::assertNotEmpty($r);
    }

    /**
     * @covers AccessManager::isAllowed()
     * @covers AccessManager::checkParams()
     *
     * @throws Exception
     */
    public function testGlobalActionExtended(): void
    {
        $user = $this->createUser('nullReadPer');
        $this->createRule($user, 11, 'token', 1);
        $r = self::$container->get('hbpf.access.manager')->isAllowed('test', 'token', $user, NULL);
        self::assertNotEmpty($r);
    }

    /**
     * @covers AccessManager::isAllowed()
     * @covers AccessManager::checkParams()
     *
     * @throws Exception
     */
    public function testGlobalActionRestriction(): void
    {
        $user = $this->createUser('nullReadPer');
        $this->createRule($user, 19, 'token', 1);
        $this->expect();
        self::$container->get('hbpf.access.manager')->isAllowed('test2', 'token', $user, NULL);
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
        $this->pfd($group);
        $user = $this->createUser('objOwner');

        $this->createRule($user, 7, 'group', 1);
        $this->expect();
        self::$container->get('hbpf.access.manager')->isAllowed('read', 'group', $user, $group);
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
        $this->pfd($group);
        $user = $this->createUser('objOwner');

        $this->createRule($user, 7, 'group', 1);
        $this->expect();
        self::$container->get('hbpf.access.manager')->isAllowed('read', 'group', $user, $group->getId());
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
        $this->pfd($group);
        $this->createRule($user, 7, 'group', 1);
        $res = self::$container->get('hbpf.access.manager')->isAllowed('read', 'group', $user, $group);
        self::assertInstanceOf(Group::class, $res);
        $res = self::$container->get('hbpf.access.manager')->isAllowed('read', 'group', $user, $group->getId());
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
        $this->pfd($file);
        $res = self::$container->get('hbpf.access.manager')->isAllowed('read', 'file', $user, $file);
        self::assertInstanceOf(File::class, $res);
        $res = self::$container->get('hbpf.access.manager')->isAllowed('read', 'file', $user, $file->getId());
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
        $this->pfd($file);
        $this->expect();
        self::$container->get('hbpf.access.manager')->isAllowed('read', 'file', $user, $file->getId());
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
        $this->pfd($group);
        $res = self::$container->get('hbpf.access.manager')->isAllowed('read', 'group', $user, $group);
        self::assertInstanceOf(Group::class, $res);
        $res = self::$container->get('hbpf.access.manager')->isAllowed('read', 'group', $user, $group->getId());
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
        $this->pfd($group);
        $this->expect();
        self::$container->get('hbpf.access.manager')->isAllowed('write', 'group', $user, $group);
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
        $this->pfd($group->setLevel(8));
        $res = self::$container->get('hbpf.access.manager')->isAllowed('write', 'group', $user, $group);
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
        $this->pfd($group->setLevel(0));
        $this->expect();
        self::$container->get('hbpf.access.manager')->isAllowed('write', 'group', $user, $group);
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
        $res  = self::$container->get('hbpf.access.manager')->isAllowed('write', 'user', $user, $tser);
        self::assertInstanceOf(User::class, $res);
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
    public function testAllowedGroupLvlUnderOwnerLvl(): void
    {
        $user = $this->createUser('usr01');
        $rule = $this->createRule($user, 7, ResourceEnum::USER, 1);

        $group = new Group(NULL);
        $group->setLevel(55)
            ->addChild($rule->getGroup()->addParent($group));
        $this->dm->persist($group);

        $rule = new Rule();
        $rule->setActionMask(7)
            ->setGroup($group)
            ->setPropertyMask(2)
            ->setResource(ResourceEnum::USER);
        $this->dm->persist($rule);
        $group->addRule($rule);
        $this->dm->flush();

        $res = self::$container->get('hbpf.access.manager')->isAllowed(ActionEnum::READ, 'user', $user);
        self::assertTrue($res);
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
        self::$container->get('hbpf.access.manager')->isAllowed('write', 'user', $user, $tser);
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
        self::$container->get('hbpf.access.manager')->isAllowed('write', 'group', $user, Group::class);
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
        $this->pfd($user);

        $this->createRule($user);

        $access = self::$container->get('hbpf.access.manager');
        $access->addGroup('newGroup');
        /** @var Group $group */
        $group = $this->dm->getRepository(Group::class)->findOneBy(['name' => 'newGroup']);
        self::assertInstanceOf(Group::class, $group);

        /** @var MaskFactory $maskFactory */
        $maskFactory = self::$container->get('hbpf.factory.mask');

        $data = new GroupDto($group);
        $data
            ->addUser($user)
            ->addRule(Rule::class, [
                [
                    'resource'      => ResourceEnum::USER,
                    'action_mask'   => $maskFactory->maskAction([
                        ActionEnum::READ   => 1,
                        ActionEnum::WRITE  => 1,
                        ActionEnum::DELETE => 1,
                    ], ResourceEnum::USER),
                    'property_mask' => $maskFactory->maskProperty([
                        PropertyEnum::OWNER => 1,
                        PropertyEnum::GROUP => 1,
                    ]),
                ],
                [
                    'resource'      => ResourceEnum::GROUP,
                    'action_mask'   => $maskFactory->maskAction([
                        ActionEnum::READ   => 1,
                        ActionEnum::WRITE  => 1,
                        ActionEnum::DELETE => 1,
                    ], ResourceEnum::GROUP),
                    'property_mask' => $maskFactory->maskProperty([
                        PropertyEnum::OWNER => 1,
                        PropertyEnum::GROUP => 1,
                    ]),
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

        /** @var GroupInterface $gp */
        $gp = $this->dm->find(Group::class, $rule->getGroup()->getId());
        self::$container->get('hbpf.access.manager')->removeGroup($gp);
        self::assertEmpty($this->dm->getRepository(Rule::class)->findAll());
    }

    /**
     * @covers AccessManager::removeGroup()
     *
     * @throws Exception
     */
    public function testRemoveGroupsMysql(): void
    {
        $group  = new ORMGroup(NULL);
        $group2 = new ORMGroup(NULL);
        $group->setName('gtest');
        $group2->setName('gtest2');
        $this->em->persist($group);
        $this->em->persist($group2);
        $group->addParent($group2);
        $this->em->flush();
        $this->em->clear();

        /** @var AccessManager $access */
        $access = self::$container->get('hbpf.access.manager');
        $this->setProperty($access, 'dm', $this->em);

        /** @var GroupInterface $gp */
        $gp = $this->em->find(ORMGroup::class, $group2->getId());
        $access->removeGroup($gp);

        /** @var GroupInterface $gp2 */
        $gp2 = $this->em->find(ORMGroup::class, $group->getId());
        self::assertEmpty($gp2->getParents());
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
     * @throws Exception
     */
    private function createUser(string $usr = 'test@test.com'): User
    {
        $user = new User();
        $user
            ->setEmail($usr)
            ->setPassword('pwd');
        $this->pfd($user);

        return $user;
    }

    /**
     * @param string $usr
     * @param int    $lvl
     *
     * @return User
     * @throws Exception
     */
    private function createUserLvl(string $usr, int $lvl): User
    {
        $user = new User();
        $user
            ->setEmail($usr)
            ->setPassword('pwd');
        $this->pfd($user);

        $group = new Group($user);
        $group->setLevel($lvl)->addUser($user);

        $this->pfd($group);

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
