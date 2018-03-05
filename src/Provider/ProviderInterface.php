<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\Provider;

use Hanaboso\UserBundle\Entity\UserInterface;

/**
 * Interface ProviderInterface
 *
 * @package Hanaboso\AclBundle\Provider
 */
interface ProviderInterface
{

    /**
     * @param UserInterface $user
     *
     * @return array
     */
    public function getRules(UserInterface $user): array;

}