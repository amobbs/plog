<?php

namespace Preslog\PreslogParser;

use ClassRegistry;
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
        return $this->buildPreslog($this->_expression);
    }

    /**
     * recursivly convert Preslog\Clauses found into mongo expression needed
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
            $keyword = $this->_findKeyword($keyword);
            $subClauses = $this->buildPreslog($clause);

            if ($keyword instanceof JqlKeyword) {
                return array(
                    $keyword->getMongoSymbol() => $subClauses,
                );
            }

            if (is_array($clause)) {
                foreach ($clause as $subKeyWord => $subClauses) {
                    $subKeyWordObj = $this->_findKeyword($subKeyWord);
                    $conditions[] = array(
                        $subKeyWordObj->getMongoSymbol() => $this->buildPreslog($subClauses),
                    );
                }
            } else {
                $conditions[] = $this->mongoExpressionToPreslog($clause);
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

        //hard replace hrid (db name) with a more human readable "id"
        if ($fieldName == 'id')
        {
            return array(
                'hrid' => $value
            );
        }

        //add the option to search for clients using 'client' field
        //match client id to name
        $clientModel = ClassRegistry::init('Client');
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

        $fieldIds = array();
        $dataField = '';
        $isText = false;
        $isSelect = false;
        $selectIn = array();

        //go through each client we have access to and get the id for the field we are searching on
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
            $clientField = $clientEntity->getFieldTypeByName( $fieldName );
            if ($clientField == null)
            {
                continue;
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

                    $operator = $clause->getOperator();
                    //loop through the actual values for the select and find which ones match since we can not do it in the db
                    $options = $clientField->getFieldSettings()['data']['options'];
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
            'fields.field_id' => array('$in' => $fieldIds),
            'fields.data.' . $dataField => $value,
        );
    }
}