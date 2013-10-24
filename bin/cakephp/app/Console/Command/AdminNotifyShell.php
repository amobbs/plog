<?php

App::uses('AppShell', 'Console/Command');
App::uses('CakeEmail', 'Network/Email');
App::uses('Set', 'Utility');

use Preslog\Logs\Entities\LogEntity;
/**
 * Class AdminNotifyShell
 *
 * @property Log Log
 * @property Client Client
 */
class AdminNotifyShell extends AppShell {
    public function main()
    {
        $this->loadModel('Log');
        $this->loadModel('Client');
        $this->loadModel('User');

        if (! $supervisors = $this->User->find('all', array('conditions' => array('role' => 'supervisor')))) {
            // No supervisors?
            return;
        }

        $dashboardId = Configure::read('Preslog.Dashboards.unqualified');

        $DateTime = new DateTime('now');
        $today = $DateTime->format('Y-m-d');
        $yesterday = $DateTime->modify('-1 Day')->format('Y-m-d');
        $niceDate = $DateTime->format('M jS, Y');
        $logs = $this->Log->findByQuery('created > ' . $yesterday . ' AND created < ' . $today . ' AND status = empty()', true);
        $logEntity = new LogEntity;
        $logEntity->setDataSource( $this->Log->getDataSource() );
        foreach ($logs as $k => $log) {
            $logEntity->setClientEntity($this->Client->getClientEntityById($log['Log']['client_id']));
            $logEntity->fromDocument($log['Log']);
            $logs[$k]['Log'] = $logEntity->toDisplay();
        }

        // No need to send an email if there are no logs.
        if (empty($logs)) {
            return;
        }

        $Email = new CakeEmail();
        $Email->config('default')
            ->subject('Logs not currently signed off for ' . $niceDate)
            ->template('admin-not-signed-off-notification')
            ->viewVars(compact('logs', 'niceDate', 'dashboardId'))
            ->emailFormat('html')
            ->to(Set::extract('/User/email', $supervisors))
            ->send();
    }
}