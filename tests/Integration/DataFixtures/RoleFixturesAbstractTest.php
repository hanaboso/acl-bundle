<?php declare(strict_types=1);

namespace AclBundleTests\Integration\DataFixtures;

use AclBundleTests\DatabaseTestCaseAbstract;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Hanaboso\AclBundle\DataFixtures\MongoDB\RoleFixtures;
use Hanaboso\AclBundle\Document\Group;
use Hanaboso\AclBundle\Document\Rule;
use Hanaboso\AclBundle\Factory\MaskFactory;
use Hanaboso\AclBundle\Repository\Document\RuleRepository;
use Hanaboso\PhpCheckUtils\PhpUnit\Traits\CustomAssertTrait;
use LogicException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RoleFixturesAbstractTest
 *
 * @package AclBundleTests\Integration\DataFixtures
 *
 * @covers  \Hanaboso\AclBundle\DataFixtures\RoleFixtureAbstract
 */
final class RoleFixturesAbstractTest extends DatabaseTestCaseAbstract
{

    use CustomAssertTrait;

    /**
     * @covers \Hanaboso\AclBundle\DataFixtures\RoleFixtureAbstract::load
     *
     * @throws Exception
     */
    public function testFixtures(): void
    {
        $f = new RoleFixtures();

        $g = new Group(NULL);
        $g->setName('test');
        $this->pfd($g);

        /** @var ObjectManager $b */
        $b = self::getContainer()->get('doctrine_mongodb.odm.default_document_manager');
        $f->setContainer(self::getContainer());
        $this->setProperty($f, 'encoderLevel', 3);

        $f->load($b);

        /** @var RuleRepository $repo */
        $repo = $b->getRepository(Rule::class);

        $all = $repo->findAll();
        self::assertNotEmpty($all);
    }

    /**
     * @throws Exception
     */
    public function testReturn(): void
    {
        $f = new RoleFixtures();
        /** @var ObjectManager $b */
        $b = self::getContainer()->get('doctrine_mongodb.odm.default_document_manager');

        $f->load($b);
        self::assertFake();
    }

    /**
     * @throws Exception
     */
    public function testActionOverkill(): void
    {
        $f = new RoleFixtures();
        $f->setContainer($this->mockContainer());

        /** @var ObjectManager $b */
        $b = self::getContainer()->get('doctrine_mongodb.odm.default_document_manager');

        self::expectException(LogicException::class);
        self::expectExceptionMessage('Amount of actions exceeded allowed 32!');
        $f->load($b);
    }

    /**
     * @return ContainerInterface
     */
    private function mockContainer(): ContainerInterface
    {
        $c = self::createMock(ContainerInterface::class);
        $c->method('getParameter')->willReturn(TestActionEnum::class);

        $f = self::createMock(MaskFactory::class);
        $c->method('get')->willReturn($f);

        return $c;
    }

}
