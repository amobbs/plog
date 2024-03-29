<?php

//App::uses('CakeLogInterface', 'Log');

use Swagger\Annotations as SWG;

/**
 * Import Controller
 * - Wipes data from Preslog Log, Client, User tables
 * - Imports from Preslog/Mediahub SQL databases, converting content to Mongo
 */
class ImportController extends AppController
{
    public $uses = array('Client', 'Log', 'User');

    private $mysqli = null;
    private $arrayLog = array();

    private $logTotal = 0;

    private $timezone = 'Australia/Sydney';

    protected $userLookup = array();
    protected $userDefault = null;

    /**
     * Log messages
     * @param $msg
     */
    private function _log($msg) {
        CakeLog::write('activity', $msg);
        $this->arrayLog[] = $msg;

        echo '<br/>'.$msg;
    }

    /**
     * Perform the import process
     */
    function runImport() {

        set_time_limit(0);

        // Output to screen
        ob_implicit_flush(true);
        ob_end_flush();
        echo '';

        $startTime = microtime(true);
        $this->_log('-starting import script at ' . date('Y-m-d H:s'));

        //clean mongodb
        $mongo = $this->Log->getDataSource();
        $mongo->truncate('clients');
        $mongo->truncate('logs');
        $mongo->truncate('users');

        $this->_log('-mongodb cleaned');

        //setup where we want to get the data
        $importDetails = $this->_initDetails();

        $dbName = '';
        //start importing data
        $this->_log('-importing data');

        $clients = array();
        foreach($importDetails as $client) {
            if ($dbName != $client['database']) {
                $this->_getDbConn($client['database']);
                $dbName = $client['database'];
            }

            $clients[] = $this->_createClient($client);
        }

        $users = $this->_createUsers($clients);

        foreach($clients as $client) {
            if ($dbName != $client['database']) {
                $this->_getDbConn($client['database']);
                $dbName = $client['database'];
            }
            $this->_createLogs($client, $users);
        }

        $this->_log("-import complete, total number of logs = $this->logTotal");

        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;

        echo 'Success! Run time = ' . $totalTime . 's';
        exit();
    }

    /**
     * Fetch an SQL db connection
     * @param $dbName
     * @throws Exception
     */
    private function _getDbConn($dbName) {
        $this->mysqli = new mysqli("127.0.0.1", "root", "", $dbName);
        if ($this->mysqli->connect_errno) {
            throw new Exception("Failed to connect to MySQL: (" . $this->mysqli->connect_errno . ") " . $this->mysqli->connect_error);
        }
    }

    /**
     * Initialise the Client list.
     * @return array
     */
    private function _initDetails() {
        $baseFormats = $this->_getBaseFormats();
        $clients = array(
            array(
                'database' => 'mediahub',
                'database_prefix' => 'ausnet',
                'name' => 'Ausnet',
                'shortName' => 'aus',
                'contact' => '',
                'logPrefix' => 'AUS',
                'activationDate' => new MongoDate(strtotime('now')),
                'fields' => $baseFormats,
                'attributes' => array(),
                'benchmark' => 0.30
            ),
            array(
                'database' => 'mediahub',
                'database_prefix' => 'mediahub',
                'name' => 'MediaHub',
                'shortName' => 'MH',
                'contact' => '',
                'logPrefix' => 'MH',
                'activationDate' => new  MongoDate(strtotime('now')),
                'fields' => $baseFormats,
                'attributes' => array(),
                'benchmark' => 0.01
            ),
            array(
                'database' => 'mediahub',
                'database_prefix' => 'sbs',
                'name' => 'SBS',
                'shortName' => 'SBS',
                'contact' => '',
                'logPrefix' => 'SBS',
                'activationDate' => new  MongoDate(strtotime('now')),
                'fields' => $baseFormats,
                'attributes' => array(),
                'benchmark' => 0.30
            ),
            array(
                'database' => 'mediahub',
                'database_prefix' => 'tvn',
                'name' => 'TVN',
                'shortName' => 'TVN',
                'contact' => '',
                'logPrefix' => 'TVN',
                'activationDate' => new  MongoDate(strtotime('now')),
                'fields' => $baseFormats,
                'attributes' => array(),
                'benchmark' => 0.01
            ),
            array(
                'database' => 'mediahub',
                'database_prefix' => 'win',
                'name' => 'WIN',
                'shortName' => 'win',
                'contact' => '',
                'logPrefix' => 'WIN',
                'activationDate' => new  MongoDate(strtotime('now')),
                'fields' => $baseFormats,
                'attributes' => array(),
                'benchmark' => 0.4795
            ),
            array(
                'database' => 'preslog',
                'database_prefix' => 'pres',
                'name' => 'ABC',
                'shortName' => 'ABC',
                'contact' => '',
                'logPrefix' => 'ABC',
                'activationDate' => new  MongoDate(strtotime('now')),
                'fields' => $baseFormats,
                'attributes' => array(),
                'benchmark' => 0.147
            ),
        );

        return $clients;
    }

    /**
     * Create the basic log format
     * @return array
     */
    private function _getBaseFormats() {
        $order = 0;
        $baseFormats = array(
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'loginfo',
                'name' => 'loginfo',
                'label' => 'Log Info',
                'data' => null,
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'datetime',
                'name' => 'datetime',   // LOGDATE/LOGTIME
                'label' => 'DATE:',
                'data' => null,
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'duration',
                'name' => 'duration',   // DURATION
                'label' => 'DURATION:',
                'data' => null,
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'textbig',
                'name' => 'program',    // PROGRAM
                'label' => 'PROGRAMME or EVENT:',
                'data' => array('placeholder' => 'Name of programme, event, promo etc.'),
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'select-impact',
                'name' => 'impact',     // ONAIRIMPACT
                'label' => 'ON-AIR IMPACT:',
                'data' => array(
                    'placeholder' => 'Choose from below',
                    'options' => array(
                        '?'
                    )
                ),
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'textsmall',
                'name' => 'asset_id',   // ASSETID
                'label' => 'ASSET ID:',
                'data' => array('placeholder' => 'The scheduled ID, if any, goes here'),
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'textarea',
                'name' => 'description', // DESCRIPTION
                'label' => 'BRIEF DESCRIPTION:',
                'data' => array('placeholder' => 'A brief summation goes here'),
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'select',
                'name' => 'what',        // IMPACT
                'label' => 'WHAT HAS HAPPENED:',
                'data' => array(
                    'placeholder' => 'Choose from below',
                    'options' => array('?'),
                ),
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'select',
                'name' => 'why',        // CAUSE
                'label' => 'WHY IT HAPPENED:',
                'data' => array(
                    'placeholder' => 'Choose from below',
                    'options' => array('?'),
                ),
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'textarea',
                'name' => 'details',     // DETAILS
                'label' => 'DETAILS of WHAT HAPPENED:',
                'data' => array('placeholder' => 'Details go here'),
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'textarea',
                'name' => 'action_taken', // ACTION
                'label' => 'WHAT ACTION TAKEN:',
                'data' => array('placeholder' => 'Details go here'),
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'textarea',
                'name' => 'follow_up',      // RESOLUTION
                'label' => 'FOLLOW UP or RESOLUTION:',
                'data' => array('placeholder' => 'Supervisor or Engineering follow up'),
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'select-severity',
                'name' => 'severity',       // SEVERITY
                'label' => 'SEVERITY:',
                'data' => array(
                    'placeholder' => 'Choose from below',
                    'options' => array(
                        '?',
                    ),
                ),
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'select',
                'name' => 'accountability',     // ACCOUNTABILITY
                'label' => 'ACCOUNTABILITY:',
                'data' => array(
                    'placeholder' => 'Supervisor Section',
                    'options' => array(
                        '?'
                    ),
                ),
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'select',
                'name' => 'status',             // STATUS
                'label' => 'STATUS:',
                'data' => array(
                    'placeholder' => 'Supervisor Section',
                    'options' => array(
                        '?'
                    ),
                ),
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'checkbox',
                'name' => 'for_reports',
                'label' => 'Show in Reports?',
                'data' => null,
            ),
        );

        return $baseFormats;
    }

    /**
     * Create a client
     * @param $client
     * @return mixed
     */
    private function _createClient($client) {
        $onairImpactId = -1;
        $severityId = -1;
        $accountabilityId = -1;
        $statusId = -1;
        //loop through format and set new mongo_ids
        for($i =0; $i < sizeof($client['fields']); $i++) {
            $client['fields'][$i]['_id'] = new MongoId();
            if($client['fields'][$i]['name'] == 'impact')   $onairImpactId = $i;
            if($client['fields'][$i]['name'] == 'severity') $severityId = $i;
            if($client['fields'][$i]['name'] == 'accountability') $accountabilityId = $i;
            if($client['fields'][$i]['name'] == 'status')   $statusId = $i;
            if($client['fields'][$i]['name'] == 'what')     $impactId = $i;
            if($client['fields'][$i]['name'] == 'why')      $causeId = $i;
        }

        // Impact
        $sql = 'SELECT id, name, deleted FROM ' . $client['database_prefix'] . '_onairimpact';
        if ($result = $this->mysqli->query($sql)) {
            $options = array();
            $i = 0;
            while ($row = $result->fetch_assoc()) {
                $options[] = array(
                    '_id' =>  new MongoId(),
                    'name' => mb_convert_encoding($row['name'], 'utf8'),
                    'order' => $i++,
                    'deleted' => ($row['deleted'] == 1 ? true : false),
                    'old_id' => $row['id'], //used to find attr later
                );
            }
            $client['fields'][$onairImpactId]['data']['options'] = $options;
        }

        // Severity lookyp table
        $severityLookup = array(
            0=>'other',
            1=>'level-1',
            2=>'level-2',
            3=>'reported',
            4=>'reported',
        );

        // Severity
        $sql = 'SELECT id, severity, level, deleted, `order`, validation FROM ' . $client['database_prefix'] . '_severity ORDER BY `order`';
        if ($result = $this->mysqli->query($sql)) {
            $options = array();
            while ($row = $result->fetch_assoc()) {
                $options[] = array(
                    '_id' =>  new MongoId(),
                    'name' => mb_convert_encoding($row['severity'], 'utf8'),
                    'severity' => $severityLookup[ $row['level'] ],
                    'order' => $row['order'],
                    'deleted' => (bool)$row['deleted'],
                    'old_id' => $row['id'],
                );
            }
            $client['fields'][$severityId]['data']['options'] = $options;
        }

        // Accountability
        $sql = "SELECT id, name, `order`, deleted FROM " . $client['database_prefix'] . '_accountability ORDER BY `order`';
        if ($result = $this->mysqli->query($sql)) {
            $options = array();
            while ($row = $result->fetch_assoc()) {
                $options[] = array(
                    '_id' =>  new MongoId(),
                    'name' => mb_convert_encoding($row['name'], 'utf8'),
                    'order' => $row['order'],
                    'deleted' => (bool)$row['deleted'],
                    'old_id' => $row['id'],
                );
            }
            $client['fields'][$accountabilityId]['data']['options'] = $options;
        }

        // Status
        $sql = "SELECT id, name FROM " . $client['database_prefix'] . '_status';
        if ($result = $this->mysqli->query($sql)) {
            $options = array();
            $order = 0;
            while ($row = $result->fetch_assoc()) {
                $options[] = array(
                    '_id' =>  new MongoId(),
                    'name' => mb_convert_encoding($row['name'], 'utf8'),
                    'order' => $order++,
                    'deleted' => false,
                    'old_id' => $row['id'],
                );
            }
            $client['fields'][$statusId]['data']['options'] = $options;
        }

        // Impact / What happened
        $sql = "SELECT id, IMPACT, deleted FROM " . $client['database_prefix'] . '_impacts GROUP BY LOWER(IMPACT) ORDER BY `order`';
        if ($result = $this->mysqli->query($sql)) {
            $options = array();
            $order = 0;
            while ($row = $result->fetch_assoc()) {
                $options[] = array(
                    '_id' =>  new MongoId(),
                    'name' => mb_convert_encoding($row['IMPACT'], 'utf8'),
                    'order' => $order++,
                    'deleted' => ($row['deleted'] == 1 ? true : false),
                    'old_id' => $row['id'],
                );
            }
            $client['fields'][$impactId]['data']['options'] = $options;
        }

        // Cause / Why it happened
        $sql = "SELECT id, CAUSE, deleted FROM " . $client['database_prefix'] . '_causes GROUP BY LOWER(CAUSE) ORDER BY `order`';
        if ($result = $this->mysqli->query($sql)) {
            $options = array();
            $order = 0;
            while ($row = $result->fetch_assoc()) {
                $options[] = array(
                    '_id' =>  new MongoId(),
                    'name' => mb_convert_encoding($row['CAUSE'], 'utf8'),
                    'order' => $order++,
                    'deleted' => ($row['deleted'] == 1 ? true : false),
                    'old_id' => $row['id'],
                );
            }
            $client['fields'][$causeId]['data']['options'] = $options;
        }

        $attrs = array();

        $networks = array(
            '_id' => new MongoId(),
            'name' => 'networks',
            'label' => 'Networks',
            'network' => true,
            'deleted' => false,
            'children' => array(),
        );


        $networkCount = 0;
        $channelCount = 0;
        //wins networks/channels are backwards
        if ($client['name'] == 'WIN' || $client['name'] == 'MediaHub') {
            $sql = 'SELECT id, name, displayorder, deleted FROM ' . $client['database_prefix'] . '_channels ORDER BY displayorder';
            if ($result = $this->mysqli->query($sql)) {
                while ($row = $result->fetch_assoc()) {
                    $n = array(
                        '_id' => new MongoId(),
                        'name' => mb_convert_encoding($row['name'], 'utf8'),
                        'live_date' => new MongoDate(0),
                        'deleted' => (bool)$row['deleted'],
                        'children' => array(),
                    );

                    $networkSql = 'SELECT network, displayorder, id, channel_id, deleted FROM ' . $client['database_prefix'] . '_networks WHERE channel_id = ' . $row['id'] . ' order by displayorder';
                    if ($nResult = $this->mysqli->query($networkSql)) {
                        while ($nRow = $nResult->fetch_assoc()) {
                            $n['children'][] = array(
                                '_id' => new MongoId(),
                                'name' => mb_convert_encoding($nRow['network'], 'utf8'),
                                'deleted' => (bool)$nRow['deleted'],
                                'children' => array(),
                            );
                            $channelCount++;
                        }
                    }
                    $networks['children'][] = $n;
                    $networkCount++;
                }
            }
        } else {
            $sql = 'SELECT network, displayOrder, id, channel_id, deleted, short_code FROM ' . $client['database_prefix'] . '_networks ORDER BY network, displayorder';
            if ($result = $this->mysqli->query($sql)) {
               $networkChildren = array();
                while ($row = $result->fetch_assoc()) {
                    if (!isset($networkChildren[$row['network']])) {
                        $networkChildren[$row['network']] = array(
                            '_id' => new MongoId(),
                            'name' => mb_convert_encoding($row['network'], 'utf8'),
                            'deleted' => (bool)$row['deleted'],
                            'children' => array(),
                        );
                        $networkCount++;
                    }

                    $channelSql = 'SELECT name, deleted FROM ' . $client['database_prefix'] . '_channels WHERE id = ' . $row['channel_id'] . ' ORDER BY displayorder';
                    if($cResult = $this->mysqli->query($channelSql)) {
                        while($cRow = $cResult->fetch_assoc()) {
                            $networkChildren[$row['network']]['children'][] = array(
                                '_id' => new MongoId(),
                                'name' => mb_convert_encoding($cRow['name'], 'utf8'),
                                'deleted' => (bool)$cRow['deleted'],
                            );
                            $channelCount++;
                        }
                    }
                }
                $networks['children'] = array_values($networkChildren);
            }
        }

        $attrs[] = $networks;
        if ($client['name'] == 'ABC' || $client['name'] == 'SBS') {
            $useStates = array('VIC', 'SA', 'WA', 'NT', 'QLD', 'NSW', 'ACT' ,'TAS');
            $states = array(
                '_id' => new MongoId(),
                'name' => 'states',
                'label'=>'States',
                'network' => false,
                'deleted' => false,
                'children' => array(),
            );
            foreach ($useStates as $state) {
                $states['children'][] = array(
                    '_id' => new MongoId(),
                    'name' => $state,
                    'live_date' =>  new MongoDate(0),
                    'deleted' => false,
                    'children' => array(),
                );
            }
            $attrs[] = $states;
        }

        if($client['name'] == 'ABC' || $client['name'] == 'MediaHub') {
            $attrs[] = array(
                '_id' => new MongoId(),
                'label' => 'City / State',
                'name'=> 'city_state',
                'network' => false,
                'deleted' => false,
                'children' => array(
                    array(
                        '_id' => new MongoId(),
                        'name' => 'City',
                        'live_date' =>  new MongoDate(0),
                        'deleted' => false,
                        'children' => array(),
                    ),
                    array(
                        '_id' => new MongoId(),
                        'name' => 'State',
                        'live_date' =>  new MongoDate(0),
                        'deleted' => false,
                        'children' => array(),
                    ),
                ),
            );
        }


        $client['attributes'] = $attrs;
        $client['deleted'] = false;
        $client['_id'] = new MongoId();
        $returnClient = $client;


        //remove anything we dont want saved to mongo
        unset($client['database']);
        unset($client['database_prefix']);
        foreach($client['fields'] as $formatKey => $formatValue) {
            foreach($formatValue as $key => $val) {
                if (substr($key, 0, 4) == 'old_')
                    unset($client['fields'][$formatKey][$key]);
            }
        }

        $this->_log('adding client ' . $client['name'] . " with $networkCount networks containing $channelCount channels");
//        $this->Client->create($client);
        $this->Client->save(array('Client'=>$client), array('callbacks'=>false, 'validate'=>false));


        /**
         * Field Aliases
         */

        $aliasGroup = array(
            $causeId=>array(
                'Technical Issue'=>array('Technical'),
                'Scheduling'=>array('Timing / Scheduling Issue', 'Timing / Scheduling'),
                'Human Error'=>array('Human', 'Human error - Presentation Coordinator'),
                'Incoming Feed Issue'=>array('Incoming Feed Issue - TVN test 3'),
            ),
            $impactId=>array(
                'Marred program'=>array('Marred Programme'),
                'Crawl Request'=>array('Crawl to air'),
                'Captions Issue'=>array('Loss of captioning','Loss of captions'),
                'Commercial Hostlist (CHL)'=>array('Commercial Hotlist CHL'),
            ),

        );

        // Add aliases for opts
        foreach ($aliasGroup as $fieldId=>$aliasList)
        {
            foreach ($returnClient['fields'][$fieldId]['data']['options'] as $option)
            {
                foreach ($aliasList as $aKey=>$aliases)
                {
                    if ($aKey == $option['name'])
                    {
                        foreach ($aliases as $alias)
                        {
                            $optionAlias = $option;
                            $optionAlias['name'] = $alias;
                            $returnClient['fields'][$fieldId]['data']['options'][] = $optionAlias;
                        }
                    }
                }
            }
        }


        return $returnClient;
    }

    /**
     * Create users
     * @param $clients
     * @return array
     */
    private function _createUsers($clients) {
        $this->_log('-importing users');

        $csvFile = TMP . 'users_20130930.csv';

        $file = fopen($csvFile, 'r');

        $users = array();
        while (($line = fgets($file)) !== false) {
            $parts = explode(',', $line);

            //ignore column headers and blank lines or incomplete rows
            if ($parts[0] == 'email' || empty($parts[0]))
                continue;
            if (sizeof($parts) < 7)
                continue;

            // Prep object
            $user =  array(
                '_id' => new MongoId(),
                'password' => 'nopassword',
                'email' => mb_convert_encoding(trim($parts[0]), 'utf8'),
                'firstName' => mb_convert_encoding(trim($parts[1]), 'utf8'),
                'lastName' => mb_convert_encoding(trim($parts[2]), 'utf8'),
                'company' => mb_convert_encoding(trim($parts[3]), 'utf8'),
                'phoneNumber' => mb_convert_encoding(trim($parts[4]), 'utf8'),
                'role' => strtolower(mb_convert_encoding(trim($parts[6]), 'utf8')),
                'favouriteDashboards' => array(),
                'notifications' => array(
                    'methods' => array(
                        'sms' => false,
                        'email' => false,
                    ),
                    'clients' => array(),
                ),
            );

            // Set deleted/active
            $user['deleted'] = ($parts[5] == 'FALSE');

            // Attach client_id if we can find it from the given company name
            if (isset($parts[7])) {
                $clientId = null;
                foreach($clients as $client) {
                    if (strtolower($client['name']) == strtolower($user['company'])) {
                        $user['client_id'] = $client['_id'];
                        $user['clients'][] = array(
                            'client_id' => $client['_id'],
                            'attributes' => array(),
                        );
                    }
                }
            }

            // Save user to DB
            $this->User->create($user);
            $this->User->save();

            // Set default user
            if (empty($this->userDefault))
            {
                $this->userDefault = $user['_id'];
            }

            // Create alias lookup
            if(isset($parts[9]) && !empty($parts[9])) {
                $aliases = explode(',', $parts[9]);

                // Add to lookup list
                foreach ($aliases as $alias)
                {
                    $this->userLookup[ $alias ] = $user['_id'];
                }
            }

            $users[] = $user;
        }
        fclose($file);

        $this->_log(sizeof($users) . ' users');

        return $users;
    }

    /**
     * Locate an existing user in the user list; from the $existing users, find $user
     * @param $existing
     * @param $user
     * @return bool|int|string
     */
    private function _findExistingUser($existing, $user) {
        foreach($existing as $cleanName => $eUser) {
            foreach($eUser as $u) {
                if ($u == $user) {
                    return $cleanName;
                }
            }
        }

        return false;
    }

    /**
     * Create logs from the database, for $client using $users
     * @param $client
     * @param $users
     */
    private function _createLogs($client, $users) {
        $this->_log('creating logs for ' . $client['name']);
        $sql = '
            SELECT
                lognum as hrid,
                logdate,
                logtime,
                duration,
                cause,
                impact,
                description,
                details,
                loggedby,
                program,
                enteredtimestamp,
                action,
                severity,
                impairment,
                created,
                modified,
                version,
                vic, sa, wa, nt, qld, nsw, act, tas,
                spread,
                assetid,
                onairimpact,
                accountability,
                status,
                resolution
            FROM
                ' . $client['database_prefix'] . '_dailylog
            ORDER BY
                created ASC';

        $logCount = 0;
        $maxLog = 0;

        if($result = $this->mysqli->query($sql)) {
            while ($row = $result->fetch_assoc()) {

                // User lookup
                if (isset($this->userLookup[ $row['loggedby'] ]))
                {
                    $userId = $this->userLookup[ $row['loggedby'] ];
                }
                else
                {
                    $userId = $this->userDefault;
                }

                // Apply modified if not v1
                $modifiedBy = '';
                if ((int)$row['version'] == 1) {
                    $modifiedBy = $userId;
                }

                // Log fields
                $fields = array(
                    array(
                        'field_id' => $this->_getMongoIDFromFormatByName($client['fields'], 'loginfo'),
                        'data' => array(
                            'created' => new MongoDate(strtotime($row['created'] .' '. $this->timezone)),
                            'created_user_id' => $userId,
                            'modified' => new MongoDate(strtotime($row['modified'] .' '. $this->timezone)),
                            'modified_user_id' => $modifiedBy,
                            'version' => (int)$row['version']
                        ),
                    ),
                    array(
                        'field_id' => $this->_getMongoIDFromFormatByName($client['fields'], 'datetime'),
                        'data' => array(
                            'datetime' => new MongoDate(strtotime($row['logdate'] . ' ' . $row['logtime'] .' '. $this->timezone)),
                        ),
                    ),
                    array(
                        'field_id' => $this->_getMongoIDFromFormatByName($client['fields'], 'duration'),
                        'data' => array(
                            'seconds' => intval($row['duration']),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['fields'], 'program'),
                        'data' => array(
                            'text' => trim(mb_convert_encoding($row['program'], 'utf-8')),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['fields'], 'asset_id'),
                        'data' => array(
                            'text' => trim(mb_convert_encoding($row['assetid'], 'utf-8')),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['fields'], 'description'),
                        'data' => array(
                            'text' => trim(mb_convert_encoding($row['description'], 'utf-8')),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['fields'], 'impact'),
                        'data' => array(
                            'selected' => $this->_getMongoIdForOptionInSelect($client['fields'], 'impact', $row['onairimpact']),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['fields'], 'what'),
                        'data' => array(
                            'selected' => $this->_getMongoIdForOptionInSelectText($client['fields'], 'what', $row['impact']),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['fields'], 'why'),
                        'data' => array(
                            'selected' => $this->_getMongoIdForOptionInSelectText($client['fields'], 'why', $row['cause']),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['fields'], 'details'),
                        'data' => array(
                            'text' => trim(mb_convert_encoding($row['details'], 'utf-8')),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['fields'], 'action_taken'),
                        'data' => array(
                            'text' => trim(mb_convert_encoding($row['action'], 'utf-8')),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['fields'], 'follow_up'),
                        'data' => array(
                            'text' => trim(mb_convert_encoding($row['resolution'], 'utf-8')),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['fields'], 'severity'),
                        'data' => array(
                            'selected' => $this->_getMongoIdForOptionInSelect($client['fields'], 'severity', $row['severity']),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['fields'], 'accountability'),
                        'data' => array(
                            'selected' => $this->_getMongoIdForOptionInSelect($client['fields'], 'accountability', $row['accountability']),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['fields'], 'status'),
                        'data' => array(
                            'selected' => $this->_getMongoIdForOptionInSelect($client['fields'], 'status', $row['status']),
                        ),
                    ),
                );


                $attr = array();

                //only abc and media hub use the spread (city/state)
                if($client['name'] == 'ABC' || $client['name'] == 'MediaHub') {
                    $spread = $row['spread'];
                    if (strpos($spread, '1') > -1) //set city
                        $attr[] = $this->_getMongoIdForAttr($client['attributes'], 'City');
                    if (strpos($spread, '2') > -1) //set state
                        $attr[] = $this->_getMongoIdForAttr($client['attributes'], 'State');

                }

                // State to attr list
                if ($client['name'] == 'ABC' || $client['name'] == 'SBS') {
                    if($row['vic'] != null) $attr[] = $this->_getMongoIdForAttr($client['attributes'], 'VIC');
                    if($row['sa'] != null) $attr[] = $this->_getMongoIdForAttr($client['attributes'], 'SA');
                    if($row['wa'] != null) $attr[] = $this->_getMongoIdForAttr($client['attributes'], 'WA');
                    if($row['nt'] != null) $attr[] = $this->_getMongoIdForAttr($client['attributes'], 'NT');
                    if($row['qld'] != null) $attr[] = $this->_getMongoIdForAttr($client['attributes'], 'QLD');
                    if($row['nsw'] != null) $attr[] = $this->_getMongoIdForAttr($client['attributes'], 'NSW');
                    if($row['act'] != null) $attr[] = $this->_getMongoIdForAttr($client['attributes'], 'ACT');
                    if($row['tas'] != null) $attr[] = $this->_getMongoIdForAttr($client['attributes'], 'TAS');
                }

                // Network selection to Attr list
                $netSql = "
                    SELECT
                        NETWORK
                    FROM
                        ". $client['database_prefix'] ."_dailylog_networks AS dln
                        LEFT JOIN ". $client['database_prefix'] ."_networks AS n ON n.id = dln.network_id
                    WHERE
                        log_id = '{$row['hrid']}'
                ";

                if($netResult = $this->mysqli->query($netSql)) {
                    while ($netRow = $netResult->fetch_assoc()) {

                        // Don't try to save a null value.
                        if (empty($netRow['NETWORK']))
                        {
                            continue;
                        }

                        // Append MongoID to attr
                        $attr[] = $this->_getMongoIdForAttr($client['attributes'], $netRow['NETWORK']);
                    }
                }

                // log object
                $log = array(
                    '_id' => new MongoId(),
                    'hrid' => $row['hrid'],
                    'deleted' => false,
                    'hidden' => true,
                    'fields' => $fields,
                    'attributes' => $attr,
                    'client_id' => $client['_id'],
                );


                $this->_log("saving log - count: " . $this->logTotal . ' id: ' . $row['hrid'] .  ' for: ' . $client['name']);
                $this->Log->save(array('Log'=>$log), array('callbacks'=>false, 'validate'=>false));
                $logCount++;
                $this->logTotal++;

                // Track top log
                $maxLog = ($maxLog < $row['hrid'] ? $row['hrid'] : $maxLog);
            }

        }

        $this->_log("$logCount logs added for " . $client['name']);

        // Update client with top HRID digit
        $this->Client->save(array(
            'Client'=>array(
                '_id'=>$client['_id'],
                'logIncrement'=>$maxLog,
            )
        ), false, array('logIncrement'));
    }

    /**
     * Fetch the MongoID for the field $name from $formats
     * @param $formats
     * @param $name
     * @return mixed
     * @throws Exception
     */
    private function _getMongoIDFromFormatByName($formats, $name) {
        foreach($formats as $format) {
            if ($format['name'] == mb_convert_encoding($name, 'utf8')) {
                return $format['_id'];
            }
        }
        throw new Exception("could not find name[$name] in format provided");
    }

    /**
 * Fetch the MongoID from field $name in $format, with selected $id
 * @param $formats
 * @param $name
 * @param $id
 * @return string
 * @throws Exception
 */
    private function _getMongoIdForOptionInSelect($formats, $name, $id) {

        if (empty($id))
        {
            return '';
        }

        foreach($formats as $format) {
            if ($format['name'] == $name) {
                if (isset($format['data']['options'])) {
                    foreach($format['data']['options'] as $option) {
                        if (isset($option['old_id']) && strtolower($option['old_id']) == strtolower(mb_convert_encoding($id, 'utf8')))
                            return $option['_id'];
                    }
                }
            }
        }

        throw new Exception("unable to find option in [$name] for id [$id]");
    }


    /**
     * Fetch the MongoID from field $name in $format, with selected $text
     * @param $formats
     * @param $name
     * @param $text
     * @return string
     * @throws Exception
     */
    private function _getMongoIdForOptionInSelectText($formats, $name, $text) {

        if (empty($text))
        {
            return '';
        }

        // Find by exact name
        foreach($formats as $format) {
            if ($format['name'] == $name) {
                if (isset($format['data']['options'])) {
                    foreach($format['data']['options'] as $option) {

                        if (isset($option['name']) && strtolower(trim($option['name'])) == strtolower(trim(mb_convert_encoding($text, 'utf8'))))
                            return $option['_id'];

                        // Catch certain fields for later
                        if ($option['name'] == 'Equipment failure') $equipmentFailure = $option['_id'];
                        if ($option['name'] == 'Human error') $humanError = $option['_id'];
                        if ($option['name'] == 'Supplier') $supplier = $option['_id'];
                        if ($option['name'] == 'Scheduling Issue') $scheduling = $option['_id'];
                        if ($option['name'] == 'CHL Ammendment') $chl = $option['_id'];
                        if ($option['name'] == 'Technical Issue') $technical = $option['_id'];
                    }
                }
            }
        }

        //
        if (stripos($text, 'equipment') === 0) return $equipmentFailure;
        if (stripos($text, 'human') === 0) return $humanError;
        if (stripos($text, 'supplier') === 0) return $supplier;
        if (stripos($text, 'scheduling') === 0) return $scheduling;
        if (stripos($text, 'CHL') === 0) return $chl;
        if (stripos($text, 'technical') === 0) return $technical;


        throw new Exception("unable to find option in [$name] for text [$text]");
    }


    /**
     * Fetch the MongoID for the selected $name attribute in $attrs
     * @param $attrs
     * @param $name
     * @param $recursed
     * @return mixed
     * @throws Exception
     */
    private function _getMongoIdForAttr($attrs, $name, $recursed=false) {

        // On first pass, convert the name string
        if (!$recursed)
        {
            $name = mb_convert_encoding($name, 'utf8');
        }

        // is this array an entry?
        if (isset($attrs['name']))
        {
            if ($attrs['name'] == $name)
            {
                return (string) $attrs['_id'];
            }
        }
        else
        {
            foreach($attrs as $attr)
            {
                // Check this item
                $result = $this->_getMongoIdForAttr($attr, $name, true);

                if ($result !== false)
                {
                    return $result;
                }

                // Do we have children? Check those too.
                if (sizeof($attr['children']))
                {
                    $result = $this->_getMongoIdForAttr($attr['children'], $name, true);
                }

                if ($result !== false)
                {
                    return $result;
                }
            }
        }

        // Failed to find anything at all?
        // Recursive calls get to go back with a false, but non-recursive means we throw an exception.
        if ($recursed)
        {
            return false;
        }
        else
        {
            throw new Exception("unable to find attr with name [$name]");
        }
    }

}