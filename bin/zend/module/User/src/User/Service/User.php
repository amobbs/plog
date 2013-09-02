<?php

namespace User\Service;

use ZfcUser\Service\User as zfcUser;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Crypt\Password\Bcrypt;

class User extends zfcUser implements ServiceManagerAwareInterface
{

    /**
     * Fetch current users permissions
     * @return array
     */
    public function getPermissions()
    {
        // Fetch rbac Service so we can harvest Permissions
        /** @var \ZfcRbac\Service\Rbac $rbacService */
        $rbacService = $this->getServiceManager()->get('ZfcRbac\Service\Rbac');
        $providers = $rbacService->getOptions()->getProviders();

        // Extract the many types
        foreach ($providers as $type)
        {
            $item = current($type);
            $types[ key($type) ] = $item;
        }

        // Extract list of roles
        foreach ($types['roles'] as $k=>$v)
        {
            $roles[] = $k;
        }

        // For each role..
        foreach ($types['permissions'] as $k=>$v)
        {
            // For each permissions
            foreach ($v as $p)
            {
                // Get unique permissions
                $perms[$p] = $p;
            }
        }

        // Filter the list just to the permissions this user has access to
        foreach ($perms as $perm)
        {
            if ( $rbacService->isGranted($perm) )
            {
                $userPerms[] = $perm;
            }
        }

        return $userPerms;
    }


    public function sendReset($postData, $url)
    {
        $user = $this->getUserMapper();

        $emailValidator = new \Zend\Validator\Db\RecordExists(array(
            'table' => 'user',
            'field' => 'email',
            'adapter' => $this->getServiceManager()->get('Zend\Db\Adapter\Adapter')
        ));

        if ($emailValidator->isValid($postData['identity'])) {

            $email = $postData['identity'];
            // Get the current user
            $currentUser = $this->getUserMapper()->findByEmail($email);
            $user_id = $currentUser->getId();

            // Build a unique, secure key
            $bcrypt = new Bcrypt;
            $bcrypt->setCost($this->getOptions()->getPasswordCost());
            $key = md5($bcrypt->create(time() . $email));

            // Generate the reset row
            $user->buildReset($user_id, $key);

            // Send the email
            $email = $this->getServiceManager()->get('email');
            $config = $email->getConfig();
            $email->to($postData['identity'])
                ->subject('Password Reset')
                ->setTemplate('email/reset')
                ->setType('html')
                ->setVars(array('site_name' => $config['site_name'],
                    'site' => $this->getServiceManager()->get('request')->getServer('HTTP_HOST'),
                    'emailLink' => $url . '/' . $key
                ))
                ->send();

            return true;
        }
    }

    public function resetPassword($newPass, $user_id)
    {
        $currentUser = $this->getUserMapper()->findById($user_id);;
        $bcrypt = new Bcrypt;
        $bcrypt->setCost($this->getOptions()->getPasswordCost());

        $pass = $bcrypt->create($newPass);
        $currentUser->setPassword($pass);

        $this->getEventManager()->trigger(__FUNCTION__, $this, array('user' => $currentUser));
        $this->getUserMapper()->update($currentUser);
        $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('user' => $currentUser));

        return true;
    }

    public function checkReset($key)
    {
        $user = $this->getServiceManager()->get('zfcuser_user_mapper');
        return $user->checkReset($key);
    }

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param ServiceManager $serviceManager
     * @return User
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }
}