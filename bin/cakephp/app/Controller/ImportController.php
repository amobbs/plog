<?php

//App::uses('CakeLogInterface', 'Log');

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
    }

    /**
     * Perform the import process
     */
    function runImport() {

        set_time_limit(0);

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
        $this->set('import', 'success. run time = ' . $totalTime . 's');
        $this->set('log', $this->arrayLog);
        $this->set('_serialize', array('import', 'log'));

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
                'name' => 'datetime',
                'label' => 'Date',
                'data' => null,
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'textbig',
                'name' => 'program',
                'label' => 'Program Name',
                'data' => array('placeholder' => '?'),
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'textsmall',
                'name' => 'asset_id',
                'label' => 'Asset ID',
                'data' => array('placeholder' => '?'),
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'select',
                'name' => 'impact',
                'label' => 'On-Air Impact',
                'data' => array(
                    'placeholder' => '?',
                    'options' => array(
                        '?'
                    )
                ),
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'textarea',
                'name' => 'what', //description
                'label' => 'What happened?',
                'data' => array('placeholder' => '?'),
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'textarea',
                'name' => 'why', //details
                'label' => 'Why it happened?',
                'data' => array('seconds' => '?'),
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'textarea',
                'name' => 'action_taken', //cause?
                'label' => 'What action taken?',
                'data' => array('placeholder' => '?'),
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'select',
                'name' => 'severity',
                'label' => 'Severity',
                'data' => array(
                    'placeholder' => '?',
                    'options' => array(
                        '?',
                    ),
                ),
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'duration',
                'name' => 'duration',
                'label' => 'Duration',
                'data' => null,
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'datetime',
                'name' => 'start_time',
                'label' => 'Start Time',
                'data' => null,
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'testsmall',
                'name' => 'cause',
                'label' => 'Cause',
                'data' => array(
                    'placeholder' => '?',
                ),
            ),
            array(

                '_id' => null,
                'order' => $order++,
                'type' => 'select',
                'name' => 'accountability',
                'label' => 'Accountability',
                'data' => array(
                    'placeholder' => '?',
                    'options' => array(
                        '?'
                    ),
                ),
            ),
            array(

                '_id' => null,
                'order' => $order++,
                'type' => 'select',
                'name' => 'status',
                'label' => 'status',
                'data' => array(
                    'placeholder' => '?',
                    'options' => array(
                        '?'
                    ),
                ),
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
            if($client['fields'][$i]['name'] == 'impact') $onairImpactId = $i;
            if($client['fields'][$i]['name'] == 'severity') $severityId = $i;
            if($client['fields'][$i]['name'] == 'accountability') $accountabilityId = $i;
            if($client['fields'][$i]['name'] == 'status') $statusId = $i;
        }

        $sql = 'SELECT distinct impact FROM ' . $client['database_prefix'] . '_dailylog';
        if ($result = $this->mysqli->query($sql)) {
            $options = array();
            $i = 0;
            while ($row = $result->fetch_assoc()) {
                $options[] = array(
                    '_id' =>  new MongoId(),
                    'name' => mb_convert_encoding($row['impact'], 'utf8'),
                    'order' => $i++,
                    'deleted' => false,
                    'old_id' => $row['impact'], //used to find attr later
                );
            }
            $client['fields'][$onairImpactId]['data']['options'] = $options;
        }

        $sql = 'SELECT id, severity, level, deleted, `order`, validation FROM ' . $client['database_prefix'] . '_severity ORDER BY `order`';
        if ($result = $this->mysqli->query($sql)) {
            $options = array();
            while ($row = $result->fetch_assoc()) {
                $options[] = array(
                    '_id' =>  new MongoId(),
                    'name' => mb_convert_encoding($row['severity'], 'utf8'),
                    'order' => $row['order'],
                    'deleted' => (bool)$row['deleted'],
                    'old_id' => $row['id'],
                );
            }
            $client['fields'][$severityId]['data']['options'] = $options;
        }

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


        $attrs = array();

        $networks = array(
            '_id' => new MongoId(),
            'name' => 'Networks',
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
                'name' => 'States',
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
                'name' => 'City / State',
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
                'password' => Security::hash('nopassword', 'blowfish', false),
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
            $user['deleted'] = ($parts[5] == 'TRUE');

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
        $sql = 'SELECT lognum as hrid, logdate, logtime, duration, cause, impact, description, details, loggedby,
         program, enteredtimestamp, action, severity, impairment, created, modified, version, vic, sa, wa, nt, qld, nsw,
         act, tas, spread, assetid, accountability, status
         FROM ' . $client['database_prefix'] . '_dailylog ORDER BY created ASC';

        $logCount = 0;

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
                        'field_id' => $this->_getMongoIDFromFormatByName($client['fields'], 'duration'),
                        'data' => array(
                            'seconds' => intval($row['duration']),
                        ),
                    ),
                    array(
                        'field_id' => $this->_getMongoIDFromFormatByName($client['fields'], 'datetime'),
                        'data' => array(
                            'datetime' => new MongoDate(strtotime($row['logdate'] . ' ' . $row['logtime'] .' '. $this->timezone)),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['fields'], 'program'),
                        'data' => array(
                            'text' => mb_convert_encoding($row['program'], 'utf-8'),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['fields'], 'asset_id'),
                        'data' => array(
                            'text' => mb_convert_encoding($row['assetid'], 'utf-8'),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['fields'], 'impact'),
                        'data' => array(
                            'selected' => $this->_getMongoIdForOptionInSelect($client['fields'], 'impact', $row['impact']),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['fields'], 'what'),
                        'data' => array(
                            'text' => mb_convert_encoding($row['description'], 'utf-8'),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['fields'], 'why'),
                        'data' => array(
                            'text' => mb_convert_encoding($row['details'], 'utf-8'),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['fields'], 'action_taken'),
                        'data' => array(
                            'text' => mb_convert_encoding($row['action'], 'utf-8'),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['fields'], 'severity'),
                        'data' => array(
                            'selected' => $this->_getMongoIdForOptionInSelect($client['fields'], 'severity', $row['severity']),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['fields'], 'start_time'),
                        'data' => array(
                            'datetime' => new MongoDate(strtotime($row['enteredtimestamp'] .' '. $this->timezone)),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['fields'], 'cause'),
                        'data' => array(
                            'text' => mb_convert_encoding($row['cause'], 'utf-8'),
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
                    'fields' => $fields,
                    'attributes' => $attr,
                    'client_id' => $client['_id'],
                );


                $this->_log("saving log - count: " . $this->logTotal . ' id: ' . $row['hrid'] .  ' for: ' . $client['name']);
                $this->Log->save(array('Log'=>$log), array('callbacks'=>false, 'validate'=>false));
                $logCount++;
                $this->logTotal++;
            }

        }

        $this->_log("$logCount logs added for " . $client['name']);
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
                return $attrs['_id'];
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