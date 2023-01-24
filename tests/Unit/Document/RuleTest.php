<?php declare(strict_types=1);

namespace AclBundleTests\Unit\Document;

use AclBundleTests\KernelTestCaseAbstract;
use Exception;
use Hanaboso\AclBundle\Document\Group;
use Hanaboso\AclBundle\Document\Rule;
use Hanaboso\AclBundle\Entity\RuleInterface;

/**
 * Class RuleTest
 *
 * @package AclBundleTests\Unit\Document
 *
 * @covers  \Hanaboso\AclBundle\Document\Rule
 * @covers  \Hanaboso\AclBundle\Document\DocumentAbstract
 */
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
        self::assertEquals('res', $doc->getResource());

        $g = new Group(NULL);
        $this->setProperty($g, 'id', '456');
        $doc->setGroup($g);
        self::assertEquals($g, $doc->getGroup());

        $doc->setActionMask(2);
        self::assertEquals(2, $doc->getActionMask());

        $doc->setPropertyMask(3);
        self::assertEquals(3, $doc->getPropertyMask());

        $arr = $doc->toArrayAcl();
        self::assertEquals(
            [
                'id'            => '123',
                'property_mask' => 3,
                'action_mask'   => 2,
                'resource'      => 'res',
            ],
            $arr,
        );

        $arr = [
            RuleInterface::ID            => '1',
            RuleInterface::PROPERTY_MASK => 1,
            RuleInterface::ACTION_MASK   => 5,
            RuleInterface::RESOURCE      => 'ress',
        ];

        $doc->fromArrayAcl($arr);
        self::assertEquals($arr, $doc->toArrayAcl());
    }

}
