<?php

namespace Preslog\PreslogParser;

use ClassRegistry;
use Configure;
use MongoId;
use MongoRegex;
use Preslog\JqlParser\Clause;
use Preslog\JqlParser\JqlKeyword\JqlKeyword;
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
        $this->clients = $clients;
        $result = $this->buildPreslog($this->_expression);
        return $result;
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