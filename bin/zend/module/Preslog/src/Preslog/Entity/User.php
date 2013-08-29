<?php
/**
 * User entity
 * Container for User Data
 */
namespace Preslog\Entity;

class User
{
    protected $_id;
    protected $firstName;
    protected $lastName;
    protected $email;
    protected $password;
    protected $company;
    protected $phoneNumber;
    protected $role;                    // Role this user has (admin, operator, etc)
    protected $clientId;                // Client ID this user is assigned to
    protected $notifications;           // Notifications entity
    protected $favouriteDashboards;     // List of favourite dashboards


    /**
     * @param mixed $id
     */
    public function set_id($id)
    {
        $this->_id = $id;
    }

    /**
     * @return mixed
     */
    public function get_id()
    {
        return $this->_id;
    }

    /**
     * @param mixed $clientId
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * @return mixed
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param mixed $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * @return mixed
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $favouriteDashboards
     */
    public function setFavouriteDashboards($favouriteDashboards)
    {
        $this->favouriteDashboards = $favouriteDashboards;
    }

    /**
     * @return mixed
     */
    public function getFavouriteDashboards()
    {
        return $this->favouriteDashboards;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $notifications
     */
    public function setNotifications($notifications)
    {
        $this->notifications = $notifications;
    }

    /**
     * @return mixed
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return mixed
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param mixed $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    public function getRoles() { return $this->getRole(); }
    public function setRoles( $roles ) { return $this->setRole( $roles ); }
    public function getId() { return $this->get_id(); }
    public function setId( $id ) { return $this->set_id($id); }



}