<?php declare(strict_types=1);

namespace AclBundleTests\Unit\Enum;

use AclBundleTests\KernelTestCaseAbstract;
use Exception;
use Hanaboso\AclBundle\Enum\ActionEnum;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Class ActionEnumTest
 *
 * @package AclBundleTests\Unit\Enum
 */
#[CoversClass(ActionEnum::class)]
final class ActionEnumTest extends KernelTestCaseAbstract
{

    /**
     * @throws Exception
     */
    public function testActionEnum(): void
    {
        self::assertEquals([ActionEnum::WRITE => ActionEnum::WRITE], ActionEnum::getGlobalActions());

        self::assertSame(0, ActionEnum::getActionBit(ActionEnum::READ));
        self::assertSame(1, ActionEnum::getActionBit(ActionEnum::WRITE));
    }

    /**
     * @throws Exception
     */
    public function testActionLimit(): void
    {
        $arr = [];
        for ($i = 0; $i < 33; $i++) {
            $str   = (string) $i;
            $arr[] = $str;
        }

        $class = new ActionEnum();
        $this->setProperty($class, 'choices', $arr);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Amount of actions exceeded 32.');
        $class::getActionBit('32');
    }

    /**
     * @throws Exception
     */
    public function testUselessFakeException(): void
    {
        self::expectException(LogicException::class);
        self::expectExceptionMessage('Missing action.');
        TestActionEnum::getActionBit('asdasd');
    }

}
