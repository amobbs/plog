<?php
/**
 * A CakePHP datasource for the mongoDB (http://www.mongodb.org/) document-oriented database.
 *
 * This datasource uses Pecl Mongo (http://php.net/mongo)
 * and is thus dependent on PHP 5.0 and greater.
 *
 * Original implementation by ichikaway(Yasushi Ichikawa) http://github.com/ichikaway/
 *
 * Reference:
 *	Nate Abele's lithium mongoDB datasource (http://li3.rad-dev.org/)
 *	Joél Perras' divan(http://github.com/jperras/divan/)
 *
 * Copyright 2010, Yasushi Ichikawa http://github.com/ichikaway/
 *
 * Contributors: Predominant, Jrbasso, tkyk, AD7six
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2010, Yasushi Ichikawa http://github.com/ichikaway/
 * @package       mongodb
 * @subpackage    mongodb.models.datasources
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */

App::uses('DboSource', 'Model/Datasource');
App::uses('SchemalessBehavior', 'Mongodb.Model/Behavior');
App::uses('SchemaBehavior', 'Mongodb.Model/Behavior');

/**
 * MongoDB Source
 *
 * @package       mongodb
 * @subpackage    mongodb.models.datasources
 */
class MongodbSource extends DboSource {

/**
 * Are we connected to the DataSource?
 *
 * true - yes
 * null - haven't tried yet
 * false - nope, and we can't connect
 *
 * @var boolean
 * @access public
 */
	public $connected = null;

/**
 * Database Instance
 *
 * @var resource
 * @access protected
 */
	protected $_db = null;

/**
 * Mongo Driver Version
 *
 * @var string
 * @access protected
 */
	protected $_driverVersion = Mongo::VERSION;

/**
 * startTime property
 *
 * If debugging is enabled, stores the (micro)time the current query started
 *
 * @var mixed null
 * @access protected
 */
	protected $_startTime = null;

/**
 * Base Config
 * 
 * set_string_id:
 *    true: In read() method, convert MongoId object to string and set it to array 'id'.
 *    false: not convert and set.
 *
 * @var array
 * @access public
 *
 */
	public $_baseConfig = array(
		'set_string_id' => true,
		'persistent' => true,
		'host'       => 'localhost',
		'database'   => '',
		'port'       => '27017',
		'login'		=> '',
		'password'	=> '',
		'replicaset'	=> '',
	);

/**
 * column definition
 *
 * @var array
 */
	public $columns = array(
		'boolean' => array('name' => 'boolean'),
		'string' => array('name'  => 'varchar'),
		'text' => array('name' => 'text'),
		'integer' => array('name' => 'integer', 'format' => null, 'formatter' => 'intval'),
		'float' => array('name' => 'float', 'format' => null, 'formatter' => 'floatval'),
		'datetime' => array('name' => 'datetime', 'format' => null, 'formatter' => 'MongodbDateFormatter'),
		'timestamp' => array('name' => 'timestamp', 'format' => null, 'formatter' => 'MongodbDateFormatter'),
		'time' => array('name' => 'time', 'format' => null, 'formatter' => 'MongodbDateFormatter'),
		'date' => array('name' => 'date', 'format' => null, 'formatter' => 'MongodbDateFormatter'),
	);

/**
 * Default schema for the mongo models
 *
 * @var array
 * @access protected
 */
	protected $_defaultSchema = array(
		'_id' => array('type' => 'string', 'length' => 24, 'key' => 'primary'),
		'created' => array('type' => 'datetime', 'default' => null),
		'modified' => array('type' => 'datetime', 'default' => null)
	);

    protected $_defaultTypeValues = array(
        'string' => null,
        'integer' => null,
        'float' => null,
        'mongoId' => null,
        'mongoDate' => null,
        'datetime' => null,
        'boolean' => false,
        'array' => array(),
        'object' => array(),
        'subDocument' => array(),
        'subCollection' => array()
    );

/**
 * construct method
 *
 * By default don't try to connect until you need to
 *
 * @param array $config Configuration array
 * @param bool $autoConnect false
 * @return void
 * @access public
 */
	function __construct($config = array(), $autoConnect = false) {
		return parent::__construct($config, $autoConnect);
	}

/**
 * Destruct
 *
 * @access public
 */
	public function __destruct() {
		if ($this->connected) {
			$this->disconnect();
		}
	}

/**
 * commit method
 *
 * MongoDB doesn't support transactions
 *
 * @return void
 * @access public
 */
	public function commit() {
		return false;
	}

/**
 * Connect to the database
 *
 * - If using 1.0.2 or above use the mongodb:// DSN format to connect
 * - The connect syntax changed in version 1.0.2 - so check for that too
 * - If authentication information in present then authenticate the connection
 *
 * - ReplicaSet failover may cause the connection to fail. Capture that and retry under those conditions
 * - ReplicaSet failover may cause a connection to a secondary. Prioritise to enforce connections to Primary.
 *   Thanks to https://gist.github.com/norganna/6491184
 *
 * @return boolean Connected
 * @access public
 */
	public function connect() {
		$this->connected = false;

        // Prep
        $this->connection = false;
        $tries = 0;
        $maxRetries = 7;

		try{

            // Configure host string
			$host = $this->createConnectionName($this->config, $this->_driverVersion);

            while (!$this->connection && $tries < $maxRetries)
            {
                try
                {
                    // Using most recent DSN method
                    if (isset($this->config['replicaset']) && count($this->config['replicaset']) === 2) {
                        $this->connection = new Mongo($this->config['replicaset']['host'], $this->config['replicaset']['options']);

                    // Legacy: Connect for =1.2.0
                    } else if ($this->_driverVersion >= '1.2.0') {
                        $this->connection = new Mongo($host, array("persist" => $this->config['persistent']));

                    // Legacy: Connect for <1.2.0
                    } else {
                        $this->connection = new Mongo($host, true, $this->config['persistent']);
                    }

                    // Test for primary if slave not OK
                    if (!isset($this->config['slaveok']) || false == $this->config['slaveok'])
                    {
                        $hasPrimary = false;
                        $conns = $this->connection->getConnections();
                        foreach ($conns as $con)
                        {
                            if ($con['connection']['connection_type_desc'] == 'PRIMARY') {
                                $hasPrimary = true;
                            }
                        }

                        // No primary? Fail the connection.
                        if (!$hasPrimary)
                        {
                            /*
                            // Close all connections, and try again
                            foreach ($conns as $con) {
                                $this->connection->close($con['hash']);
                            }
                            */

                            // Kill the client object
                            $this->connection = false;
                        }
                    }
                }
                catch(MongoConnectionException $e)
                {
                    // Catch "No candidate server"
                }

                // Retry required?
                if ( !$this->connection && $tries > 0 )
                {
                    // Sleep for 1s * retries (0s, 1s, 2s...)
                    usleep( 2000 * $tries );
                }

                // Increment try check
                $tries++;
            }

            // Connection could not be established?
            if (!$this->connection)
            {
                trigger_error("Could not connect to the database after {$tries} tries. Please try again.", E_USER_ERROR);
            }

            // Set slave OK
			if (isset($this->config['slaveok'])) {
				$this->connection->setSlaveOkay($this->config['slaveok']);
			}

            // Select database
			if ($this->_db = $this->connection->selectDB($this->config['database'])) {

                // Legacy: Auth in version 1.2.0
                if (!empty($this->config['login']) && $this->_driverVersion < '1.2.0') {
					$return = $this->_db->authenticate($this->config['login'], $this->config['password']);

                    // Auth Error?
                    if (!$return || !$return['ok']) {
						trigger_error('MongodbSource::connect ' . $return['errmsg']);
						return false;
					}
				}

                // Connected
				$this->connected = true;
			}

        // Catch errors
		} catch(MongoException $e) {
			$this->error = $e->getMessage();
			trigger_error($this->error);
		}

        // Return success status
		return $this->connected;
	}

/**
 * create connection name.
 *
 * @param array $config
 * @param string $version  version of MongoDriver
 */
		public function createConnectionName($config, $version) {
			$host = null;

			if ($version >= '1.0.2') {
				$host = "mongodb://";
			} else {
				$host = '';
			}
			$hostname = $config['host'] . ':' . $config['port'];

			if(!empty($config['login'])){
				$host .= $config['login'] .':'. $config['password'] . '@' . $hostname . '/'. $config['database'];
			} else {
				$host .= $hostname;
			}

			return $host;
		}


/**
 * Inserts multiple values into a table
 *
 * @param string $table
 * @param string $fields
 * @param array $values
 * @access public
 */
	public function insertMulti($table, $fields, $values) {
		$table = $this->fullTableName($table);

		if (!is_array($fields) || !is_array($values)) {
			return false;
		}
		$data = array();
		foreach($values as $row) {
			if (is_string($row)) {
				$row = explode(', ', substr($row, 1, -1));
			}
			$data[] = array_combine($fields, $row);
		}
		$this->_prepareLogQuery($table); // just sets a timer
		try{
			$return = $this->_db
				->selectCollection($table)
				->batchInsert($data, array('safe' => true));
		} catch (MongoException $e) {
			$this->error = $e->getMessage();
			trigger_error($this->error);
		}
		if ($this->fullDebug) {
			$this->logQuery("db.{$table}.insertMulti( :data , array('safe' => true))", compact('data'));
		}
	}

/**
 * check connection to the database
 *
 * @return boolean Connected
 * @access public
 */
	public function isConnected() {
		if ($this->connected === false) {
			return false;
		}
		return $this->connect();
	}

/**
 * get MongoDB Object
 *
 * @return mixed MongoDB Object
 * @access public
 */
	public function getMongoDb() {
		if ($this->connected === false) {
			return false;
		}
		return $this->_db;
	}

/**
 * get MongoDB Collection Object
 *
 * @return mixed MongoDB Collection Object
 * @access public
 */
	public function getMongoCollection(&$Model) {
		if ($this->connected === false) {
			return false;
		}

		$collection = $this->_db
			->selectCollection($Model->table);
		return $collection;
	}

/**
 * isInterfaceSupported method
 *
 * listSources is infact supported, however: cake expects it to return a complete list of all
 * possible sources in the selected db - the possible list of collections is infinte, so it's
 * faster and simpler to tell cake that the interface is /not/ supported so it assumes that
 * <insert name of your table here> exist
 *
 * @param mixed $interface
 * @return void
 * @access public
 */
	public function isInterfaceSupported($interface) {
		if ($interface === 'listSources') {
			return false;
		}
		return parent::isInterfaceSupported($interface);
	}

/**
 * Close database connection
 *
 * @return boolean Connected
 * @access public
 */
	public function close() {
		return $this->disconnect();
	}

/**
 * Disconnect from the database
 *
 * @return boolean Connected
 * @access public
 */
	public function disconnect() {
		if ($this->connected) {
			$this->connected = !$this->connection->close();
			unset($this->_db, $this->connection);
			return !$this->connected;
		}
		return true;
	}

/**
 * Get list of available Collections
 *
 * @param array $data
 * @return array Collections
 * @access public
 */
	public function listSources($data = null) {
		if (!$this->isConnected()) {
			return false;
		}
		return true;	
	}

/**
 * Describe
 *
 * Automatically bind the schemaless behavior if there is no explicit mongo schema.
 * When called, if there is model data it will be used to derive a schema. a row is plucked
 * out of the db and the data obtained used to derive the schema.
 *
 * @param Model $Model
 * @return array if model instance has mongoSchema, return it.
 * @access public
 */
	public function describe(&$Model, $field = null) {


        // Is the schema supplied on the model?
		if (!empty($Model->mongoSchema) && is_array($Model->mongoSchema)) {

            // $Model->primaryKey will be loaded from the Schema on Behaviour::setup().
            $Model->Behaviors->load('Mongodb.Schema');

            // Pass schema
            $schema = $Model->mongoSchema;
			return $schema;

        // Schema is not supplied. Infer it via Schemaless behaviour
		} elseif ($this->isConnected() && is_a($Model, 'Model') && !empty($Model->Behaviors)) {
			$Model->Behaviors->load('Mongodb.Schemaless');

            // Primary key is always _id on schemaless
            $Model->primaryKey = '_id';

            // Derive the data if the data exists
            if (!$Model->data) {
				if ($this->_db->selectCollection($Model->table)->count()) {
					return $this->deriveSchemaFromData($Model, $this->_db->selectCollection($Model->table)->findOne());
				}
			}
		}

        // Attempt to derive the schema without the database
		return $this->deriveSchemaFromData($Model);
	}

/**
 * begin method
 *
 * Mongo doesn't support transactions
 *
 * @return void
 * @access public
 */
	public function begin() {
		return false;
	}

/**
 * Calculate
 *
 * @param Model $Model
 * @return array
 * @access public
 */
	public function calculate(&$Model) {
		return array('count' => true);
	}

/**
 * Quotes identifiers.
 *
 * MongoDb does not need identifiers quoted, so this method simply returns the identifier.
 *
 * @param string $name The identifier to quote.
 * @return string The quoted identifier.
 */
	public function name($name) {
		return $name;
	}

/**
 * Create Data
 *
 * @param Model $Model Model Instance
 * @param array $fields Field data
 * @param array $values Save data
 * @return boolean Insert result
 * @access public
 */
	public function create(&$Model, $fields = null, $values = null) {
		if (!$this->isConnected()) {
			return false;
		}

		if ($fields !== null && $values !== null) {
			$data = array_combine($fields, $values);
		} else {
			$data = $Model->data;
		}

        $this->convertToDocument($data, $Model->mongoSchema, $fields);
        $this->prune($data, $Model->mongoSchema);

		if (!empty($data['_id'])) {
			$this->_convertId($data['_id']);
		}

		$this->_prepareLogQuery($Model); // just sets a timer
		try{
			$return = $this->_db
				->selectCollection($Model->table)
				->insert($data);
		} catch (MongoException $e) {
			$this->error = $e->getMessage();
			trigger_error($this->error);
		}
		if ($this->fullDebug) {
			$this->logQuery("db.{$Model->useTable}.insert( :data , true)", compact('data'));
		}

        // If we saved data ..
		if (!empty($return) && $return === true) {

			$id = $data['_id'];
			if($this->config['set_string_id'] && is_object($data['_id'])) {
				$id = $data['_id']->__toString();
			}
			$Model->setInsertID($id);
			$Model->id = $id;
			return true;
		}
		return false;
	}

/**
 * createSchema method
 *
 * Mongo no care for creating schema. Mongo work with no schema.
 *
 * @param mixed $schema
 * @param mixed $tableName null
 * @return void
 * @access public
 */
	public function createSchema($schema, $tableName = null) {
		return true;
	}

/**
 * dropSchema method
 *
 * Return a command to drop each table
 *
 * @param mixed $schema
 * @param mixed $tableName null
 * @return void
 * @access public
 */
	public function dropSchema($schema, $tableName = null) {
		if (!$this->isConnected()) {
			return false;
		}

		if (!is_a($schema, 'CakeSchema')) {
			trigger_error(__('Invalid schema object', true), E_USER_WARNING);
			return null;
		}
		if ($tableName) {
			return "db.{$tableName}.drop();";
		}

		$toDrop = array();
		foreach ($schema->tables as $curTable => $columns) {
			if ($tableName === $curTable) {
				$toDrop[] = $curTable;
			}
		}

		if (count($toDrop) === 1) {
			return "db.{$toDrop[0]}.drop();";
		}

		$return = "toDrop = :tables;\nfor( i = 0; i < toDrop.length; i++ ) {\n\tdb[toDrop[i]].drop();\n}";
		$tables = '["' . implode($toDrop, '", "') . '"]';

		return String::insert($return, compact('tables'));
	}

/**
 * distinct method
 *
 * @param mixed $Model
 * @param array $keys array()
 * @param array $params array()
 * @return void
 * @access public
 */
	public function distinct(&$Model, $keys = array(), $params = array()) {
		if (!$this->isConnected()) {
			return false;
		}

		$this->_prepareLogQuery($Model); // just sets a timer

		if (array_key_exists('conditions', $params)) {
			$params = $params['conditions'];
		}
		try{
			$return = $this->_db
				->selectCollection($Model->table)
				->distinct($keys, $params);
		} catch (MongoException $e) {
			$this->error = $e->getMessage();
			trigger_error($this->error);
		}
		if ($this->fullDebug) {
			$this->logQuery("db.{$Model->useTable}.distinct( :keys, :params )", compact('keys', 'params'));
		}

		return $return;
	}


/**
 * group method
 *
 * @param mixed $Model
 * @param array $params array()
 *   Set params  same as MongoCollection::group()
 *    key,initial, reduce, options(conditions, finalize)
 *
 *   Ex. $params = array(
 *           'key' => array('field' => true),
 *           'initial' => array('csum' => 0),
 *           'reduce' => 'function(obj, prev){prev.csum += 1;}',
 *           'options' => array(
 *                'condition' => array('age' => array('$gt' => 20)),
 *                'finalize' => array(),
 *           ),
 *       );
 * @return void
 * @access public
 */
	public function group(&$Model, $params = array()) {

		if (!$this->isConnected() || count($params) === 0 ) {
			return false;
		}

		$this->_prepareLogQuery($Model); // just sets a timer

		$key = (empty($params['key'])) ? array() : $params['key'];
		$initial = (empty($params['initial'])) ? array() : $params['initial'];
		$reduce = (empty($params['reduce'])) ? array() : $params['reduce'];
		$options = (empty($params['options'])) ? array() : $params['options'];

		try{
			$return = $this->_db
				->selectCollection($Model->table)
				->group($key, $initial, $reduce, $options);
		} catch (MongoException $e) {
			$this->error = $e->getMessage();
			trigger_error($this->error);
		}
		if ($this->fullDebug) {
			$this->logQuery("db.{$Model->useTable}.group( :key, :initial, :reduce, :options )", $params);
		}


		return $return;
	}


/**
 * ensureIndex method
 *
 * @param mixed $Model
 * @param array $keys array()
 * @param array $params array()
 * @return void
 * @access public
 */
	public function ensureIndex(&$Model, $keys = array(), $params = array()) {
		if (!$this->isConnected()) {
			return false;
		}

		$this->_prepareLogQuery($Model); // just sets a timer

		try{
			$return = $this->_db
				->selectCollection($Model->table)
				->ensureIndex($keys, $params);
		} catch (MongoException $e) {
			$this->error = $e->getMessage();
			trigger_error($this->error);
		}
		if ($this->fullDebug) {
			$this->logQuery("db.{$Model->useTable}.ensureIndex( :keys, :params )", compact('keys', 'params'));
		}

		return $return;
	}

/**
 * Update Data
 *
 * This method uses $set operator automatically with MongoCollection::update().
 * If you don't want to use $set operator, you can chose any one as follw.
 *  1. Set TRUE in Model::mongoNoSetOperator property.
 *  2. Set a mongodb operator in a key of save data as follow.
 *      Model->save(array('_id' => $id, '$inc' => array('count' => 1)));
 *      Don't use Model::mongoSchema property,
 *       CakePHP delete '$inc' data in Model::Save().
 *  3. Set a Mongo operator in Model::mongoNoSetOperator property.
 *      Model->mongoNoSetOperator = '$inc';
 *      Model->save(array('_id' => $id, array('count' => 1)));
 *
 * @param Model $Model Model Instance
 * @param array $fields Field data
 * @param array $values Save data
 * @return boolean Update result
 * @access public
 */
	public function update(&$Model, $fields = null, $values = null, $conditions = null) {

		if (!$this->isConnected()) {
			return false;
		}

		if ($fields !== null && $values !== null) {
			$data = array_combine($fields, $values);
		} elseif($fields !== null && $conditions !== null) {
			return $this->updateAll($Model, $fields, $conditions);
		} else{
			$data = $Model->data;
		}

        $this->convertToDocument($data, $Model->mongoSchema, $fields);
        $this->prune($data, $Model->mongoSchema);

		if (empty($data['_id'])) {
			$data['_id'] = $Model->id;
		}
		$this->_convertId($data['_id']);

		try{
			$mongoCollectionObj = $this->_db
				->selectCollection($Model->table);
		} catch (MongoException $e) {
			$this->error = $e->getMessage();
			trigger_error($this->error);
			return false;
		}

		$this->_prepareLogQuery($Model); // just sets a timer
		if (!empty($data['_id'])) {
			$this->_convertId($data['_id']);
			$cond = array('_id' => $data['_id']);
			unset($data['_id']);

			$data = $this->setMongoUpdateOperator($Model, $data);

			try{
				$return = $mongoCollectionObj->update($cond, $data, array("multiple" => false));
			} catch (MongoException $e) {
				$this->error = $e->getMessage();
				trigger_error($this->error);
			}
			if ($this->fullDebug) {
				$this->logQuery("db.{$Model->useTable}.update( :conditions, :data, :params )",
					array('conditions' => $cond, 'data' => $data, 'params' => array("multiple" => false))
				);
			}
		} else {
			try{
				$return = $mongoCollectionObj->save($data);
			} catch (MongoException $e) {
				$this->error = $e->getMessage();
				trigger_error($this->error);
			}
			if ($this->fullDebug) {
				$this->logQuery("db.{$Model->useTable}.save( :data )", compact('data'));
			}
		}
		return $return;
	}


/**
 * setMongoUpdateOperator
 *
 * Set Mongo update operator following saving data.
 * This method is for update() and updateAll.
 *
 * @param Model $Model Model Instance
 * @param array $values Save data
 * @return array $data
 * @access public
 */
	public function setMongoUpdateOperator(&$Model, $data) {
		if(isset($data['updated'])) {
			$updateField = 'updated';
		} else {
			$updateField = 'modified';			
		}

		//setting Mongo operator
		if(empty($Model->mongoNoSetOperator)) {
			if(!preg_grep('/^\$/', array_keys($data))) {
				$data = array('$set' => $data);
			} else {
				if(!empty($data[$updateField])) {
					$modified = $data[$updateField];
					unset($data[$updateField]);
					$data['$set'] = array($updateField => $modified);
				}
			}
		} elseif(substr($Model->mongoNoSetOperator,0,1) === '$') {
			if(!empty($data[$updateField])) {
				$modified = $data[$updateField];
				unset($data[$updateField]);
				$data = array($Model->mongoNoSetOperator => $data, '$set' => array($updateField => $modified));
			} else {
				$data = array($Model->mongoNoSetOperator => $data);

			}
		}

		return $data;
	}

/**
 * Update multiple Record
 *
 * @param Model $Model Model Instance
 * @param array $fields Field data
 * @param array $conditions
 * @return boolean Update result
 * @access public
 */
	public function updateAll(&$Model, $fields = null,  $conditions = null) {
		if (!$this->isConnected()) {
			return false;
		}

		$this->_stripAlias($conditions, $Model->alias);
		$this->_stripAlias($fields, $Model->alias, false, 'value');

		$fields = $this->setMongoUpdateOperator($Model, $fields);

		$this->_prepareLogQuery($Model); // just sets a timer
		try{
			$return = $this->_db
				->selectCollection($Model->table)
				->update($conditions, $fields, array("multiple" => true));
		} catch (MongoException $e) {
			$this->error = $e->getMessage();
			trigger_error($this->error);
		}

		if ($this->fullDebug) {
			$this->logQuery("db.{$Model->useTable}.update( :conditions, :fields, :params )",
				array('conditions' => $conditions, 'fields' => $fields, 'params' => array("multiple" => true))
			);
		}
		return $return;
	}

/**
 * deriveSchemaFromData method
 *
 * @param mixed $Model
 * @param array $data array()
 * @return void
 * @access public
 */
	public function deriveSchemaFromData($Model, $data = array()) {
		if (!$data) {
			$data = $Model->data;
			if ($data && array_key_exists($Model->alias, $data)) {
				$data = $data[$Model->alias];
			}
		}

		$return = $this->_defaultSchema;

		if ($data) {
			$fields = array_keys($data);
			foreach($fields as $field) {
				if (in_array($field, array('created', 'modified', 'updated'))) {
					$return[$field] = array('type' => 'datetime', 'null' => true);
				} else {
					$return[$field] = array('type' => 'string', 'length' => 2000);
				}
			}
		}

		return $return;
	}

/**
 * Delete Data
 *
 * For deleteAll(true, false) calls - conditions will arrive here as true - account for that and
 * convert to an empty array
 * For deleteAll(array('some conditions')) calls - conditions will arrive here as:
 *  array(
 *  	Alias._id => array(1, 2, 3, ...)
 *  )
 *
 * This format won't be understood by mongodb, it'll find 0 rows. convert to:
 *
 *  array(
 *  	Alias._id => array('$in' => array(1, 2, 3, ...))
 *  )
 *
 * @TODO bench remove() v drop. if it's faster to drop - just drop the collection taking into
 *  	account existing indexes (recreate just the indexes)
 * @param Model $Model Model Instance
 * @param array $conditions
 * @return boolean Update result
 * @access public
 */
	public function delete(&$Model, $conditions = null) {
		if (!$this->isConnected()) {
			return false;
		}

		$id = null;

		$this->_stripAlias($conditions, $Model->alias);

		if ($conditions === true) {
			$conditions = array();
		} elseif (empty($conditions)) {
			$id = $Model->id;
		} elseif (!empty($conditions) && !is_array($conditions)) {
			$id = $conditions;
			$conditions = array();
		} elseif (!empty($conditions['id'])) { //for cakephp2.0
			$id = $conditions['id'];
			unset($conditions['id']);
		}

		$mongoCollectionObj = $this->_db
			->selectCollection($Model->table);

		$this->_stripAlias($conditions, $Model->alias);
		if (!empty($id)) {
			$conditions['_id'] = $id;
		}
		if (!empty($conditions['_id'])) {
			$this->_convertId($conditions['_id'], true);
		}

		$return = false;
		$r = false;
		try{
			$this->_prepareLogQuery($Model); // just sets a timer
			$return = $mongoCollectionObj->remove($conditions);
			if ($this->fullDebug) {
				$this->logQuery("db.{$Model->useTable}.remove( :conditions )",
					compact('conditions')
				);
			}
			$return = true;
		} catch (MongoException $e) {
			$this->error = $e->getMessage();
			trigger_error($this->error);
		}
		return $return;
	}

/**
 * Read Data
 *
 * For deleteAll(true) calls - the conditions will arrive here as true - account for that and switch to an empty array
 *
 * @param Model $Model Model Instance
 * @param array $query Query data
 * @return array Results
 * @access public
 */
	public function read(&$Model, $query = array()) {
		if (!$this->isConnected()) {
			return false;
		}

		$this->_setEmptyValues($query);
		extract($query);

		if (!empty($order[0])) {
			$order = array_shift($order);
		}
		$this->_stripAlias($conditions, $Model->alias);
		$this->_stripAlias($fields, $Model->alias, false, 'value');
		$this->_stripAlias($order, $Model->alias, false, 'both');

		//for cakephp2.0. it doesn't call describe()
		if(!empty($conditions['id']) && empty($conditions['_id'])) {
			$conditions['_id'] = $conditions['id'];
			unset($conditions['id']);
		}

		if (!empty($conditions['_id'])) {
			$this->_convertId($conditions['_id']);
		}

		$fields = (is_array($fields)) ? $fields : array($fields => 1);
		if ($conditions === true) {
			$conditions = array();
		} elseif (!is_array($conditions)) {
			$conditions = array($conditions);
		}
		$order = (is_array($order)) ? $order : array($order);

		if (is_array($order)) {
			foreach($order as $field => &$dir) {
				if (is_numeric($field) || is_null($dir)) {
					unset ($order[$field]);
					continue;
				}
				if ($dir && strtoupper($dir) === 'ASC') {
					$dir = 1;
					continue;
				} elseif (!$dir || strtoupper($dir) === 'DESC') {
					$dir = -1;
					continue;
				}
				$dir = (int)$dir;
			}
		}

		if (empty($offset) && $page && $limit) {
			$offset = ($page - 1) * $limit;
		}

		$return = array();

		$this->_prepareLogQuery($Model); // just sets a timer
		if (empty($modify)) {

            // Count
			if ($Model->findQueryType === 'count' && $fields == array('count' => true)) {

                // Fetch
                $count = $this->_db
					->selectCollection($Model->table)
					->count($conditions);

                // Log
                if ($this->fullDebug) {
					$this->logQuery("db.{$Model->useTable}.count( :conditions )",
						compact('conditions', 'count')
					);
				}

                // return
				return array(array($Model->alias => array('count' => $count)));
			}

            // Find by query
			$return = $this->_db
				->selectCollection($Model->table)
				->find($conditions, $fields)
				->sort($order)
				->limit($limit)
				->skip($offset);

            // Debug
			if ($this->fullDebug) {
				$count = $return->count(true);
				$this->logQuery("db.{$Model->useTable}.find( :conditions, :fields ).sort( :order ).limit( :limit ).skip( :offset )",
					compact('conditions', 'fields', 'order', 'limit', 'offset', 'count')
				);
			}

        // Find and Modify
		} else {

            // Opts
			$options = array_filter(array(
				'findandmodify' => $Model->table,
				'query' => $conditions,
				'sort' => $order,
				'remove' => !empty($remove),
				'update' => $this->setMongoUpdateOperator($Model, $modify),
				'new' => !empty($new),
				'fields' => $fields,
				'upsert' => !empty($upsert)
			));

            // Find and Modify
			$return = $this->_db
				->command($options);

            // Debug
			if ($this->fullDebug) {
				if ($return['ok']) {
					$count = $return->count(true);
				} else {
					$count = 0;
				}

                // Log
				$this->logQuery("db.runCommand( :options )",
					array('options' => array_filter($options), 'count' => $count)
				);
			}
		}

        // Count?
		if ($Model->findQueryType === 'count') {
			return array(array($Model->alias => array('count' => $return->count())));
		}

        // is Object?
        // Increment objects into data blocks
		if (is_object($return)) {
			$_return = array();
			while ($return->hasNext()) {
				$mongodata = $return->getNext();
;
                $this->convertToArray($mongodata, $Model->mongoSchema, $fields);
                $this->prune($mongodata, $Model->mongoSchema);

                // Add to return block
				$_return[][$Model->alias] = $mongodata;
			}

			return $_return;
		}

		return $return;
	}

/**
 * rollback method
 *
 * MongoDB doesn't support transactions
 *
 * @return void
 * @access public
 */
	public function rollback() {
		return false;
	}

/**
 * Deletes all the records in a table
 *
 * @param mixed $table A string or model class representing the table to be truncated
 * @return boolean
 * @access public
 */
	public function truncate($table) {
		if (!$this->isConnected()) {
			return false;
		}

		return $this->execute('db.' . $this->fullTableName($table) . '.remove();');
	}

/**
 * query method
 *  If call getMongoDb() from model, this method call getMongoDb().
 *
 * @param mixed $query
 * @param array $params array()
 * @return void
 * @access public
 */
	public function query($query, $params = array()) {
		if (!$this->isConnected()) {
			return false;
		}

		if($query === 'getMongoDb') {
			return $this->getMongoDb();
		}

		$this->_prepareLogQuery($Model); // just sets a timer
		$return = $this->_db
			->command($query);
		if ($this->fullDebug) {
			$this->logQuery("db.runCommand( :query )", 	compact('query'));
		}

		return $return;
	}

/**
 * mapReduce
 *
 * @param mixed $query
 * @param integer $timeout (milli second)
 * @return mixed false or array 
 * @access public
 */
	public function mapReduce($query, $timeout = null) {

		//above MongoDB1.8, query must object.
		if(isset($query['query']) && !is_object($query['query'])) {
			$query['query'] = (object)$query['query'];
		}

		$result = $this->query($query);

		if($result['ok']) {
			if (isset($query['out']['inline']) && $query['out']['inline'] === 1) {
				if (is_array($result['results'])) {
					$data = $result['results'];
				}else{
					$data = false;
				}
			}else {
				$data = $this->_db->selectCollection($result['result'])->find();
				if(!empty($timeout)) {
					$data->timeout($timeout);
				}
			}
			return $data;
		}
		return false;
	}



/**
 * Prepares a value, or an array of values for database queries by quoting and escaping them.
 *
 * @param mixed $data A value or an array of values to prepare.
 * @param string $column The column into which this data will be inserted
 * @param boolean $read Value to be used in READ or WRITE context
 * @return mixed Prepared value or array of values.
 * @access public
 */
	public function value($data, $column = null, $read = true) {
		$return = parent::value($data, $column, $read);
		if ($return === null && $data !== null) {
			return $data;
		}
		return $return;
	}

/**
 * execute method
 *
 * If there is no query or the query is true, execute has probably been called as part of a
 * db-agnostic process which does not have a mongo equivalent, don't do anything.
 *
 * @param mixed $query
 * @param array $params array()
 * @return void
 * @access public
 */
	public function execute($query, $params = array()) {
		if (!$this->isConnected()) {
			return false;
		}

		if (!$query || $query === true) {
			return;
		}
		$this->_prepareLogQuery($Model); // just sets a timer
		$return = $this->_db
			->execute($query, $params);
		if ($this->fullDebug) {
			if ($params) {
				$this->logQuery(":query, :params",
					compact('query', 'params')
				);
			} else {
				$this->logQuery($query);
			}
		}
		if ($return['ok']) {
			return $return['retval'];
		}
		return $return;
	}

/**
 * Set empty values, arrays or integers, for the variables Mongo uses
 *
 * @param mixed $data
 * @param array $integers array('limit', 'offset')
 * @return void
 * @access protected
 */
	protected function _setEmptyValues(&$data, $integers = array('limit', 'offset')) {
		if (!is_array($data)) {
			return;
		}
		foreach($data as $key => $value) {
			if (empty($value)) {
				if (in_array($key, $integers)) {
					$data[$key] = 0;
				} else {
					$data[$key] = array();
				}
			}
		}
	}

/**
 * prepareLogQuery method
 *
 * Any prep work to log a query
 *
 * @param mixed $Model
 * @return void
 * @access protected
 */
	protected function _prepareLogQuery(&$Model) {
		if (!$this->fullDebug) {
			return false;
		}
		$this->_startTime = microtime(true);
		$this->took = null;
		$this->affected = null;
		$this->error = null;
		$this->numRows = null;
		return true;
	}
	
/**
 * setTimeout Method
 * 
 * Sets the MongoCursor timeout so long queries (like map / reduce) can run at will.
 * Expressed in milliseconds, for an infinite timeout, set to -1
 *
 * @param int $ms 
 * @return boolean
 * @access public
 */
	public function setTimeout($ms){
		MongoCursor::$timeout = $ms;
		
		return true;
	}

/**
 * logQuery method
 *
 * Set timers, errors and refer to the parent
 * If there are arguments passed - inject them into the query
 * Show MongoIds in a copy-and-paste-into-mongo format
 *
 *
 * @param mixed $query
 * @param array $args array()
 * @return void
 * @access public
 */
	public function logQuery($query, $args = array()) {
		if ($args) {
			$this->_stringify($args);
			$query = String::insert($query, $args);
		}
		$this->took = round((microtime(true) - $this->_startTime) * 1000, 0);
		$this->affected = null;
		if (empty($this->error['err'])) {
			$this->error = $this->_db->lastError();
			if (!is_scalar($this->error)) {
				$this->error = json_encode($this->error);
			}
		}
		$this->numRows = !empty($args['count'])?$args['count']:null;

		$query = preg_replace('@"ObjectId\((.*?)\)"@', 'ObjectId ("\1")', $query);
		return parent::logQuery($query);
	}

/**
 * convertId method
 *
 * $conditions is used to determine if it should try to auto correct _id => array() queries
 * it only appies to conditions, hence the param name
 *
 * @param mixed $mixed
 * @param bool $conditions false
 * @return void
 * @access protected
 */
	protected function _convertId(&$mixed, $conditions = false) {
		if (is_string($mixed)) {
			if (strlen($mixed) !== 24) {
				return;
			}
			$mixed = new MongoId($mixed);
		}
		if (is_array($mixed)) {
			foreach($mixed as &$row) {
				$this->_convertId($row, false);
			}
			if (!empty($mixed[0]) && $conditions) {
				$mixed = array('$in' => $mixed);
			}
		}
	}

    //TODO remove this is just here for testing
    public function toString($args) {
        return $this->_stringify($args);
    }

/**
 * stringify method
 *
 * Takes an array of args as an input and returns an array of json-encoded strings. Takes care of
 * any objects the arrays might be holding (MongoID);
 *
 * @param array $args array()
 * @param int $level 0 internal recursion counter
 * @return array
 * @access protected
 */
	protected function _stringify(&$args = array(), $level = 0) {
		foreach($args as &$arg) {
			if (is_array($arg)) {
				$this->_stringify($arg, $level + 1);
			} elseif (is_object($arg) && is_callable(array($arg, '__toString'))) {
				$class = get_class($arg);
				if ($class === 'MongoId') {
					$arg = 'ObjectId(' . $arg->__toString() . ')';
				} elseif ($class === 'MongoRegex') {
					$arg = '_regexstart_' . $arg->__toString() . '_regexend_';
				} else {
					$arg = $class . '(' . $arg->__toString() . ')';
				}
			}
			if ($level === 0) {
				$arg = json_encode($arg);
				if (strpos($arg, '_regexstart_')) {
					preg_match_all('@"_regexstart_(.*?)_regexend_"@', $arg, $matches);
					foreach($matches[0] as $i => $whole) {
						$replace = stripslashes($matches[1][$i]);
						$arg = str_replace($whole, $replace, $arg);
					}
				}
			}
		}
	}

/**
 * Convert automatically array('Model.field' => 'foo') to array('field' => 'foo')
 *
 * This introduces the limitation that you can't have a (nested) field with the same name as the model
 * But it's a small price to pay to be able to use other behaviors/functionality with mongoDB
 *
 * @param array $args array()
 * @param string $alias 'Model'
 * @param bool $recurse true
 * @param string $check 'key', 'value' or 'both'
 * @return void
 * @access protected
 */
	protected function _stripAlias(&$args = array(), $alias = 'Model', $recurse = true, $check = 'key') {
		if (!is_array($args)) {
			return;
		}
		$checkKey = ($check === 'key' || $check === 'both');
		$checkValue = ($check === 'value' || $check === 'both');

		foreach($args as $key => &$val) {
			if ($checkKey) {
				if (strpos($key, $alias . '.') === 0) {
					unset($args[$key]);
					$key = substr($key, strlen($alias) + 1);
					$args[$key] = $val;
				}
			}
			if ($checkValue) {
				if (is_string($val) && strpos($val, $alias . '.') === 0) {
					$val = substr($val, strlen($alias) + 1);
				}
			}
			if ($recurse && is_array($val)) {
				$this->_stripAlias($val, $alias, true, $check);
			}
		}
	}

    public function convertToArray(&$doc, $schema, $fields = array()) {
        $this->convert($doc, $schema, $fields, true);
    }

    public function convertToDocument(&$doc, $schema, $fields = array()) {
        $this->convert($doc, $schema, $fields);
    }
    /**
     * From Database to Model
     */
    public function convert(&$doc, $schema, $fields, $toArray = false, $topLevel = true)
    {

        // Skip non-arrays
        if (! is_array($doc)) {
            return;
        }

        // If an array, recuse each one on the schema type.
        if (! $this->_isAssoc($doc) ) {
            foreach ($doc as &$docItem) {
                $this->convert($docItem, $schema, $fields, $toArray, false);
            }

            return;
        }



        // Convert schema
        foreach ($schema as $fieldKey => $fieldOptions) {
            // Skip schema that doesn't have a type set
            if (empty($fieldOptions['type'])) {
                continue;
            }

            $type = $fieldOptions['type'];

            // If on top level and the field is not in the list of fields passed in, go about your business
            if (! empty($fields) && $topLevel && ! in_array($fieldKey, $fields)) {
                continue;
            }

            // Set default values if field doesn't exist
            if (! isset($doc[$fieldKey])) {
                $doc[$fieldKey] = isset($fieldOptions['default']) ?
                    $fieldOptions['default'] : $this->_defaultTypeValues[$type];
            }

            // Recurse for subCollections
            if ('subCollection' === $type && ! empty($doc[$fieldKey])) {
                // Enforce the data to be a numerical array
                if ($this->_isAssoc($doc[$fieldKey])) {
                    throw new ErrorException('If using subCollection type, the data must be a numerical array');
                }

                $this->convert($doc[$fieldKey], $fieldOptions['schema'], $fields, $toArray, false);
            }

            // Recurse for subDocuments
            elseif ('subDocument' === $type) {
                // Enforce the data to be non-numerical array
                if (! empty($doc[$fieldKey]) && ! $this->_isAssoc($doc[$fieldKey])) {
                    throw new ErrorException('If using subDocument type, the data must be a non-numerical array');
                }

                $this->convert($doc[$fieldKey], $fieldOptions['schema'], $fields, $toArray, false);
            }

            // Recurse for subArrays with a type
            elseif ('subArray' === $type) {

                // Skip empty arrays
                if (!sizeof($doc[$fieldKey]))
                {
                    continue;
                }

                // Enforce the data to be non-numerical array
                if ($this->_isAssoc($doc[$fieldKey])) {
                    throw new ErrorException('If using subArray type, the data must be a numerical array');
                }

                // Convert subitems
                foreach ($doc[$fieldKey] as &$field)
                {
                    $this->convertValue( $field, $fieldOptions['arraySchema'], $toArray);
                }
            }

            // Convert single item
            else
            {
                $this->convertValue( $doc[$fieldKey], $fieldOptions, $toArray);
            }


        }
    }


    public function convertValue( &$value, $fieldOptions, $toArray=false )
    {
        // Fix string/integer lengths
        if (
            ! empty($fieldOptions['length']) &&
            (is_string($value) || is_integer($value))
        ) {
            $value = substr($value, 0, $fieldOptions['length']);
        }

        // Cast the values to specified types
        switch ($fieldOptions['type']) {
            case 'object':
                $value = (object) $value;
                break;
            case 'string':
                $value = (string) $value;
                break;
            case 'array':
                $value = (array) $value;
                break;
            case 'boolean':
                $value = (boolean) $value;
                break;
            case 'integer':
                $value = (integer) $value;
                break;
        }

        // Convert mongoTypes
        if (! empty($fieldOptions['mongoType'])) {
            switch ($fieldOptions['mongoType']) {
                case 'mongoId':
                    if ($toArray) {
                        $value = (string) $value;
                    } elseif (! empty($value) && ! $value instanceof MongoId) {
                        $value = new MongoId($value);
                    }
                    break;
                case 'mongoDate':

                    if ($toArray) {
                        if (is_object($value)) {
                            $value = date('r', $value->sec);
                        }
                    } elseif (! empty($value) && ! $value instanceof MongoDate) {
                        if (! is_object($value)) {
                            $value = new MongoDate(strtotime($value));
                        }
                    }

                    break;
            }
        }
    }


    /**
     * Remove fields from the doc that aren't in the Schema
     *
     * @param $doc
     * @param array $schema
     */
    public function prune(&$doc, array $schema) {
        if (! $this->_isAssoc($doc)) {
            foreach ($doc as &$docItem) {
                $this->prune($docItem, $schema);
            }

            return;
        }

        foreach ($doc as $fieldKey => &$value) {
            if (! isset($schema[$fieldKey])) {
                unset($doc[$fieldKey]);
            } elseif (is_array($value) && isset($schema[$fieldKey]['schema'])) {
                $this->prune($value, $schema[$fieldKey]['schema']);
            }
        }
    }

    /**
     * Checks to see if an array is a numeric array
     * var_dump($this->_isAssoc(array('a', 'b', 'c'))); // false
     * var_dump($this->_isAssoc(array("0" => 'a', "1" => 'b', "2" => 'c'))); // false
     * var_dump($this->_isAssoc(array("1" => 'a', "0" => 'b', "2" => 'c'))); // true
     * var_dump($this->_isAssoc(array("a" => 'a', "b" => 'b', "c" => 'c'))); // true
     * @param $array
     *
     * @return bool
     */
    protected function _isAssoc($array) {
        // Return assoc vs numeric
        return array_keys($array) !== range(0, count($array) - 1);
    }
}

/**
 * MongoDbDateFormatter method
 *
 * This function cannot be in the class because of the way model save is written
 *
 * @param mixed $date null
 * @return void
 * @access public
 */
function MongoDbDateFormatter($date = null) {
	if ($date) {
		return new MongoDate($date);
	}
	return new MongoDate();
}


