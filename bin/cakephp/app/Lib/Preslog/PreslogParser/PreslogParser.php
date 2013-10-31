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
use Preslog\JqlParser\JqlOperator\LessThanOperator;
use Preslog\JqlParser\JqlOperator\NotEqualsOperator;
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
     * @param $clients
     *
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
        return $this->_validateExpression($this->_expression, $clients);
    }

    private function _validateExpression($expression, $clients)
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

    private function _validateClause($clause, $clients)
    {
        $errors = array();

        $config = Configure::read('Preslog');
        $operator = $clause->getOperator();

        if ($clause->getField() == 'id')
        {
            $logRegex = $config['regex']['logid'];

            //check logid matches required format
            if ( !preg_match($logRegex, $clause->getValue()) )
            {
                $errors[] = "The Log ID provided does not match the format required. [prefix]_#[numeric id] ";
            }

            if ( ! ($operator instanceof EqualsOperator || $operator instanceof NotEqualsOperator) )
            {
                $errors[] = "You can only use the Equals or not Equals operator when searching by Log ID";
            }

            return $errors;
        }

        if ($clause->getField() == 'created' || $clause->getField() == 'modified' )
        {

            $allowed = array(
                new EqualsOperator(),
                new NotEqualsOperator(),
                new GreaterThanOperator(),
                new LessThanOperator()
            );

            $allowedString = '';
            if ( ! $this->operatorAllowed($operator, $allowed, $allowedString) )
            {
                $errors[] = "The operator " . $operator->getHumanReadable() . ' can not be used with the field ' . $clause->getField() . '. Operators allowed are ' . $allowedString;
            }
            //validate date time
            return $errors;
        }


        $clientModel = ClassRegistry::init('Client');

        foreach($clients as $client)
        {
            $clientEntity = $clientModel->getClientEntityById($client['_id']);
            $clientField = $clientEntity->getFieldTypeByName( $clause->getField() );
            if ($clientField == null)
            {
                return array('The field "' . $clause->getField() . '" does not exist.');
            }

            $allowedString = '';
            if ( ! $this->operatorAllowed($operator, $clientField->getProperties('allowedJqlOperators'), $allowedString) )
            {
                $errors[] = "The operator " . $operator->getHumanReadable() . ' can not be used with the field ' . $clause->getField() . '. Operators allowed are ' . $allowedString;
            }

            //all so check field value validates against field type (inc functions).
        }

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

            if ( preg_match($logRegex, $value, $parts) )
            {
                $prefix = $parts[1];
                $numericId = (int)$parts[2];

                //find client the prefix matches
                $client = $clientModel->find('first', array(
                    'conditions' => array(
                        'logPrefix' => $prefix
                    ),
                ));

                return array(
                    '$and' => array(
                        array('client_id' => new MongoId($client['Client']['_id'])),
                        array('hrid' => $numericId),
                    ),
                );
            }
        }

        //add the option to search for clients using 'client' field
        //match client id to name

        if ($fieldName == 'client')
        {
            $clientId = '';
            $clients = $clientModel->find('all');
            foreach($clients as $client)
            {
                if (strtoupper($client['Client']['name']) == $value)
                {
                    $clientId = $client['Client']['_id'];
                }
            }

            return array(
                'client_id' => new MongoId($clientId),
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
                $clientEntity = $clientModel->getClientEntityById($client['Client']['_id']);
            }
            else
            {
                $clientEntity = $clientModel->getClientEntityById((string)$client['_id']);
            }


            $operator = $clause->getOperator();

            $clientField = $clientEntity->getFieldTypeByName( $fieldName );
            if ($clientField == null)
            {
                //the field name does not exist maybe it is an attribute
                $attributeIds = array();
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
            $value = new MongoRegex("/^$value$/i");
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