<?php

namespace Preslog\PreslogParser;

use ClassRegistry;
use Configure;
use Exception;
use MongoId;
use MongoRegex;
use Preslog\JqlParser\Clause;
use Preslog\JqlParser\JqlKeyword\JqlKeyword;
use Preslog\JqlParser\JqlOperator\EqualsOperator;
use Preslog\JqlParser\JqlOperator\GreaterThanOperator;
use Preslog\JqlParser\JqlOperator\InOperator;
use Preslog\JqlParser\JqlOperator\LessThanOperator;
use Preslog\JqlParser\JqlOperator\LikeOperator;
use Preslog\JqlParser\JqlOperator\NotEqualsOperator;
use Preslog\JqlParser\JqlOperator\NotInOperator;
use Preslog\JqlParser\JqlParser;
use Preslog\Logs\FieldTypes\Select;

class PreslogParser extends JqlParser {

    /**
     * @var array of clients from database
     */
    private $clients;

    /**
     * given the expression on this object parse it in a way so that we replace human readable field names (client.fields.name)
     * with mongo critera in the format required
     *
     * @param $clients
     *
     * @throws \Exception
     * @return array
     */
    public function parse($clients) {
        $errors = $this->validate($clients);
        if (sizeof($errors) > 0 )
        {
            throw new Exception('Validation errors have been found in the query string');
        }
        $this->clients = $clients;
        $result = $this->buildPreslog($this->_expression);
        return $result;
    }

    public function validate($clients)
    {
        $useClients = array();
        foreach($clients as $client)
        {
            if ( isset($client['Client']) )
            {
                $useClients[] = $client['Client'];
            }
            else
            {
                $useClients[] = $client;
            }
        }

        return $this->_validateExpression($this->_expression, $useClients);
    }

    protected function _validateExpression($expression, $clients)
    {
        if ( $expression instanceof Clause )
        {
            return $this->_validateClause($expression, $clients);
        }

        if ( ! is_array($expression) )
        {
            return array('Malformed query');
        }

        $errors = array();

        foreach( $expression as $clause )
        {
            if ($clause instanceof Clause)
            {
                $errors = array_merge($this->_validateClause($clause, $clients), $errors);
            }
            else
            {
                $errors = array_merge($this->_validateExpression($clause, $clients), $errors);
            }
        }

        return $errors;
    }

    /**
     * Validate the specific $clause being used.
     * First check if the field being looked for is a "special" type field, and validate as appropriate.
     * Lastly, check if the field at least exists in one of the clients.
     * @param $clause
     * @param $clients
     * @return array
     */
    protected function _validateClause($clause, $clients)
    {
        // Models
        $clientModel = ClassRegistry::init('Client');

        // Config
        $config = Configure::read('Preslog');

        // Error container
        $errors = parent::_validateClause($clause);

        // Operator, field and value
        $operator = $clause->getOperator();
        $field = $clause->getField();
        $value = $clause->getValue();

        // Field type: ID
        if ($field == 'id')
        {
            $logRegex = $config['regex']['logid'];
            $parts = array();

            // check the log ID isn't empty
            if (empty($value))
            {
                $errors[] = "A Log ID must be provided in the format [prefix]_#[numeric id]";
            }

            //check logid matches required format
            elseif ( !preg_match($logRegex, $value, $parts) )
            {
                $errors[] = "The Log ID provided does not match the format required. [prefix]_#[numeric id]";
            }

            elseif ( !($operator instanceof EqualsOperator) )
            {
                $errors[] = "The operator " . $operator->getHumanReadable() . ' can not be used with the field "ID". Operators allowed are = ';
            }

            else
            {
                //find client the prefix matches
                $client = $clientModel->find('first', array(
                    'conditions' => array(
                        'logPrefix' => $parts[1]
                    ),
                ));

                if (empty($client))
                {
                    $errors[] = $parts[1] . ' is not a valid log prefix';

                }
            }

            return $errors;
        }

        // Field Type: Client
        elseif ($field == 'client')
        {
            $allowed = array(
                new EqualsOperator(),
                new NotEqualsOperator(),
                new LikeOperator(),
                new InOperator(),
                new NotInOperator(),
            );

            $allowedString = '';
            if ( ! $this->operatorAllowed($operator, $allowed, $allowedString) )
            {
                $errors[] = "The operator " . $operator->getHumanReadable() . ' can not be used with the field "' . $clause->getField() . '". Operators allowed are ' . $allowedString;
            }

            return $errors;
        }

        // Field Type: Client_Id
        elseif ($field == 'client_id')
        {
            $allowed = array(
                new EqualsOperator(),
                new NotEqualsOperator()
            );

            $allowedString = '';
            if ( ! $this->operatorAllowed($operator, $allowed, $allowedString) )
            {
                $errors[] = "The operator " . $operator->getHumanReadable() . ' can not be used with the field "' . $clause->getField() . '". Operators allowed are ' . $allowedString;
            }

            return $errors;
        }

        // Field type: Text
        elseif ($field == 'text')
        {
            $allowed = array(
                new EqualsOperator(),
                new NotEqualsOperator(),
                new LikeOperator(),
            );

            $allowedString = '';
            if ( ! $this->operatorAllowed($operator, $allowed, $allowedString) )
            {
                $errors[] = "The operator " . $operator->getHumanReadable() . ' can not be used with the field "' . $clause->getField() . '". Operators allowed are ' . $allowedString;
            }

            return $errors;
        }


        // Field Type: Created/Modified
        elseif ($field == 'created' || $clause->getField() == 'modified' )
        {

            $allowed = array(
                new EqualsOperator(),
                new NotEqualsOperator(),
                new GreaterThanOperator(),
                new LessThanOperator(),
            );

            $allowedString = '';
            if ( ! $this->operatorAllowed($operator, $allowed, $allowedString) )
            {
                $errors[] = "The operator " . $operator->getHumanReadable() . ' can not be used with the field "' . $clause->getField() . '". Operators allowed are ' . $allowedString;
            }

            // TODO: validate date time

            return $errors;
        }

        // check the field exists for at least one client
        else
        {

            $foundOnce = false;
            foreach($clients as $client)
            {
                $clientEntity = $clientModel->getClientEntityById($client['_id']);
                $clientField = $clientEntity->getFieldTypeByName( $clause->getField() );
                if ($clientField == null)
                {
                    //check attribute groups
                    foreach($clientEntity->data['attributes'] as $attr)
                    {
                        if (strtolower($attr['name']) == strtolower($clause->getField()))
                        {
                            $foundOnce = true;
                        }
                    }
                    continue;
                }

                $foundOnce = true;

                $allowedString = '';
                if ( ! $this->operatorAllowed($operator, $clientField->getProperties('allowedJqlOperators'), $allowedString) )
                {
                    $errors[] = "The operator " . $operator->getHumanReadable() . ' can not be used with the field ' . $clause->getField() . '. Operators allowed are ' . $allowedString;
                }
            }

            // Field couldn't be found!
            if ( ! $foundOnce )
            {
                $errors[] = 'The field "' . $field . '" does not exist.';
            }
        }

        // Return any errors.
        return $errors;
    }

    private function operatorAllowed($operator, $allowedArray, &$allowedString)
    {
        $operatorAllowed = false;
        $allowedString = '';
        foreach($allowedArray as $allowed)
        {
            $allowedString .= $allowed->getJqlSymbol() . ' ';
            if ($operator instanceof $allowed)
            {
                $operatorAllowed = true;
            }
        }

        return $operatorAllowed;

    }

    /**
     * recursively convert Preslog\Clauses found into mongo expression needed
     * @param $expression
     *
     * @return array
     */
    private function buildPreslog($expression) {
        if (!$expression) {
            return array();
        }

        if ($expression instanceof Clause) {
            return $this->mongoExpressionToPreslog($expression);
        }

        $conditions = array();
        foreach($expression as $keyword => $clause) {
            $keywordObj = $this->_findKeyword($keyword);
            $subClauses = $this->buildPreslog($clause);

            if ($keywordObj instanceof JqlKeyword) {
                $conditions[$keywordObj->getMongoSymbol()] = $subClauses;
            }
            else
            {
                $conditions[] = $subClauses;
            }
        }

        return $conditions;
    }

    /**
     * replace field name ain expression with list of client field id's
     *
     * @param $clause
     *
     * @internal param $expression
     *
     * @return array
     */
    private function mongoExpressionToPreslog($clause) {
        $expression = $clause->getMongoCriteria();

        //there should only be one field/value pair in the expression
        $keys = array_keys($expression);
        $fieldName = $keys[0];
        $value = $expression[$fieldName];

        $clientModel = ClassRegistry::init('Client');

        //hard replace hrid (db name) with a more human readable "id"
        if ($fieldName == 'id')
        {
            $config = Configure::read('Preslog');
            $logRegex = $config['regex']['logid'];

            //split the log prefix from numeric log id
            $parts = array();

            $checkValue = $value;
            if ( is_array($value) && sizeof($value) > 0)
            {
                $values = array_values($value);
                $checkValue = $values[0];
            }

            if ( preg_match($logRegex, $checkValue, $parts) )
            {
                $prefix = $parts[1];
                $numericId = (int)$parts[2];

                $searchArray = array('hrid' => $numericId);
                if ($clause->getOperator() instanceof NotEqualsOperator)
                {
                   $searchArray = array('$not' => $searchArray);
                }

                //find client the prefix matches
                $client = $clientModel->find('first', array(
                    'conditions' => array(
                        'logPrefix' => $prefix
                    ),
                ));

                return array(
                    '$and' => array(
                        array('client_id' => new MongoId($client['Client']['_id'])),
                        $searchArray,
                    ),
                );
            }
        }

        //add the option to search for clients using 'client' field
        //match client id to name

        if ($fieldName == 'client')
        {
            $clientIds = array();
            $clients = $clientModel->find('all');
            foreach($clients as $client)
            {
                $operator = $clause->getOperator();
                if ($operator->matches(strtoupper($client['Client']['name']), $value))
                {
                    $clientIds[] = new MongoId($client['Client']['_id']);
                }
            }

            return array(
                'client_id' => array(
                    '$in' => $clientIds,
                )
            );
        }

        if ($fieldName == 'client_id')
        {
            return array(
                'client_id' => new MongoId($value),
            );
        }

        //we have a special case, to just search all text fields, mostly used for quick search
        if ($fieldName == 'text')
        {
            return array(
                'fields.data.text' => new MongoRegex("/^$value$/i"),
            );
        }

        $fieldIds = array();
        $dataField = '';
        $isText = false;
        $isSelect = false;
        $selectIn = array();

        //get matching field ids so we only search against the fields specified
        foreach($this->clients as $client) {
            //sometimes we get mongoIds some times we get strings (not sure why)
            if ( isset($client['Client']) )
            {
                $client = $client['Client'];
            }

            $clientEntity = $clientModel->getClientEntityById((string)$client['_id']);

            $operator = $clause->getOperator();

            $clientField = $clientEntity->getFieldTypeByName( $fieldName );
            if ($clientField == null)
            {
                //the field name does not exist maybe it is an attribute
                $attributeIds = array();
                if ( isset($client['attributes']) )
                {
                    foreach($client['attributes'] as $attr)
                    {
                        //match fieldname to group name
                        if (strtolower($fieldName) == strtolower($attr['name']))
                        {
                            //check all children for matching attribute
                            foreach($attr['children'] as $child)
                            {
                                if ($operator->matches($child['name'], $value))
                                {
                                    $attributeIds[] = new MongoId($child['_id']);
                                }
                                foreach($child['children'] as $subChild)
                                {
                                    if ($operator->matches($subChild['name'], $value))
                                    {
                                        $attributeIds[] = new MongoId($subChild['_id']);
                                    }
                                }
                            }
                        }
                    }
                }

                if (sizeof($attributeIds) == 0)
                {
                    continue;
                }

                return array(
                    'attributes' => array(
                        '$in' => $attributeIds
                    )
                );
            }

            $clientFieldSettings = $clientField->getFieldSettings();

            $fieldIds[] = new MongoId($clientFieldSettings['_id']);

            //created/modified/version are actually all inside loginfo, so a stupid special case for them
            if ($fieldName == 'created' || $fieldName == 'modified' || $fieldName == 'version')
            {
                $dataField = $fieldName;
            }
            else
            {
                $schema = $clientField->getMongoSchema();
                $schemaKeys = array_keys( $clientField->getMongoSchema() );
                $dataField = $schemaKeys[0];
                if($schema[$dataField]['type'] == 'string')
                {
                    $isText = true;
                }

                //select fields dont store the data on the log, only a reference to the option set on the client field
                if ( $clientField instanceof Select )
                {
                    $isText = false;
                    $isSelect = true;

                    // empty string will not match any mongo id's so force it in.
                    if ( $operator->matches("", $value) )
                    {
                        $selectIn[] = "";
                    }

                    //loop through the actual values for the select and find which ones match since we can not do it in the db
                    $preslogSettings = $clientField->getFieldSettings();
                    $options = $preslogSettings['data']['options'];
                    foreach( $options as $option )
                    {
                        if ( $operator->matches($option['name'], $value) )
                        {
                            $selectIn[] = new MongoId($option['_id']);
                        }
                    }
                }
            }
        }

        //make case insensitive
        if ($isText)
        {
            if ( is_array($value) )
            {
                $newValue = array();
                foreach($value as $key => $val)
                {
                    $newValue[$key] = new MongoRegex("/^$val$/i");
                }
                $value = $newValue;
            }
            else
            {
                $value = new MongoRegex("/^$value$/i");
            }
        }

        if ( $isSelect )
        {
            $value = array(
                '$in' => $selectIn,
            );
        }

       return array(
            '$and' => array(
                array('fields.field_id' => array('$in' => $fieldIds)),
                array('fields.data.' . $dataField => $value),
            ),
        );
    }
}