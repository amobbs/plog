<?php

App::uses('AppShell', 'Console/Command');
App::uses('CakeEmail', 'Network/Email');
App::uses('Set', 'Utility');

/**
 * Class FixBrokenLogsShell
 *
 * @property Log Log
 */
class FixBrokenLogsShell extends AppShell {

	public function main()
	{
		echo 'Starting' . "\n";
		$this->loadModel('Log');
		echo 'Loaded model' . "\n";

		$logs = $this->Log->find('all', array(
			'order' => array('_id' => -1),
			'limit' => 1000,
		));
		foreach($logs as $log) {
			$this->Log->set($log['Log']);
			$this->Log->ensureClientSelectAttributes();
		}


	}
}