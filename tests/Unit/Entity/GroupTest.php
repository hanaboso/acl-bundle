<?php declare(strict_types=1);

namespace AclBundleTests\Unit\Entity;

use AclBundleTests\KernelTestCaseAbstract;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Hanaboso\AclBundle\Entity\Group;
use Hanaboso\AclBundle\Entity\GroupInterface;
use Hanaboso\AclBundle\Entity\Rule;
use Hanaboso\AclBundle\Entity\RuleInterface;
use Hanaboso\UserBundle\Entity\User;

/**
 * Class GroupTest
 *
 * @package AclBundleTests\Unit\Entity
 *
 * @covers  \Hanaboso\AclBundle\Entity\Group
 * @covers  \Hanaboso\AclBundle\Entity\EntityAbstract
 */
final class GroupTest extends KernelTestCaseAbstract
{

    /**
     * @throws Exception
     */
    public function testGroup(): void
    {
        $g = new Group(NULL);
        $g
            ->setName('nae')
            ->setLevel(1);
        $this->setProperty($g, 'id', '1');
        self::assertEquals('nae', $g->getName());

        $r = new Rule();
        $g
            ->setRules([$r])
            ->addRule($r);
        $this->setProperty($r, 'id', '1');
        self::assertEquals(new ArrayCollection([$r, $r]), $g->getRules());

        $u = new User();
        $g
            ->setUsers([$u])
            ->addUser($u);
        $this->setProperty($u, 'id', '1');
        self::assertEquals(new ArrayCollection([$u, $u]), $g->getUsers());

        self::assertEquals(GroupInterface::TYPE_ORM, $g->getType());

        $g->setLevel(11);
        self::assertEquals(11, $g->getLevel());

        $g->setTmpUsers([$u])
            ->addTmpUser($u);
        self::assertEquals(new ArrayCollection([$u, $u]), $g->getTmpUsers());

        $par = new Group(NULL);
        $par->setLevel(2);
        $g->addParent($par);
        self::assertEquals(new ArrayCollection([$par]), $g->getParents());
        $g->addParent($par);
        self::assertEquals(new ArrayCollection([$par]), $g->getParents());
        $g->removeParent($par);
        self::assertEquals(new ArrayCollection([]), $g->getParents());

        $g->setOwner(NULL);
        self::assertNull($g->getOwner());
        $this->setProperty($u, 'id', 'ownerId');
        $g->setOwner($u);
        self::assertEquals($u, $g->getOwner());

        $g->addChild($par);
        self::assertEquals(new ArrayCollection([$par]), $g->getChildren());

        $g->setRules([]);
        $links = [];
        $arr   = [
            GroupInterface::ID    => 'groupId',
            GroupInterface::NAME  => 'onamae',
            GroupInterface::LEVEL => 2,
            'owner'               => 'ownerId',
            GroupInterface::RULES => [
                [
                    RuleInterface::ID            => 'ruleId',
                    RuleInterface::PROPERTY_MASK => 1,
                    RuleInterface::ACTION_MASK   => 1,
                    RuleInterface::RESOURCE      => 'r',
                ],
            ],
        ];

        $g->fromArrayAcl($arr, Rule::class, $links);

        self::assertEquals($arr, $g->toArrayAcl($links));
        self::assertEquals(['ruleId' => 'groupId'], $links);

        $g = new Group(new User());
        self::assertNotNull($g->getOwner());
    }

}
