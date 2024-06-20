<?php declare(strict_types=1);

namespace AclBundleTests\Integration\Reposity\Entity;

use AclBundleTests\DatabaseTestCaseAbstract;
use Exception;
use Hanaboso\AclBundle\Entity\Group;
use Hanaboso\AclBundle\Repository\Entity\GroupRepository;
use Hanaboso\UserBundle\Entity\TmpUser;
use Hanaboso\UserBundle\Entity\User;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Class GroupRepositoryTest
 *
 * @package AclBundleTests\Integration\Reposity\Entity
 */
#[CoversClass(GroupRepository::class)]
final class GroupRepositoryTest extends DatabaseTestCaseAbstract
{

    /**
     * @throws Exception
     */
    public function testUserGroups(): void
    {
        self::markTestSkipped('Don\'t work with paralel run.');
        $user  = (new User())->setPassword('pwd')->setEmail('a@a.com');
        $user2 = (new User())->setPassword('pwd2')->setEmail('a2@a.com');
        $this->em->persist($user);
        $this->em->flush($user);
        $this->em->persist($user2);
        $this->em->flush($user2);

        $group3 = (new Group(NULL))->setName('qwe');
        $group  = (new Group($user))->addUser($user)->addUser($user2)->setName('asd')->addParent($group3);
        $group2 = (new Group($user2))->addUser($user2)->setName('asd');
        $this->em->persist($group);
        $this->em->persist($group2);
        $this->em->persist($group3);
        $this->em->flush();

        $this->em->clear();
        /** @var User $user */
        $user = $this->em->getRepository(User::class)->find($user->getId());
        /** @var GroupRepository $repo */
        $repo = $this->em->getRepository(Group::class);
        $res  = $repo->getUserGroups($user);
        self::assertEquals(2, count($res));

        /** @var GroupRepository $repo */
        $repo = $this->em->getRepository(Group::class);
        self::assertTrue($repo->exists('qwe'));
        self::assertFalse($repo->exists('eee'));
    }

    /**
     * @throws Exception
     */
    public function testTmpUserGroups(): void
    {
        self::markTestSkipped('Don\'t work with paralel run.');
        $user = (new TmpUser())->setPassword('pwd')->setEmail('a@a.com');
        $this->pfe($user);

        $usr = (new User())->setPassword('pwd')->setEmail('a@a.com');
        $this->pfe($usr);
        $group = (new Group($usr))->addTmpUser($user)->setName('asd');
        $this->pfe($group);

        $this->em->clear();

        /** @var GroupRepository $repo */
        $repo = $this->em->getRepository(Group::class);
        $res  = $repo->getTmpUserGroups($user);
        self::assertEquals(1, count($res));
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->em = self::getContainer()->get('doctrine.orm.default_entity_manager');
        $this->clearMysql();
    }

}
