<?php
/**
 * User entity
 * Container for User Data
 */
namespace Preslog\Entity;

class User
{
    /** @var    \MongoId    $id         */
    protected $id;

    /** @var    string      $firstName  */
    protected $firstName;

    /** @var    string      $lastName   */
    protected $lastName;

    /** @var    string      $email   */
    protected $email;

    /** @var    string      $password   */
    protected $password;

    /** @var    string      $company   */
    protected $company;

    /** @var    string      $phoneNumber   */
    protected $phoneNumber;

    /** @var    string      $role                   Role (thus permissions) this user has */
    protected $role;

    /** @var    \MongoId    $clientId               Client ID this user is assigned to */
    protected $clientId;

    /** @var    array       $notifications          Notifications this client is attached to */
    protected $notifications;

    /** @var    \MongoId[]  $favouriteDashboards    List of dashboards this user has as favourites */
    protected $favouriteDashboards;

    /** @var    boolean     $deleted                Had this user been deleted? */
    protected $deleted;


    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return  string
     */
    public function getId()
    {
        return $this->id;
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

    /**
     * @param mixed $deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    /**
     * @return mixed
     */
    public function getDeleted()
    {
        return $this->deleted;
    }


    public function getRoles() { return $this->getRole(); }
    public function setRoles( $roles ) { return $this->setRole( $roles ); }

    public function get_id() { return $this->getId(); }
    public function set_id( $id ) { return $this->setId($id); }

}