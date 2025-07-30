<?php declare(strict_types=1);

namespace AclBundleTests\Unit\Document;

use AclBundleTests\KernelTestCaseAbstract;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Hanaboso\AclBundle\Document\DocumentAbstract;
use Hanaboso\AclBundle\Document\Group;
use Hanaboso\AclBundle\Document\Rule;
use Hanaboso\AclBundle\Entity\Group as EntityGroup;
use Hanaboso\AclBundle\Entity\Rule as EntityRule;
use Hanaboso\UserBundle\Document\TmpUser;
use Hanaboso\UserBundle\Document\User;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Class GroupTest
 *
 * @package AclBundleTests\Unit\Document
 */
#[CoversClass(Group::class)]
#[CoversClass(DocumentAbstract::class)]
final class GroupTest extends KernelTestCaseAbstract
{

    /**
     * @throws Exception
     */
    public function testGroup(): void
    {
        $g = new Group(NULL);
        $g->setName('nae');
        self::assertSame('nae', $g->getName());

        $r = new Rule();
        $g->setRules([$r])
            ->addRule($r);
        self::assertEquals(new ArrayCollection([$r, $r]), $g->getRules());

        $u = new User();
        $g->setUsers([$u])
            ->addUser($u);
        self::assertEquals(new ArrayCollection([$u, $u]), $g->getUsers());

        self::assertSame(EntityGroup::TYPE_ODM, $g->getType());

        $g->setLevel(11);
        self::assertSame(11, $g->getLevel());

        $t = new TmpUser();
        $g->setTmpUsers([$t])
            ->addTmpUser($t);
        self::assertEquals(new ArrayCollection([$t, $t]), $g->getTmpUsers());

        $par = new Group(NULL);
        $g->addParent($par);
        self::assertEquals(new ArrayCollection([$par]), $g->getParents());
        $g->addParent($par);
        self::assertEquals(new ArrayCollection([$par]), $g->getParents());
        self::assertEquals(new ArrayCollection([$g]), $par->getChildren());
        $g->removeParent($par);
        self::assertEquals(new ArrayCollection([]), $g->getParents());

        $this->setProperty($u, 'id', 'ownerId');
        $g->setOwner($u);
        self::assertEquals($u, $g->getOwner());

        $g->setRules([]);
        $links = [];
        $arr   = [
            'owner'               => 'ownerId',
            EntityGroup::ID    => 'groupId',
            EntityGroup::LEVEL => 11,
            EntityGroup::NAME  => 'onamae',
            EntityGroup::RULES => [
                [
                    EntityRule::ACTION_MASK   => 1,
                    EntityRule::ID            => 'ruleId',
                    EntityRule::PROPERTY_MASK => 1,
                    EntityRule::RESOURCE      => 'r',
                ],
            ],
        ];

        $g->fromArrayAcl($arr, Rule::class, $links);

        self::assertEquals($arr, $g->toArrayAcl($links));
        self::assertEquals(['ruleId' => 'groupId'], $links);
    }

}
