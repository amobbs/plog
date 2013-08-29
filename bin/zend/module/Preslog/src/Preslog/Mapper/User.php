<?php
/**
 * User Mapper
 * Extension of the DB abstract; Translates actions (CRUD) into DB tasks (insert, update, delete, etc.
 */

namespace Preslog\Mapper;

use Mongo\Mapper\DbAbstract as AbstractDbMapper;
use MongoId;

class User extends AbstractDbMapper
{
    protected $database = 'preslog';
    protected $collection  = 'users';

    /**
     * Insert this user
     * @param array|object $entity
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function insert($entity)
    {
        $result = parent::insert($entity);
        $entity->set_id($result->getGeneratedValue());
        return $result;
    }


    /**
     * Update this user
     * @param array|object $entity
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function update($entity)
    {
        $result = parent::update($entity, array('newsId'=>$entity->getNewsId()));
        return $result;
    }


    /**
     * Delete this user
     * @param array|string|\ZfcBase\Mapper\closure $id
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function delete($id)
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

}
