<?php declare(strict_types=1);

namespace AclBundleTests;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle;
use EmailServiceBundle\EmailServiceBundle;
use Exception;
use Hanaboso\AclBundle\HbPFAclBundle;
use Hanaboso\CommonsBundle\HbPFCommonsBundle;
use Hanaboso\UserBundle\HbPFUserBundle;
use RabbitMqBundle\RabbitMqBundle;
use Snc\RedisBundle\SncRedisBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 * Class Kernel
 *
 * @package AclBundleTests
 */
class Kernel extends BaseKernel
{

    use MicroKernelTrait;

    public const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    /**
     * @return string
     */
    public function getCacheDir(): string
    {
        return sprintf('%s/var/cache/%s', $this->getProjectDir(), $this->environment);
    }

    /**
     * @return BundleInterface[]
     */
    public function registerBundles(): iterable
    {
        $contents = [
            FrameworkBundle::class        => ['all' => TRUE],
            SecurityBundle::class         => ['all' => TRUE],
            DoctrineBundle::class         => ['all' => TRUE],
            MonologBundle::class          => ['all' => TRUE],
            DoctrineMongoDBBundle::class  => ['all' => TRUE],
            HbPFCommonsBundle::class      => ['all' => TRUE],
            RabbitMqBundle::class         => ['all' => TRUE],
            SwiftmailerBundle::class      => ['all' => TRUE],
            EmailServiceBundle::class     => ['all' => TRUE],
            HbPFUserBundle::class         => ['all' => TRUE],
            HbPFAclBundle::class          => ['all' => TRUE],
            DoctrineFixturesBundle::class => ['all' => TRUE],
            SncRedisBundle::class         => ['all' => TRUE],
        ];

        foreach ($contents as $class => $envs) {
            $envs;
            yield new $class();
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param LoaderInterface  $loader
     *
     * @throws Exception
     */
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->setParameter('container.dumper.inline_class_loader', TRUE);
        $confDir = sprintf('%s/tests/testApp/config', $this->getProjectDir());
        $loader->load(sprintf('%s/*%s', $confDir, self::CONFIG_EXTS), 'glob');
    }

    /**
     * @param RouteCollectionBuilder $routes
     */
    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $routes;
    }

}
