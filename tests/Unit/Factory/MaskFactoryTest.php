<?php declare(strict_types=1);

namespace Tests\Unit\Factory;

use Exception;
use Hanaboso\AclBundle\Factory\MaskFactory;
use Tests\KernelTestCaseAbstract;

/**
 * Class MaskFactoryTest
 *
 * @package Tests\Unit\Factory
 */
final class MaskFactoryTest extends KernelTestCaseAbstract
{

    /**
     * @covers MaskFactory::maskAction()
     * @throws Exception
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
     * @throws Exception
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