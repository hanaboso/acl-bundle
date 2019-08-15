<?php declare(strict_types=1);

namespace Tests\Unit\Enum;

use Exception;
use Hanaboso\AclBundle\Enum\ActionEnum;
use LogicException;
use Tests\KernelTestCaseAbstract;
use Tests\PrivateTrait;

/**
 * Class ActionEnumTest
 *
 * @package Tests\Unit\Enum
 */
final class ActionEnumTest extends KernelTestCaseAbstract
{

    use PrivateTrait;

    /**
     * @covers ActionEnum::getActionBit()
     *
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

}
