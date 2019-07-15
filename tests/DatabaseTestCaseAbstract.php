<?php declare(strict_types=1);

namespace Tests;

use Doctrine\DBAL\DBALException;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class DatabaseTestCaseAbstract
 *
 * @package Tests
 */
abstract class DatabaseTestCaseAbstract extends KernelTestCaseAbstract
{

    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Session
     */
    protected $session;

    /**
     * DatabaseTestCaseAbstract constructor.
     *
     * @param null   $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = NULL, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        self::bootKernel();
        $this->dm      = self::$container->get('doctrine_mongodb.odm.default_document_manager');
        $this->em      = self::$container->get('doctrine.orm.default_entity_manager');
        $this->session = new Session();
    }

    /**
     *
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->dm->getConnection()->dropDatabase('pipes');
        $this->session->invalidate();
        $this->session->clear();
    }

    /**
     * @param mixed $document
     */
    protected function persistAndFlush($document): void
    {
        $this->dm->persist($document);
        $this->dm->flush($document);
    }

    /**
     * @throws DBALException
     */
    protected function clearMysql(): void
    {
        $query      = '';
        $connection = $this->em->getConnection();
        foreach ($connection->getSchemaManager()->listTableNames() as $table) {
            $query .= sprintf('TRUNCATE %s;', $table);
        }

        $connection->query(sprintf('SET FOREIGN_KEY_CHECKS=0;%sSET FOREIGN_KEY_CHECKS=1;', $query));
    }

}
