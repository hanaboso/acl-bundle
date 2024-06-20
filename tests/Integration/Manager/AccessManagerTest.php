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
use Hanaboso\CommonsBundle\FileStorage\Document\File;
use Hanaboso\UserBundle\Document\User;
use Hanaboso\Utils\Exception\EnumException;
use Hanaboso\Utils\String\DsnParser;
use PHPUnit\Framework\Attributes\CoversClass;
use Predis\Client;
use Throwable;

/**
 * Class AccessManagerTest
 *
 * @package AclBundleTests\Integration\Manager
 */
#[CoversClass(AccessManager::class)]
final class AccessManagerTest extends DatabaseTestCaseAbstract
{

    /**
     * @throws Exception
     */
    public function testWrongObjectArray(): void
    {
        $user = $this->createUser('wrongOne');
        $this->createRule($user);
        $this->expect();
        self::getContainer()->get('hbpf.access.manager')->isAllowed('write', 'group', $user, []);
    }

    /**
     * @throws Exception
     */
    public function testWrongObjectArray2(): void
    {
        $user = $this->createUser('wrongOne');
        $this->createRule($user, 7, 'group', 2, TRUE);
        $this->expect();
        self::getContainer()->get('hbpf.access.manager')->isAllowed('write', 'group', $user, []);
    }

    /**
     * @throws Exception
     */
    public function testWrongObjectBool(): void
    {
        $user = $this->createUser('wrongOne');
        $this->createRule($user);
        $this->expect();
        self::getContainer()->get('hbpf.access.manager')->isAllowed('read', 'group', $user, TRUE);
    }

    /**
     * @throws Exception
     */
    public function testWrongResourceAction(): void
    {
        $user = $this->createUser('resource');
        $this->createRule($user);
        $this->expect(EnumException::INVALID_CHOICE, EnumException::class);
        self::getContainer()->get('hbpf.access.manager')->isAllowed('writdde', 'group', $user);
    }

    /**
     * @throws Exception
     */
    public function testWrongResourceResource(): void
    {
        $user = $this->createUser('resource');
        $this->createRule($user);
        $this->expect(EnumException::INVALID_CHOICE, EnumException::class);
        self::getContainer()->get('hbpf.access.manager')->isAllowed('write', 'grosdup', $user);
    }

    /**
     * @throws Exception
     */
    public function testReadPermission(): void
    {
        $user = $this->createUser('readPer');
        $this->createRule($user, 3);
        self::assertTrue(self::getContainer()->get('hbpf.access.manager')->isAllowed('read', 'group', $user));
    }

    /**
     * @throws Exception
     */
    public function testReadPermissionNotAllowed(): void
    {
        $user = $this->createUser('nullReadPer');
        $this->createRule($user, 3, 'group', 1);
        $this->expect();
        self::getContainer()->get('hbpf.access.manager')->isAllowed('read', 'group', $user);
    }

    /**
     * @throws Exception
     */
    public function testWritePermission(): void
    {
        $user = $this->createUser('writePer');
        $this->createRule($user, 2, 'group', 1);
        self::assertTrue(self::getContainer()->get('hbpf.access.manager')->isAllowed('write', 'group', $user));
    }

    /**
     * @throws Exception
     */
    public function testWritePermissionNotAllowed(): void
    {
        $user = $this->createUser('nullWritePer');
        $this->createRule($user, 1);
        $this->expect();
        self::getContainer()->get('hbpf.access.manager')->isAllowed('write', 'group', $user);
    }

    /**
     * @throws Exception
     */
    public function testDeletePermission(): void
    {
        $user = $this->createUser('deletePer');
        $this->createRule($user, 6);
        self::assertTrue(self::getContainer()->get('hbpf.access.manager')->isAllowed('delete', 'group', $user));
    }

    /**
     * @throws Exception
     */
    public function testDeletePermissionNotAllowed(): void
    {
        $user = $this->createUser('nullDeletePer');
        $this->createRule($user, 6, 'group', 1);
        $this->expect();
        self::getContainer()->get('hbpf.access.manager')->isAllowed('delete', 'group', $user);
    }

    /**
     * @throws Exception
     */
    public function testMissingResourceAction(): void
    {
        $user = $this->createUser('nullReadPer');
        $this->createRule($user, 3);
        $this->expect(AclException::INVALID_ACTION);
        self::getContainer()->get('hbpf.access.manager')->isAllowed('test2', 'group', $user);
    }

    /**
     * @throws Exception
     */
    public function testExtraDefaultResourceAction(): void
    {
        $user = $this->createUser('nullReadPer');
        $this->createRule($user, 11);
        $r = self::getContainer()->get('hbpf.access.manager')->isAllowed('test', 'group', $user);
        self::assertNotEmpty($r);
    }

    /**
     * @throws Exception
     */
    public function testExtraResourceAction(): void
    {
        $user = $this->createUser('nullReadPer');
        $this->createRule($user, 19, 'token');
        $r = self::getContainer()->get('hbpf.access.manager')->isAllowed('test2', 'token', $user);
        self::assertNotEmpty($r);
    }

    /**
     * @throws Exception
     */
    public function testGlobalActionExtended(): void
    {
        $user = $this->createUser('nullReadPer');
        $this->createRule($user, 11, 'token', 1);
        $r = self::getContainer()->get('hbpf.access.manager')->isAllowed('test', 'token', $user);
        self::assertNotEmpty($r);
    }

    /**
     * @throws Exception
     */
    public function testGlobalActionRestriction(): void
    {
        $user = $this->createUser('nullReadPer');
        $this->createRule($user, 19, 'token', 1);
        $this->expect();
        self::getContainer()->get('hbpf.access.manager')->isAllowed('test2', 'token', $user);
    }

    /**
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
        self::getContainer()->get('hbpf.access.manager')->isAllowed('read', 'group', $user, $group);
    }

    /**
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
        self::getContainer()->get('hbpf.access.manager')->isAllowed('read', 'group', $user, $group->getId());
    }

    /**
     * @throws Exception
     */
    public function testObjOwnerRight(): void
    {
        $user  = $this->createUser('objOwner');
        $group = new Group($user);
        $this->pfd($group);
        $this->createRule($user, 7, 'group', 1);
        $res = self::getContainer()->get('hbpf.access.manager')->isAllowed('read', 'group', $user, $group);
        self::assertInstanceOf(Group::class, $res);
        $res = self::getContainer()->get('hbpf.access.manager')->isAllowed('read', 'group', $user, $group->getId());
        self::assertInstanceOf(Group::class, $res);
    }

    /**
     * @throws Exception
     */
    public function testObjWithoutOwnerAllowed(): void
    {
        $user = $this->createUser('noOwnerAllow');
        $this->createRule($user, 7, 'file', 1);
        $file = new File();
        $this->pfd($file);
        $res = self::getContainer()->get('hbpf.access.manager')->isAllowed('read', 'file', $user, $file);
        self::assertInstanceOf(File::class, $res);
        $res = self::getContainer()->get('hbpf.access.manager')->isAllowed('read', 'file', $user, $file->getId());
        self::assertInstanceOf(File::class, $res);
    }

    /**
     * @throws Exception
     */
    public function testObjWithoutOwnerNotAllowed(): void
    {
        $user = $this->createUser('noOwnerAllow');
        $this->createRule($user, 2, 'file');
        $file = new File();
        $this->pfd($file);
        $this->expect();
        self::getContainer()->get('hbpf.access.manager')->isAllowed('read', 'file', $user, $file->getId());
    }

    /**
     * @throws Exception
     */
    public function testGroupAllowed(): void
    {
        $user = $this->createUser('groupAllowed');
        $this->createRule($user, 1);
        $group = new Group(NULL);
        $this->pfd($group);
        $res = self::getContainer()->get('hbpf.access.manager')->isAllowed('read', 'group', $user, $group);
        self::assertInstanceOf(Group::class, $res);
        $res = self::getContainer()->get('hbpf.access.manager')->isAllowed('read', 'group', $user, $group->getId());
        self::assertInstanceOf(Group::class, $res);
    }

    /**
     * @throws Exception
     */
    public function testGroupNotAllowed(): void
    {
        $user = $this->createUser('groupAllowed');
        $this->createRule($user, 1);
        $group = new Group($user);
        $this->pfd($group);
        $this->expect();
        self::getContainer()->get('hbpf.access.manager')->isAllowed('write', 'group', $user, $group);
    }

    /**
     * @throws Exception
     */
    public function testGroupLvlAllowed(): void
    {
        $user = $this->createUser('groupAllowed');
        $this->createRule($user);
        $group = new Group($user);
        $this->pfd($group->setLevel(8));
        $res = self::getContainer()->get('hbpf.access.manager')->isAllowed('write', 'group', $user, $group);
        self::assertInstanceOf(Group::class, $res);
    }

    /**
     * @throws Exception
     */
    public function testGroupLvlNotAllowed(): void
    {
        $user = $this->createUser('groupNotAllowed');
        $this->createRule($user);
        $group = new Group($user);
        $this->pfd($group->setLevel(0));
        $this->expect();
        self::getContainer()->get('hbpf.access.manager')->isAllowed('write', 'group', $user, $group);
    }

    /**
     * @throws Exception
     */
    public function testUserLvlAllowed(): void
    {
        $user = $this->createUser('userAllo');
        $this->createRule($user, 7, 'user');
        $tser = $this->createUserLvl('tserAllo', 55);
        $res  = self::getContainer()->get('hbpf.access.manager')->isAllowed('write', 'user', $user, $tser);
        self::assertInstanceOf(User::class, $res);
    }

    /**
     * @throws Exception
     */
    public function testAllowedGroupLvlUnderOwnerLvl(): void
    {
        $user = $this->createUser('usr01');
        $rule = $this->createRule($user, 7, ResourceEnum::USER, 1);

        $group = new Group(NULL);
        $group
            ->setName('group')
            ->setLevel(55)
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

        $res = self::getContainer()->get('hbpf.access.manager')->isAllowed(ActionEnum::READ, 'user', $user);
        self::assertTrue($res);
    }

    /**
     * @throws Exception
     */
    public function testUserLvlNotAllowed(): void
    {
        $user = $this->createUser('userNotAllo');
        $this->createRule($user, 7, 'user');
        $tser = $this->createUserLvl('tserNotAllo', 0);
        $this->expect();
        self::getContainer()->get('hbpf.access.manager')->isAllowed('write', 'user', $user, $tser);
    }

    /**
     * @throws Exception
     */
    public function testClassPermission(): void
    {
        $user = $this->createUser('class');
        $this->createRule($user);
        $this->expect();
        self::getContainer()->get('hbpf.access.manager')->isAllowed('write', 'group', $user, Group::class);
    }

    /**
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

        $access = self::getContainer()->get('hbpf.access.manager');
        $access->addGroup('newGroup');
        /** @var Group $group */
        $group = $this->dm->getRepository(Group::class)->findOneBy(['name' => 'newGroup']);

        /** @var MaskFactory $maskFactory */
        $maskFactory = self::getContainer()->get('hbpf.factory.mask');

        $data = new GroupDto($group);
        $data
            ->addUser($user)
            ->addRule(
                Rule::class,
                [
                    [
                        'action_mask'   => $maskFactory->maskAction(
                            [
                                ActionEnum::DELETE => 1,
                                ActionEnum::READ   => 1,
                                ActionEnum::WRITE  => 1,
                            ],
                            ResourceEnum::USER,
                        ),
                        'property_mask' => MaskFactory::maskProperty(
                            [
                                PropertyEnum::GROUP => 1,
                                PropertyEnum::OWNER => 1,
                            ],
                        ),
                        'resource'      => ResourceEnum::USER,
                    ],
                    [
                        'action_mask'   => $maskFactory->maskAction(
                            [
                                ActionEnum::DELETE => 1,
                                ActionEnum::READ   => 1,
                                ActionEnum::WRITE  => 1,
                            ],
                            ResourceEnum::GROUP,
                        ),
                        'property_mask' => MaskFactory::maskProperty(
                            [
                                PropertyEnum::GROUP => 1,
                                PropertyEnum::OWNER => 1,
                            ],
                        ),
                        'resource'      => ResourceEnum::GROUP,
                    ],
                ],
            );
        $group = $access->updateGroup($data);

        self::assertInstanceOf(Group::class, $group);
        self::assertEquals(2, count($group->getRules()));
    }

    /**
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
        self::getContainer()->get('hbpf.access.manager')->removeGroup($gp);
        self::assertEmpty($this->dm->getRepository(Rule::class)->findAll());
    }

    /**
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
        $access = self::getContainer()->get('hbpf.access.manager');
        $this->setProperty($access, 'dm', $this->em);

        /** @var GroupInterface $gp */
        $gp = $this->em->find(ORMGroup::class, $group2->getId());
        $access->removeGroup($gp);

        /** @var GroupInterface $gp2 */
        $gp2 = $this->em->find(ORMGroup::class, $group->getId());
        self::assertEmpty($gp2->getParents());
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->em = self::getContainer()->get('doctrine.orm.default_entity_manager');
        $this->clearMysql();

        $config = DsnParser::parseRedisDsn((string) getenv('REDIS_DSN'));
        $redis  = new Client(
            [
                'host' => $config[DsnParser::HOST],
                'port' => $config[DsnParser::PORT] ?? 6_379,
            ],
        );

        $redis->connect();
        $redis->flushall();
    }

    /**
     * @param User|null $user
     * @param int       $act
     * @param string    $res
     * @param int       $prop
     * @param bool      $extra
     *
     * @return Rule
     * @throws Exception
     */
    private function createRule(
        ?User $user = NULL,
        int $act = 7,
        string $res = 'group',
        int $prop = 2,
        bool $extra = FALSE,
    ): Rule
    {
        $group = new Group($user);
        $rule2 = new Rule();

        if ($extra) {
            $rule = new Rule();
            $rule
                ->setGroup($group->setLevel(1))
                ->setResource($res)
                ->setActionMask($act)
                ->setPropertyMask(2);
            $this->dm->persist($rule);
            $group->addRule($rule);
        }

        $rule2
            ->setGroup($group->setLevel(5))
            ->setResource($res)
            ->setActionMask($act)
            ->setPropertyMask($prop);

        $group
            ->addRule($rule2)
            ->setName('group');
        if ($user) {
            $group->addUser($user);
        }

        $this->dm->persist($rule2);
        $this->dm->persist($group);
        $this->dm->flush();

        return $rule2;
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
     * @phpstan-param class-string<Throwable> $class
     *
     * @param int    $code
     * @param string $class
     */
    private function expect(int $code = AclException::PERMISSION, string $class = AclException::class): void
    {
        self::expectException($class);
        self::expectExceptionCode($code);
    }

}
