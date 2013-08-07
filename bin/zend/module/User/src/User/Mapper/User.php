<?php
namespace User\Mapper;

use MongoUser\Mapper\User as ZfcUser;
use Zend\Crypt\Password\Bcrypt;

class User extends ZfcUser
{
    protected $tableName = 'user';

    public function findAll($field = null, $value = null)
    {
        $select = $this->getSelect();

        if (!is_null($field) AND !is_null($value)) {
            $select->where(array($field=>$value));
        }

        $entity = $this->select($select)->toArray();
        return $entity;
    }

    public function getList() {
        $users = $this->findAll();

        $rows = array();
        foreach ($users as $user) {
            $rows[] = array($user['user_id'], $user['display_name'], $user['email']);
        }

        return $rows;
    }

    public function buildReset($user_id, $key)
    {
        $userValidator = new \Zend\Validator\Db\RecordExists(array(
            'table' => 'user_password_reset', 'field' => 'user_id', 'adapter' => $this->getDbAdapter()
        ));

        if (! $userValidator->isValid($user_id)) {
            $query = $this->getSql()
                ->insert('user_password_reset')
                ->values(array('user_id' => $user_id, 'request_key' => $key));
        }
        else {
            $query = $this->getSql()
                ->update('user_password_reset')
                ->set(array('request_key' => $key))
                ->where(array('user_id' => $user_id));
        }
        $this->getSql()->prepareStatementForSqlObject($query)->execute();

        return $key;
    }

    public function findByResetKey($key)
    {
        // Redundant
        $select = $this->getSelect('user_password_reset')->where(array('request_key'=>$key));
        $userResult = $this->getSql()->prepareStatementForSqlObject($select)->execute();

        foreach ($userResult as $user) {
            return $user['user_id'];
        }

    }

    public function checkReset($key)
    {
        $keyValidator = new \Zend\Validator\Db\RecordExists(array(
            'table' => 'user_password_reset',
            'field' => 'request_key',
            'adapter' => $this->getDbAdapter()
        ));

        if ($keyValidator->isValid($key)) {
            return $this->findByResetKey($key);
        }

        return false;
    }

}