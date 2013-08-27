<?php
namespace MongoUser\Entity;
use ZfcUser\Entity\UserInterface;

class User implements UserInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $firstName;

    /**
     * @var string
     */
    protected $lastName;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $company;

    /**
     * @var string
     */
    protected $phoneNumber;

    /**
     * @var string
     */
    protected $client;

    /**
     * @var string
     */
    protected $dashboards;

    /**
     * @var string
     */
    protected $notifications;

    /**
     * @var int
     */
    protected $role;


    public function getId()
    {
        if (!$this->id) return null;
        return $this->id;
    }

    public function setId($id)
    {
        if (is_object($id)) {
            $id = (string) $id;
        }
        $this->id = $id;

        return $this;
    }

    public function set_id($id)
    {
        return $this->setId( $id );
    }

    public function get_id()
    {
        return $this->getId();
    }

    /**
     * Get username.
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set username.
     *
     * @param   string $str
     * @return  UserInterface
     */
    public function setFirstName( $str )
    {
        $this->firstName = $str;
        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email.
     *
     * @param string $email
     * @return UserInterface
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get last name.
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set last name.
     *
     * @param string $str
     * @return UserInterface
     */
    public function setLastName( $str )
    {
        $this->lastName = $str;
        return $this;
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set password.
     *
     * @param string $password
     * @return UserInterface
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Get state.
     *
     * @return int
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set Role.
     *
     * @param int $str
     * @return UserInterface
     */
    public function setRole( $str )
    {
        $this->role = $str;
        return $this;
    }

    /**
     * @param string $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * @return string
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param string $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param string $dashboards
     */
    public function setDashboards($dashboards)
    {
        $this->dashboards = $dashboards;
    }

    /**
     * @return string
     */
    public function getDashboards()
    {
        return $this->dashboards;
    }

    /**
     * @param string $notifications
     */
    public function setNotifications($notifications)
    {
        $this->notifications = $notifications;
    }

    /**
     * @return string
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * @param string $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }



    public function getUsername() {}

    public function setUsername( $username ) {}

    public function getDisplayName() {}

    public function setDisplayName( $displayName ) {}

    public function getState() {}

    public function setState($state) {}

    public function getRoles() {
        return $this->role;
    }

    public function setRoles($roles) {
        $this->role = $roles;
        return $this;
    }


}
