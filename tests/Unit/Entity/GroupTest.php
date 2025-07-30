<?php declare(strict_types=1);

namespace AclBundleTests\Unit\Entity;

use AclBundleTests\KernelTestCaseAbstract;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Hanaboso\AclBundle\Entity\EntityAbstract;
use Hanaboso\AclBundle\Entity\Group;
use Hanaboso\AclBundle\Entity\Rule;
use Hanaboso\UserBundle\Entity\TmpUser;
use Hanaboso\UserBundle\Entity\User;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Class GroupTest
 *
 * @package AclBundleTests\Unit\Entity
 */
#[CoversClass(Group::class)]
#[CoversClass(EntityAbstract::class)]
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
        $this->setProperty($g, 'id', 1);
        self::assertSame('nae', $g->getName());

        $r = new Rule();
        $g
            ->setRules([$r])
            ->addRule($r);
        $this->setProperty($r, 'id', 1);
        self::assertEquals(new ArrayCollection([$r, $r]), $g->getRules());

        $u = new User();
        $g
            ->setUsers([$u])
            ->addUser($u);
        $this->setProperty($u, 'id', 1);
        self::assertEquals(new ArrayCollection([$u, $u]), $g->getUsers());

        self::assertSame(Group::TYPE_ORM, $g->getType());

        $g->setLevel(11);
        self::assertSame(11, $g->getLevel());

        $t = new TmpUser();
        $g->setTmpUsers([$t])
            ->addTmpUser($t);
        self::assertEquals(new ArrayCollection([$t, $t]), $g->getTmpUsers());

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
        $this->setProperty($u, 'id', 2);
        $g->setOwner($u);
        self::assertEquals($u, $g->getOwner());

        $g->addChild($par);
        self::assertEquals(new ArrayCollection([$par]), $g->getChildren());

        $g->setRules([]);
        $links = [];
        $arr   = [
            'owner'      => 2,
            Group::ID    => 33,
            Group::LEVEL => 2,
            Group::NAME  => 'onamae',
            Group::RULES => [
                [
                    Rule::ACTION_MASK   => 1,
                    Rule::ID            => 22,
                    Rule::PROPERTY_MASK => 1,
                    Rule::RESOURCE      => 'r',
                ],
            ],
        ];

        $g->fromArrayAcl($arr, Rule::class, $links);

        self::assertEquals($arr, $g->toArrayAcl($links));
        self::assertEquals(['22' => '33'], $links);

        $g = new Group(new User());
        self::assertNotNull($g->getOwner());
    }

}
