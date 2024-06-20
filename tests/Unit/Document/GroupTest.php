<?php declare(strict_types=1);

namespace AclBundleTests\Unit\Document;

use AclBundleTests\KernelTestCaseAbstract;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Hanaboso\AclBundle\Document\DocumentAbstract;
use Hanaboso\AclBundle\Document\Group;
use Hanaboso\AclBundle\Document\Rule;
use Hanaboso\AclBundle\Entity\GroupInterface;
use Hanaboso\AclBundle\Entity\RuleInterface;
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
        self::assertEquals('nae', $g->getName());

        $r = new Rule();
        $g->setRules([$r])
            ->addRule($r);
        self::assertEquals(new ArrayCollection([$r, $r]), $g->getRules());

        $u = new User();
        $g->setUsers([$u])
            ->addUser($u);
        self::assertEquals(new ArrayCollection([$u, $u]), $g->getUsers());

        self::assertEquals(GroupInterface::TYPE_ODM, $g->getType());

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
            'owner'               => 'ownerId',
            GroupInterface::ID    => 'groupId',
            GroupInterface::LEVEL => 11,
            GroupInterface::NAME  => 'onamae',
            GroupInterface::RULES => [
                [
                    RuleInterface::ACTION_MASK   => 1,
                    RuleInterface::ID            => 'ruleId',
                    RuleInterface::PROPERTY_MASK => 1,
                    RuleInterface::RESOURCE      => 'r',
                ],
            ],
        ];

        $g->fromArrayAcl($arr, Rule::class, $links);

        self::assertEquals($arr, $g->toArrayAcl($links));
        self::assertEquals(['ruleId' => 'groupId'], $links);
    }

}
