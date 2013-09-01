<?php
/**
 * User Mapper
 * Extension of the DB abstract; Translates actions (CRUD) into DB tasks (insert, update, delete, etc.
 */

namespace Preslog\Mapper;

use Zend\Stdlib\Hydrator\HydratorInterface;
use Mongo\Mapper\AbstractMapper;
use MongoId;

class User extends AbstractMapper implements \ZfcUser\Mapper\UserInterface
{
    protected $database = 'preslog';
    protected $collection  = 'users';

    /**
     * Insert this user
     * @param array|object $entity
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function insert($entity, $options = array())
    {
        $result = parent::insert($entity, $options);
        $entity->set_id($result->getGeneratedValue());
        return $result;
    }


    /**
     * Update this user
     * @param array|object $entity
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function update($entity, array $where = null, array $options = array(), $collectionName = null, HydratorInterface $hydrator = null)
    {
        return parent::update($entity, array('newsId'=>$entity->getNewsId()));
    }


    /**
     * Delete this user
     * @param array|string|\ZfcBase\Mapper\closure $id
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function delete($entity, array $where = null, array $options = array())
    {
        return parent::delete(array('newsId'=>$id));
    }


    /**
     * Fetch all users
     * @return bool|\Zend\Db\ResultSet\HydratingResultSet
     */
    public function findAllUsers()
    {
        return parent::find();
    }


    /**
     * Find a singler use by ID
     * @param $id
     * @return \Preslog\Entity\User
     */
    public function findById( $id )
    {
        // Instigating a MongoId with an invalid Id will cause an exception.
        try {
            $id = new MongoId($id);
        }
        catch (\MongoException $e) {
            return false;
        }

        // Perform find
        return parent::find(array(
            '_id'=> $id
        ), array(), null, null, false);
    }

    /**
     * Find a singler use by ID
     * @param $id
     * @return \Preslog\Entity\User
     */
    public function findByEmail( $email )
    {
        return parent::find(array(
            'email'=> $email
        ), array(), null, null, false);
    }

    public function findByUsername( $username )
    {
        return parent::find(array(
            'username'=> $username
        ), array(), null, null, false);
    }

}
