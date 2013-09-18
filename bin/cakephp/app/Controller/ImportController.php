<?php

//App::uses('CakeLogInterface', 'Log');

class ImportController extends AppController
{
    public $uses = array('Client', 'Log', 'User');

    private $mysqli = null;
    private $arrayLog = array();

    private $logTotal = 0;

    private function _log($msg) {
        $this->arrayLog[] = $msg;
    }

    function runImport() {
        $startTime = microtime(true);
        $this->_log('-starting import script at ' . date('Y-m-d H:s'));

        //clean mongodb
        if($this->Client->deleteAll(true)
            && $this->Log->deleteAll(true)
            && $this->User->deleteAll(true)
        ) {
            $this->_log('-mongodb cleaned');
        } else {
            $this->_log('--FAILED - to clean mongo');
            $this->set('import', 'incomplete');
            $this->set('log', $this->arrayLog);
            $this->set('_serialize', array('import', 'log'));
            return;
        }

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
                'shortname' => 'aus',
                'contact' => '',
                'logprefix' => 'AUS',
                'activationDate' => new DateTime('now'),
                'format' => $baseFormats,
                'attributes' => array(),
            ),
            array(
                'database' => 'mediahub',
                'database_prefix' => 'mediahub',
                'name' => 'mediahub',
                'shortname' => '',
                'contact' => '',
                'logprefix' => 'MH',
                'activationDate' => new DateTime('now'),
                'format' => $baseFormats,
                'attributes' => array(),
            ),
            array(
                'database' => 'mediahub',
                'database_prefix' => 'sbs',
                'name' => 'sbs',
                'shortname' => '',
                'contact' => '',
                'logprefix' => 'SBS',
                'activationDate' => new DateTime('now'),
                'format' => $baseFormats,
                'attributes' => array(),
            ),
            array(
                'database' => 'mediahub',
                'database_prefix' => 'tvn',
                'name' => 'TVN',
                'shortname' => 'tvn',
                'contact' => '',
                'logprefix' => 'TVN',
                'activationDate' => new DateTime('now'),
                'format' => $baseFormats,
                'attributes' => array(),
            ),
            array(
                'database' => 'mediahub',
                'database_prefix' => 'win',
                'name' => 'WIN',
                'shortname' => 'win',
                'contact' => '',
                'logprefix' => 'WIN',
                'activationDate' => new DateTime('now'),
                'format' => $baseFormats,
                'attributes' => array(),
            ),
            array(
                'database' => 'preslog',
                'database_prefix' => 'pres',
                'name' => 'ABC',
                'shortname' => 'abc',
                'contact' => '',
                'logprefix' => 'abc',
                'activationDate' => new DateTime('now'),
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
                'type' => 'text',
                'name' => 'Program',
                'data' => array('placeholder' => '?'),
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'text',
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
                'type' => 'text',
                'name' => 'What happened?', //description
                'data' => array('placeholder' => '?'),
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'text',
                'name' => 'Why it happened', //details
                'data' => array('seconds' => '?'),
            ),
            array(
                '_id' => null,
                'order' => $order++,
                'type' => 'text',
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
                'type' => 'text',
                'name' => 'Cause',
                'data' => array(
                    'placeholder' => '?',
                ),
            ),
        );

        return $baseFormats;
    }

    private function _createClient($client) {
        $onairImpactId = -1;
        $severityId = -1;
        //loop through format and set new mongo_ids
        for($i =0; $i < sizeof($client['format']); $i++) {
            $client['format'][$i]['_id'] = new MongoId();
            if($client['format'][$i]['name'] == 'On-Air Impact') $onairImpactId = $i;
            if($client['format'][$i]['name'] == 'Severity') $severityId = $i;
        }

        //set options for select in format
//        $sql = 'SELECT id, name, `order`, deleted FROM ' . $client['database_prefix'] . '_onairimpact ORDER BY `order`';
//        if ($result = $this->mysqli->query($sql)) {
//            $options = array();
//            while ($row = $result->fetch_assoc()) {
//                $options[] = array(
//                    '_id' =>  new MongoId(),
//                    'name' => $row['name'],
//                    'order' => $row['order'],
//                    'deleted' => $row['deleted'],
//                    'old_id' => $row['id'],
//                );
//            }
//            $client['format'][$onairImpactId]['data']['options'] = $options;
//        }
        $sql = 'SELECT distinct impact FROM ' . $client['database_prefix'] . '_dailylog';
        if ($result = $this->mysqli->query($sql)) {
            $options = array();
            $i = 0;
            while ($row = $result->fetch_assoc()) {
                $options[] = array(
                    '_id' =>  new MongoId(),
                    'name' => mb_convert_encoding($row['impact'], 'utf8'),
                    'order' => $i++,
                    'deleted' => '0',
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
                    'deleted' => $row['deleted'],
                    'old_id' => $row['id'],
                );
            }
            $client['format'][$severityId]['data']['options'] = $options;
        }

        $attrs = array();

        $networks = array(
            '_id' => new MongoId(),
            'name' => 'Networks',
            'deleted' => '0',
            'children' => array(),
        );
        $networkCount = 0;
        $channelCount = 0;
        $sql = 'SELECT network, displayOrder, id, channel_id, deleted, short_code FROM ' . $client['database_prefix'] . '_networks ORDER BY network, displayorder';
        if ($result = $this->mysqli->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                if (!isset($networks['children'][$row['network']])) {
                    $networks['children'][$row['network']] = array(
                        '_id' => new MongoId(),
                        'name' => mb_convert_encoding($row['network'], 'utf8'),
                        'deleted' => $row['deleted'],
                        'children' => array(),
                    );
                    $networkCount++;
                }

                $channelSql = 'SELECT name, deleted FROM ' . $client['database_prefix'] . '_channels WHERE id = ' . $row['channel_id'] . ' ORDER BY displayorder';
                if($cResult = $this->mysqli->query($channelSql)) {
                    while($cRow = $cResult->fetch_assoc()) {
                        $networks['children'][$row['network']]['children'][] = array(
                            '_id' => new MongoId(),
                            'name' => mb_convert_encoding($cRow['name'], 'utf8'),
                            'deleted' => $cRow['deleted'],
                        );
                        $channelCount++;
                    }
                }
            }
        }
        $useStates = array('VIC', 'SA', 'WA', 'NT', 'QLD', 'NSW', 'ACT' ,'TAS');
        $states = array(
            '_id' => new MongoId(),
            'name' => 'States',
            'deleted' => '0',
            'children' => array(),
        );
        foreach ($useStates as $state) {
            $states['children'][] = array(
                '_id' => new MongoId(),
                'name' => $state,
                'deleted' => '0',
                'children' => array(),
            );
        }
        $attrs[] = $networks;
        $attrs[] = $states;

        $client['attributes'] = $attrs;
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

        $csvFile = TMP . 'users.csv';

        $file = fopen($csvFile, 'r');

        $users = array();
        while (($line = fgets($file)) !== false) {
            $parts = explode(',', $line);

            if ($parts[0] == 'email') //ignore column headers
                continue;
            if (sizeof($parts) < 7)
                continue;

            $user =  array(
                '_id' => new MongoId(),
                'email' => trim($parts[0]),
                'firstName' => trim($parts[1]),
                'lastName' => trim($parts[2]),
                'company' => trim($parts[3]),
                'phoneNumber' => trim($parts[4]),
                'role' => trim($parts[6]),
            );

            $user['deleted'] = trim($parts[5]);

            if (isset($parts[7])) { //TODO convert name to mongo id
                $user['clientid'] = trim($parts[7]);
            }

            $alias = array();
            if(sizeof($parts) > 8) {
                for($i = 8; $i < sizeof($parts); $i++) {
                    $a = preg_replace('/"/', '', trim($parts[$i]));
                    $alias[] = $a;
                }
                $user['alias'] = $alias;
            }

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
        $sql = 'SELECT lognum as hrid, logdate, logtime, duration, cause, impact, description, details, loggedby as user_id,
         program, enteredtimestamp, action, severity, impairment, created, modified, version, vic, sa, wa, nt, qld, nsw,
         act, tas, spread, assetid
         FROM ' . $client['database_prefix'] . '_dailylog ORDER BY created ASC';

        $logCount = 0;

        if($result = $this->mysqli->query($sql)) {
            $hrid = 1;
            while ($row = $result->fetch_assoc()) {

                //get any extra data we need
                $datetime = new DateTime();
                $datetime->setTimestamp(strtotime($row['logdate'] . ' ' . $row['logtime']));

                $fields = array(
                    array(
                        'field_id' => $this->_getMongoIDFromFormatByName($client['format'], 'Duration'),
                        'data' => array(
                            'seconds' => $row['duration'],
                        ),
                    ),
                    array(
                        'field_id' => $this->_getMongoIDFromFormatByName($client['format'], 'datetime'),
                        'data' => array(
                            'datetime' => $datetime,
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
                            'selected' => mb_convert_encoding($row['action'], 'utf-8'),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['format'], 'Severity'),
                        'data' => array(
                            'text' => $this->_getMongoIdForOptionInSelect($client['format'], 'Severity', $row['severity']),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['format'], 'Entered Time'),
                        'data' => array(
                            'datetime' => new DateTime(strtotime($row['enteredtimestamp'])),
                        ),
                    ),
                    array(
                        'field_id' =>  $this->_getMongoIDFromFormatByName($client['format'], 'Cause'),
                        'data' => array(
                            'text' => mb_convert_encoding($row['enteredtimestamp'], 'utf-8'),
                        ),
                    ),
                );


                $attr = array();
//                if($row['vic'] != null) $attr[] = $this->_getMongoIdForAttr($client['attributes'], 'VIC');
//                if($row['sa'] != null) $attr[] = $this->_getMongoIdForAttr($client['attributes'], 'SA');
//                if($row['wa'] != null) $attr[] = $this->_getMongoIdForAttr($client['attributes'], 'WA');
//                if($row['nt'] != null) $attr[] = $this->_getMongoIdForAttr($client['attributes'], 'NT');
//                if($row['qld'] != null) $attr[] = $this->_getMongoIdForAttr($client['attributes'], 'QLD');
//                if($row['nsw'] != null) $attr[] = $this->_getMongoIdForAttr($client['attributes'], 'NSW');
//                if($row['act'] != null) $attr[] = $this->_getMongoIdForAttr($client['attributes'], 'ACT');
//                if($row['tas'] != null) $attr[] = $this->_getMongoIdForAttr($client['attributes'], 'TAS');

                //general log object
                $log = array(
                    '_id' => new MongoId(),
                    'hrid' => $row['hrid'],
                    'created_user_id' => 'TODO!!!!!!',
                    'modified_user_id' => null,
                    'deleted' => '0',
                    'fields' => $fields,
                    'attributes' => $attr,
                    'version' => (int)$row['version'],
                    'client_id' => $client['_id'],
                    'created' => $row['created'],
                    'modified' => $row['modified'],
                );

                $this->Log->create($log);
                $this->Log->save();
                $logCount++;
            }

        }

        $this->logTotal += $logCount;
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