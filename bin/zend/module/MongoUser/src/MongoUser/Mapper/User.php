<?php
namespace MongoUser\Mapper;
use ZfcUser\Mapper\UserInterface as UserInterface;

use Mongo\Mapper\DbAbstract;
use MongoId;
use Exception;

class User extends DbAbstract implements UserInterface
{
    protected $database = '';
    protected $collection  = '';

    public function setConfig($config)
    {

        if ($config['mongo']['user']['database'] == '' || $config['mongo']['user']['collection'] == '') {
            throw new Exception('Database and Collection have not been set. Ensure mongouser.local.php has been copied to your autoload folder.');
        }

        $this->database = $config['mongo']['user']['database'];
        $this->collection = $config['mongo']['user']['collection'];

        $this->setDbAdapter($config);
    }

    public function findByEmail($email)
    {
        $entity = $this->selectOne(array('email' => $email));

        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }

    public function findByUsername($username)
    {
        $entity = $this->selectOne(array('username' => $username));

        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }

    public function findById($id)
    {
        $entity = $this->selectOne(array('_id' => new MongoId($id)));

        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }

    public function insert($entity, $tableName = null, HydratorInterface $hydrator = null) {

        // Insert and ensure that the entry is written to the journal before continuing!
        // Note: This is the slowest possible write. Minimum time 20ms
        $result = parent::insert($entity, array("j" => true));
        $entity->setId($result['_id']);
        return $result;
    }

    public function update($user) {

    }
}