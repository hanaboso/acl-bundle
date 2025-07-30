<?php declare(strict_types=1);

namespace AclBundleTests\Unit\Document;

use AclBundleTests\KernelTestCaseAbstract;
use Exception;
use Hanaboso\AclBundle\Document\DocumentAbstract;
use Hanaboso\AclBundle\Document\Group;
use Hanaboso\AclBundle\Document\Rule;
use Hanaboso\AclBundle\Entity\Rule as EntityRule;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Class RuleTest
 *
 * @package AclBundleTests\Unit\Document
 */
#[CoversClass(Rule::class)]
#[CoversClass(DocumentAbstract::class)]
final class RuleTest extends KernelTestCaseAbstract
{

    /**
     * @throws Exception
     */
    public function testDocument(): void
    {
        $doc = new Rule();
        $doc->setResource('res');
        $this->setProperty($doc, 'id', '123');
        self::assertSame('res', $doc->getResource());

        $g = new Group(NULL);
        $this->setProperty($g, 'id', '456');
        $doc->setGroup($g);
        self::assertEquals($g, $doc->getGroup());

        $doc->setActionMask(2);
        self::assertSame(2, $doc->getActionMask());

        $doc->setPropertyMask(3);
        self::assertSame(3, $doc->getPropertyMask());

        $arr = $doc->toArrayAcl();
        self::assertEquals(
            [
                'action_mask'   => 2,
                'id'            => '123',
                'property_mask' => 3,
                'resource'      => 'res',
            ],
            $arr,
        );

        $arr = [
            EntityRule::ACTION_MASK   => 5,
            EntityRule::ID            => '1',
            EntityRule::PROPERTY_MASK => 1,
            EntityRule::RESOURCE      => 'ress',
        ];

        $doc->fromArrayAcl($arr);
        self::assertEquals($arr, $doc->toArrayAcl());
    }

}
