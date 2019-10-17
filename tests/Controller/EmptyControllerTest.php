<?php declare(strict_types=1);

namespace AclBundleTests\Controller;

use AclBundleTests\ControllerTestCaseAbstract;

/**
 * Class EmptyControllerTest
 *
 * @package AclBundleTests\Controller
 */
final class EmptyControllerTest extends ControllerTestCaseAbstract
{

    /**
     *
     */
    public function testEmpty(): void
    {
        self::assertCount(0, []);
    }

}
