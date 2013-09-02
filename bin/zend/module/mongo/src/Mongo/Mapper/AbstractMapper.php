<?php
namespace Mongo\Mapper;

use MongoClient;
use MongoId;
use MongoCursor;
use MongoCollection;
use Exception;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Hydrator\ClassMethods;
use Zend\Di\ServiceLocator;
use Zend\Stdlib\Hydrator\HydratorInterface;

use ZfcBase\EventManager\EventProvider;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;


/**
 * Class DbAbstract
 *
 * @package Zf2Mongo\Mapper
 */
abstract class AbstractMapper extends EventProvider implements ServiceLocatorAwareInterface
{
    /**
     * @var MongoClient
     */
    protected $dbAdapter;

    /**
     * @var string
     */
    protected $collection;

    /**
     * @var string
     */
    protected $database;

    /**
     * @var MongoCollection
     */
    protected $collectionPrototype;

    /**
     * @var ClassMethods
     */
    protected $hydrator;

    /**
     * @var MongoCursor
     */
    protected $lastCursor;

    /**
     * @var object
     */
    protected $entityPrototype;

    /**
     * @var bool
     */
    protected $isInitialised = false;

    /**
     * @var ServiceLocatorInterface
     */
    protected $services;

    /**
     * Initialise Class
     * @return bool
     * @throws \Exception
     */
    public function initialise() {
        if ($this->isInitialised) {
            return true;
        }

        if (!$this->hydrator instanceof ClassMethods) {
            $this->getHydrator();
        }

        if (!is_object($this->entityPrototype)) {
            throw new \Exception('No entity prototype set');
        }

        if (!is_object($this->services)) {
            throw new \Exception('No service locator set');
        }

        $this->isInitialised = true;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->services = $serviceLocator;
    }

    /**
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->services;
    }

    /**
     * Return a Hydrating Resultset object
     * @see http://www.php.net/manual/en/class.mongocollection.php
     *
     * @param array $query
     * @param null $entityPrototype
     * @param null $hydrator
     * @param bool $findAll
     * @return bool|HydratingResultSet
     */
    public function find($query = array(), $fields = array(), $entityPrototype = null, $hydrator = null, $findAll = true)
    {
        $this->initialise();

        $collection = $this->getCollectionPrototype();

        if ($findAll) {
            $cursor = $collection->find($query, $fields);
        } else {
            $cursor = $collection->findOne($query, $fields);
            $cursor = array($cursor);
        }

        // Push to hydrator
        $resultSet = new HydratingResultSet($hydrator ?: $this->getHydrator(),
            $entityPrototype ?: $this->getEntityPrototype());
        $resultSet->initialize($cursor);

        // Save the cursor to the object for raw output
        $this->setLastCursor($cursor);

        return $resultSet;
    }

    /**
     * Return a Hydrating Resultset object Alias of find
     * @see http://www.php.net/manual/en/class.mongocollection.php
     *
     * @param array $query
     * @param null $entityPrototype
     * @param null $hydrator
     * @param bool $findAll
     * @return bool|HydratingResultSet
     */
    public function select($query = array(), $fields = array(), $entityPrototype = null, $hydrator = null, $findAll = true)
    {
        return find($query, $fields, $entityPrototype, $hydrator, $findAll);
    }

    /**
     * Return a single document based on standard MongoCollection format
     * @see http://www.php.net/manual/en/class.mongocollection.php
     *
     * @param array $query
     * @param null $entityPrototype
     * @param HydratorInterface $hydrator
     * @return bool|HydratingResultSet
     */
    public function findOne($query = array(), $fields = array(), $entityPrototype = null, HydratorInterface $hydrator = null)
    {
        return $this->find($query, $fields, $entityPrototype, $hydrator, false);
    }

    /**
     * Return a single document based on standard MongoCollection format
     * Alias for FindOne
     *
     * @see http://www.php.net/manual/en/class.mongocollection.php
     *
     * @param array $query
     * @param null $entityPrototype
     * @param HydratorInterface $hydrator
     * @return bool|HydratingResultSet
     */
    public function selectOne($query = array(), $fields = array(), $entityPrototype = null, HydratorInterface $hydrator = null)
    {
        return $this->find($query, $fields, $entityPrototype, $hydrator, false);
    }

    /**
     * Insert Document
     * @param $entity
     * @param array $options
     * @return mixed
     */
    public function insert($entity, $options = array())
    {
        $this->initialise();
        $collection = $this->getCollectionPrototype();

        $rowData = $this->getHydrator()->extract($entity);
        if (is_null($rowData['_id'])) unset($rowData['_id']);

        $collection->insert($rowData, $options);

        return $rowData;
    }

    /**
     * Update Document
     * @see http://www.php.net/manual/en/mongocollection.update.php
     *
     * @param $entity
     * @param null $where
     * @param null $collectionName
     * @param HydratorInterface $hydrator
     * @return bool
     */
    public function update($entity, array $where = null, array $options = array(), $collectionName = null, HydratorInterface $hydrator = null)
    {
        $this->initialise();
        $collectionName = $collectionName ?: $this->collection;
        $hydrator = (!$hydrator)?$this->getHydrator():$hydrator;

        $this->getDbAdapter()->selectCollection($collectionName);
        $collection = $this->getCollectionPrototype();

        if (!$where) {
            $id = $entity->get_id();
            if ($id instanceof MongoId) {
                $id = $this->mongoId($id);
            }
            $where = array('_id'=>$id);
        }

        $rowData = $hydrator->extract($entity);

        $collection->update($where, $rowData, $options);

        return $collection->update($rowData);
    }

    /**
     * Delete Document
     * @param $entity
     * @param array $where
     * @param array $options
     *
     * @return mixed
     */
    public function remove($entity, array $where = null, array $options = array())
    {
        $this->initialise();

        if (!$where) {
            $id = $entity->get_id();
            if ($id instanceof MongoId) {
                $id = $this->mongoId($id);
            }
            $where = array('_id'=>$id);
        }

        $collection = $this->getCollectionPrototype();
        $result = $collection->remove($where, $options);

        return $result;
    }

    /**
     * Remove Document (Alias of Delete)
     * @param $entity
     * @param array $where
     * @param array $options
     * @return mixed
     */
    public function delete($entity, array $where = null, array $options = array())
    {
        return $this->remove($entity, $where, $options);
    }

    /**
     * Generate a MongoId Object
     * @param $id
     * @return bool|MongoId
     */
    public function mongoId($id)
    {
        if (empty($id)) return false;
        return new MongoId($id);
    }

    /**
     * Get the Mongo Collection Prototype
     * @var collectionPrototype MongoDb
     * @return MongoCollection
     */
    public function getCollectionPrototype()
    {
        // Fetch the collection to this object
        if (!$this->collectionPrototype) {
            $this->collectionPrototype = $this->getDbAdapter()->getDriver()->getConnection()->getResource()->{$this->database}->{$this->collection};
        }

        // Return the collection
        return $this->collectionPrototype;
    }

    /**
     * Set the DB Adapter
     * @param array $config
     *
     * @return $this
     * @throws MongoConnectionException
     * @throws MongoException
     */
    public function setDbAdapter($adapter)
    {
        $this->dbAdapter = $adapter;
        return $this;
    }

    /**
     * Get MongoClient Instance
     * @return \Mongo\Db\Adapter\Adapter
     */
    public function getDbAdapter()
    {
        return $this->dbAdapter;
    }

    /**
     * Set Entity Prototype
     * @param mixed $entityPrototype
     */
    public function setEntityPrototype($entityPrototype)
    {
        $this->entityPrototype = $entityPrototype;
        return $this;
    }

    /**
     * Get Entity Prototype
     * @return mixed
     */
    public function getEntityPrototype()
    {
        return $this->entityPrototype;
    }

    /**
     * Set Hydrator
     * @param $hydrator ClassMethods
     *
     * @return $this
     */
    public function setHydrator(ClassMethods $hydrator)
    {
        $this->hydrator = $hydrator;
        return $this;
    }

    /**
     * Get Hydrator
     * @return ClassMethods
     */
    public function getHydrator()
    {
        if (!$this->hydrator) {
            $this->hydrator = new ClassMethods(false);
        }
        return $this->hydrator;
    }

    /**
     * Set last cursor object
     * @param \MongoCursor $lastCursor
     */
    public function setLastCursor($lastCursor)
    {
        $this->lastCursor = $lastCursor;

        return $this;
    }

    /**
     * Get last cursor object
     * @return \MongoCursor
     */
    public function getLastCursor()
    {
        return $this->lastCursor;
    }


    /**
     * Trigger an unrecoverable database exception
     * @param Exception $e
     */
    protected function databaseException( \Exception $e)
    {
        // Get the app and event manager
        $app = $this->getServiceLocator()->get('Application');
        $events = $app->getEventManager();

        // Set up the occurred event
        $event = $app->getMvcEvent();
        $event->setError(Application::ERROR_EXCEPTION)
            ->setParam('exception', $e);

        // Fire event from the app's event manager
        $events->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $event);
        $event->setTarget($app);
        $events->trigger(MvcEvent::EVENT_RENDER, $event);
        $events->trigger(MvcEvent::EVENT_FINISH, $event);

        // Graceful exit
        exit();
    }

}