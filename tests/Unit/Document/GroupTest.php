<?php declare(strict_types=1);

namespace AclBundleTests\Unit\Document;

use AclBundleTests\KernelTestCaseAbstract;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Hanaboso\AclBundle\Document\Group;
use Hanaboso\AclBundle\Document\Rule;
use Hanaboso\UserBundle\Document\User;

/**
 * Class GroupTest
 *
 * @package AclBundleTests\Unit\Document
 *
 * @covers  \Hanaboso\AclBundle\Document\Group
 * @covers  \Hanaboso\AclBundle\Document\DocumentAbstract
 */
final class GroupTest extends KernelTestCaseAbstract
{

    /**
     * @throws Exception
     */
    public function testGroup(): void
    {
        $g = new Group(NULL);
        $g->setName('nae');
        self::assertEquals('nae', $g->getName());

        $r = new Rule();
        $g->setRules([$r])
            ->addRule($r);
        self::assertEquals(new ArrayCollection([$r, $r]), $g->getRules());

        $u = new User();
        $g->setUsers([$u])
            ->addUser($u);
        self::assertEquals(new ArrayCollection([$u, $u]), $g->getUsers());

        self::assertEquals(Group::TYPE_ODM, $g->getType());

        $g->setLevel(11);
        self::assertEquals(11, $g->getLevel());

        $g->setTmpUsers([$u])
            ->addTmpUser($u);
        self::assertEquals(new ArrayCollection([$u, $u]), $g->getTmpUsers());

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
            Group::ID    => 'groupId',
            Group::NAME  => 'onamae',
            Group::LEVEL => 'll',
            'owner'      => 'ownerId',
            Group::RULES => [
                [
                    Rule::ID            => 'ruleId',
                    Rule::PROPERTY_MASK => 1,
                    Rule::ACTION_MASK   => 1,
                    Rule::RESOURCE      => 'r',
                ],
            ],
        ];

        $g->fromArrayAcl($arr, Rule::class, $links);

        self::assertEquals($arr, $g->toArrayAcl($links));
        self::assertEquals(['ruleId' => 'groupId'], $links);
    }

}
