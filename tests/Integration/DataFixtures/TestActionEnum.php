<?php declare(strict_types=1);

namespace AclBundleTests\Integration\DataFixtures;

use Hanaboso\Utils\Enum\EnumAbstract;

/**
 * Class TestActionEnum
 *
 * @package AclBundleTests\Integration\DataFixtures
 */
final class TestActionEnum extends EnumAbstract
{

    /**
     * @var int[]
     */
    protected static array $choices = [
        1, 2, 3, 4, 5, 6, 7, 8, 9, 10,
        11, 12, 13, 14, 15, 16, 17, 18, 19, 20,
        21, 22, 23, 24, 25, 26, 27, 28, 29, 30,
        31, 32, 33,
    ];

}
