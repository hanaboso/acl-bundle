<?php declare(strict_types=1);

namespace AclBundleTests;

use Hanaboso\PhpCheckUtils\PhpUnit\Traits\PrivateTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class KernelTestCaseAbstract
 *
 * @package AclBundleTests
 */
abstract class KernelTestCaseAbstract extends KernelTestCase
{

    use PrivateTrait;

    /**
     *
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
    }

}
