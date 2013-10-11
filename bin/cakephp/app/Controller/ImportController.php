<?php

//App::uses('CakeLogInterface', 'Log');

class ImportController extends AppController
{
    public $uses = array('Client', 'Log', 'User');

    private $mysqli = null;
    private $arrayLog = array();

    private $logTotal = 0;

    private function _log($msg) {
        CakeLog::write('activity', $msg);
        $this->arrayLog[] = $msg;
    }

    function runImport() {

        set_time_limit(0);

        $startTime = microtime(true);
        $this->_log('-starting import script at ' . date('Y-m-d H:s'));

        // clear mongos schemas
        $this->Client->mongoSchema  = array();
        $this->Log->mongoSchema     = array();
        $this->User->mongoSchema    = array();



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

    private function _getDbConn($dbName) {
        $this->mysqli = new mysqli("127.0.0.1", "root", "", $dbName);
        if ($this->mysqli->connect_errno) {
            throw new Exception("Failed to connect to MySQL: (" . $this->mysqli->connect_errno . ") " . $this->mysqli->connect_error);
        }
    }

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
                'format' => $baseFormats,
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
                'format' => $baseFormats,
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
                'format' => $baseFormats,
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
                'format' => $baseFormats,
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
                'format' => $baseFormats,
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
                'format' => $baseFormats,
                'attributes' => array(),
            ),
        );

        return $clients;
    }

    private function _getBaseFormats() {
        $order = 0;
        $baseFormats = array(
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'loginfo',
                'name' => 'loginfo',
                'data' => null,
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'datetime',
                'name' => 'datetime',
                'data' => null,
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'textarea',
                'name' => 'Program',
                'data' => array('placeholder' => '?'),
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'textarea',
                'name' => 'Asset ID',
                'data' => array('placeholder' => '?'),
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'select',
                'name' => 'On-Air Impact',
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
                'name' => 'What happened?', //description
                'data' => array('placeholder' => '?'),
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'textarea',
                'name' => 'Why it happened', //details
                'data' => array('seconds' => '?'),
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'textarea',
                'name' => 'What action taken', //cause?
                'data' => array('placeholder' => '?'),
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'select',
                'name' => 'Severity',
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
                'name' => 'Duration',
                'data' => null,
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'datetime',
                'name' => 'Entered Time',
                'data' => null,
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'textarea',
                'name' => 'Cause',
                'data' => array(
                    'placeholder' => '?',
                ),
            ),
            array(

                '_id' => null,
                'order' => $order++,
                'type' => 'select',
                'name' => 'Accountability',
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
                'name' => 'Status',
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

    private function _createClient($client) {
        $onairImpactId = -1;
        $severityId = -1;
        $accountabilityId = -1;
        $statusId = -1;
        //loop through format and set new mongo_ids
        for($i =0; $i < sizeof($client['format']); $i++) {
            $client['format'][$i]['_id'] = new MongoId();
            if($client['format'][$i]['name'] == 'On-Air Impact') $onairImpactId = $i;
            if($client['format'][$i]['name'] == 'Severity') $severityId = $i;
            if($client['format'][$i]['name'] == 'Accountability') $accountabilityId = $i;
            if($client['format'][$i]['name'] == 'Status') $statusId = $i;
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
            $client['format'][$onairImpactId]['data']['options'] = $options;
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
            $client['format'][$severityId]['data']['options'] = $options;
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
            $client['format'][$accountabilityId]['data']['options'] = $options;
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
            $client['format'][$statusId]['data']['options'] = $options;
        }


        $attrs = array();

        $networks = array(
            '_id' => new MongoId(),
            'name' => 'Networks',
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
                'deleted' => false,
                'children' => array(),
            );
            foreach ($useStates as $state) {
                $states['children'][] = array(
                    '_id' => new MongoId(),
                    'name' => $state,
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
                'deleted' => false,
                'children' => array(
                    array(
                        '_id' => new MongoId(),
                        'name' => 'City',
                        'deleted' => false,
                        'children' => array(),
                    ),
                    array(
                        '_id' => new MongoId(),
                        'name' => 'State',
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
        foreach($client['format'] as $formatKey => $formatValue) {
            foreach($formatValue as $key => $val) {
                if (substr($key, 0, 4) == 'old_')
                    unset($client['format'][$formatKey][$key]);
            }
        }

        $this->_log('adding client ' . $client['name'] . " with $networkCount networks containing $channelCount channels");
        $this->Client->create($client);
        $this->Client->save();

        return $returnClient;
    }

    private function _createUsers($clients) {
        $this->_log('-importing users');

        $csvFile = TMP . 'users_20130930.csv';

        $file = fopen($csvFile, 'r');

        $users = array();
        while (($line = fgets($file)) !== false) {
            $parts = explode(',', $line);

            if ($parts[0] == 'email' || empty($parts[0])) //ignore column headers and blank lines
                continue;
            if (sizeof($parts) < 7)
                continue;

            $user =  array(
                '_id' => new MongoId(),
                'password' => 'nopassword',
                'email' => mb_convert_encoding(trim($parts[0]), 'utf8'),
                'firstName' => mb_convert_encoding(trim($parts[1]), 'utf8'),
                'lastName' => mb_convert_encoding(trim($parts[2]), 'utf8'),
                'company' => mb_convert_encoding(trim($parts[3]), 'utf8'),
                'phoneNumber' => mb_convert_encoding(trim($parts[4]), 'utf8'),
                'role' => strtolower(mb_convert_encoding(trim($parts[6]), 'utf8')),
                'dashboards' => array(),
                'notifications' => array(
                    'types' => array(
                        'sms' => false,
                        'email' => false,
                    ),
                ),
                'clients' => array(),
            );

            //all users are active according to data sent from mediahub
            $user['deleted'] = false;

            if (isset($parts[7])) { //TODO convert name to mongo id
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

            //TODO ignoring alias as not provided by media hub
//            $alias = array();
//            if(sizeof($parts) > 8) {
//                for($i = 8; $i < sizeof($parts); $i++) {
//                    $a = preg_replace('/"/', '', trim($parts[$i]));
//                    $alias[] = $a;
//                }
//                $user['alias'] = $alias;
//            }

            $this->User->create($user);
            $this->User->save();

            $users[] = $user;
        }
        fclose($file);

        $this->_log(sizeof($users) . ' users');

        return $users;
    }

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

    private function _createLogs($client, $users) {
        $this->_log('creating logs for ' . $client['name']);
        $sql = 'SELECT lognum as hrid, logdate, logtime, duration, cause, impact, description, details, loggedby,
         program, enteredtimestamp, action, severity, impairment, created, modified, version, vic, sa, wa, nt, qld, nsw,
         act, tas, spread, assetid, accountability, status
         FROM ' . $client['database_prefix'] . '_dailylog ORDER BY created ASC';

        $logCount = 0;

        if($result = $this->mysqli->query($sql)) {
            while ($row = $result->fetch_assoc()) {

                $modifiedBy = '';
                if ((int)$row['version'] == 1) {
                    $modifiedBy = $row['loggedby'];
                }

                $fields = array(
                    array(
                        'field_id' => $this->_getMongoIDFromFormatByName($client['format'], 'loginfo'),
                        'data' => array(
                            'created' => new MongoDate(strtotime($row['created'])),
                            'createdBy' => $row['loggedby'],
                            'modified' => new MongoDate(strtotime($row['modified'])),
                            'modifiedBy' => $modifiedBy,
                            'version' => (int)$row['version']
                        ),
                    ),
                    array(
                        'field_id' => $this->_getMongoIDFromFormatByName($client['format'], 'Duration'),
                        'data' => array(
                            'seconds' => intval($row['duration']),
                        ),
                    ),
                    array(
                        'field_id' => $this->_getMongoIDFromFormatByName($client['format'], 'datetime'),
                        'data' => array(
                            'datetime' => new MongoDate(strtotime($row['logdate'] . ' ' . $row['logtime'])),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['format'], 'Program'),
                        'data' => array(
                            'text' => mb_convert_encoding($row['program'], 'utf-8'),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['format'], 'Asset ID'),
                        'data' => array(
                            'text' => mb_convert_encoding($row['assetid'], 'utf-8'),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['format'], 'On-Air Impact'),
                        'data' => array(
                            'selected' => $this->_getMongoIdForOptionInSelect($client['format'], 'On-Air Impact', $row['impact']),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['format'], 'What happened?'),
                        'data' => array(
                            'text' => mb_convert_encoding($row['description'], 'utf-8'),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['format'], 'Why it happened'),
                        'data' => array(
                            'text' => mb_convert_encoding($row['details'], 'utf-8'),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['format'], 'What action taken'),
                        'data' => array(
                            'text' => mb_convert_encoding($row['action'], 'utf-8'),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['format'], 'Severity'),
                        'data' => array(
                            'selected' => $this->_getMongoIdForOptionInSelect($client['format'], 'Severity', $row['severity']),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['format'], 'Entered Time'),
                        'data' => array(
                            'datetime' => new MongoDate(strtotime($row['enteredtimestamp'])),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['format'], 'Cause'),
                        'data' => array(
                            'text' => mb_convert_encoding($row['cause'], 'utf-8'),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['format'], 'Accountability'),
                        'data' => array(
                            'selected' => $this->_getMongoIdForOptionInSelect($client['format'], 'Accountability', $row['accountability']),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['format'], 'Status'),
                        'data' => array(
                            'selected' => $this->_getMongoIdForOptionInSelect($client['format'], 'Status', $row['status']),
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
                //general log object
                $log = array(
                    '_id' => new MongoId(),
                    'hrid' => $row['hrid'],
                    'created_user_id' => 'TODO!!!!!!',
                    'modified_user_id' => null,
                    'deleted' => false,
                    'fields' => $fields,
                    'attributes' => $attr,
                    'version' => (int)$row['version'],
                    'client_id' => $client['_id'],
                    'created' => new MongoDate(strtotime($row['created'])),
                    'modified' => new MongoDate(strtotime($row['modified'])),
                );


                $this->_log("saving log - count: " . $this->logTotal . ' id: ' . $row['hrid'] .  ' for: ' . $client['name']);
                $this->Log->create($log);
                $this->Log->save();
                $logCount++;
                $this->logTotal++;
            }

        }

        $this->_log("$logCount logs added for " . $client['name']);
    }

    private function _getMongoIDFromFormatByName($formats, $name) {
        foreach($formats as $format) {
            if ($format['name'] == mb_convert_encoding($name, 'utf8')) {
                return $format['_id'];
            }
        }
        throw new Exception("could not find name[$name] in format provided");
    }

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

    private function _getMongoIdForAttr($attrs, $name) {
        $name = mb_convert_encoding($name, 'utf8');
        foreach($attrs as $attr) {
            if(is_array($attr)) {
                foreach($attr['children'] as $child) {
                    if ($child['name'] == $name)
                        return $child['_id'];
                }
            }
            if ($attr['name'] == $name)
                return $attr['_id'];
        }
        throw new Exception("unable to find attr with name [$name]");
    }

}