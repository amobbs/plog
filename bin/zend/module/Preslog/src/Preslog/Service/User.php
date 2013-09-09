<?php
/**
 * User Service
 * Handles user-related actions (find, findall, update, etc)
 */

namespace Preslog\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use Preslog\Entity\User as UserEntity;

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
        return $this->getMapper()->findById( $id );
    }


    /**
     * Update the given entity
     * @param   mixed   $entity
     */
    public function update( $entity )
    {
        return $this->getMapper()->update($entity);
    }


    /**
     * Insert the given entity
     * @param $entity
     */
    public function insert( $entity )
    {
        return $this->getMapper()->insert($entity);
    }


    /**
     * Delete this item
     * @param   mixed    $id
     */
    public function delete($id)
    {
        return $this->getMapper()->delete($id);
    }

    /**
     * Extract the array, giving fields the front UI will use for login
     * @param   UserEntity    $userObject
     * @return  array
     */
    public function extractForLogin( UserEntity $userObject )
    {
        $user = $this->getMapper()->getHydrator()->extract($userObject);
        return $user;
    }


    /*****************************************************************
     * Object setup methods
     */

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