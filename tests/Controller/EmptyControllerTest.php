<?php declare(strict_types=1);

namespace AclBundleTests\Controller;

use AclBundleTests\ControllerTestCaseAbstract;
use Hanaboso\PhpCheckUtils\PhpUnit\Traits\CustomAssertTrait;

/**
 * Class EmptyControllerTest
 *
 * @package AclBundleTests\Controller
 */
final class EmptyControllerTest extends ControllerTestCaseAbstract
{

    use CustomAssertTrait;

    /**
     *
     */
    public function testEmpty(): void
    {
        self::assertFake();
    }

}
