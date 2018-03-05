<?php declare(strict_types=1);

namespace Tests\Unit\Factory;

use Hanaboso\AclBundle\Factory\MaskFactory;
use Tests\KernelTestCaseAbstract;

/**
 * Class MaskFactoryTest
 *
 * @package Tests\Unit\Factory
 */
class MaskFactoryTest extends KernelTestCaseAbstract
{

    /**
     * @covers MaskFactory::maskAction()
     */
    public function testMaskAction(): void
    {
        $data = [
            'read'   => FALSE,
            'write'  => 1,
            'delete' => 'true',
        ];

        self::assertEquals(6, MaskFactory::maskAction($data));
    }

    /**
     * @covers MaskFactory::maskProperty()
     */
    public function testMaskProperty(): void
    {
        $data = [
            'owner' => '1',
            'group' => 1,
        ];

        self::assertEquals(2, MaskFactory::maskProperty($data));
    }

}