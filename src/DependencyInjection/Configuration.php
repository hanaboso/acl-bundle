<?php declare(strict_types=1);

namespace Hanaboso\AclBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 *
 * @package Hanaboso\AclBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{

    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('hbpf');
        $rootNode    = $treeBuilder->getRootNode();

        $rootNode->children()
            ->arrayNode('acl');

        return $treeBuilder;
    }

}
