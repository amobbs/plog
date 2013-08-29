<?php
/**
 * User Service
 * Handles user-related actions (find, findall, update, etc)
 */

namespace Preslog\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;

class User extends EventProvider implements ServiceManagerAwareInterface
{

    /**
     * Fetch all users
     * return bool|\Zend\Db\ResultSet\HydratingResultSet
     */
    public function findAll()
    {
        return $this->getMapper()->findAllUsers();
    }


    /**
     * Fetch the specified user
     * return bool|\Preslog\Entity\User
     */
    public function findById( $id )
    {
        return $this->getMapper()->findById($id);
    }


    /**
     * Save this entity
     * @param $arrayData
     */
    public function save($arrayData)
    {
        $entity = $this->getMapper()->getEntityPrototype();
        $this->getMapper()->getHydrator()->hydrate($arrayData, $entity);

        // Update / Insert
        if (!is_null($entity->getId())) {
            $this->getMapper()->update($entity);
        } else {
            $this->getMapper()->insert($entity);
        }
    }


    /**
     * Delete this item
     * @param $id
     */
    public function delete($id)
    {
        $this->getMapper()->delete($id);
    }


    /**
     * Configure
     * @return mixed
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }


    /**
     * Attach the service manager
     * @param ServiceManager $serviceManager
     * @return $this
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }


    /**
     * Fetch the entity from the mapper.
     * Used when constructing a new entity.
     * @return mixed
     */
    function getEntity()
    {
        return $this->getMapper()->getEntityPrototype();
    }


    /**
     * Set the mapper
     * @param $mapper
     * @return $this
     */
    function setMapper($mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }


    /**
     * Get the mapper
     * @return mixed
     */
    function getMapper()
    {
        return $this->mapper;
    }

}