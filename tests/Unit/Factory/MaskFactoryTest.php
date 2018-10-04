<?php declare(strict_types=1);

namespace Tests\Unit\Factory;

use Exception;
use Hanaboso\AclBundle\Enum\ResourceEnum;
use Hanaboso\AclBundle\Factory\MaskFactory;
use Tests\KernelTestCaseAbstract;
use Tests\testApp\ExtActionEnum;

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
        $factory = $this->c->get('hbpf.factory.mask');
        $data    = [
            'read'   => FALSE,
            'write'  => 1,
            'delete' => 'true',
        ];

        self::assertEquals(6, $factory->maskAction($data, ResourceEnum::TOKEN));
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

    /**
     * @covers MaskFactory::getAllowedList()
     *
     * @throws Exception
     */
    public function testAllowedList(): void
    {
        $factory  = $this->c->get('hbpf.factory.mask');
        $baseList = [
            ExtActionEnum::READ,
            ExtActionEnum::WRITE,
            ExtActionEnum::DELETE,
            ExtActionEnum::TEST,
        ];

        self::assertEquals([
            MaskFactory::DEFAULT_ACTIONS => $baseList,
            MaskFactory::RESOURCE_LIST   => [
                ResourceEnum::TOKEN => [
                    ExtActionEnum::TEST2,
                ],
            ],
        ], $factory->getAllowedList(FALSE));

        self::assertEquals([
            ResourceEnum::TOKEN    => [
                ExtActionEnum::READ,
                ExtActionEnum::WRITE,
                ExtActionEnum::DELETE,
                ExtActionEnum::TEST,
                ExtActionEnum::TEST2,
            ],
            ResourceEnum::GROUP    => $baseList,
            ResourceEnum::USER     => $baseList,
            ResourceEnum::FILE     => $baseList,
            ResourceEnum::RULE     => $baseList,
            ResourceEnum::TMP_USER => $baseList,
        ], $factory->getAllowedList());
    }

    /**
     * @covers MaskFactory::isActionAllowed()
     *
     * @throws Exception
     */
    public function testAllowedActions(): void
    {
        /** @var MaskFactory $factory */
        $factory = $this->c->get('hbpf.factory.mask');
        self::assertTrue($factory->isActionAllowed(ExtActionEnum::TEST, ResourceEnum::FILE));
        self::assertTrue($factory->isActionAllowed(ExtActionEnum::READ, ResourceEnum::TOKEN));
        self::assertTrue($factory->isActionAllowed(ExtActionEnum::TEST2, ResourceEnum::TOKEN));
        self::assertFalse($factory->isActionAllowed(ExtActionEnum::TEST2, ResourceEnum::USER));
    }

}