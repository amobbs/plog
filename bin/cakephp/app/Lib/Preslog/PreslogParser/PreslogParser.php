<?php

namespace Preslog\PreslogParser;

use ClassRegistry;
use MongoId;
use MongoRegex;
use Preslog\JqlParser\Clause;
use Preslog\JqlParser\JqlKeyword\JqlKeyword;
use Preslog\JqlParser\JqlParser;

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
            return $this->mongoExpressionToPreslog($expression->getMongoCriteria());
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
                $exp = $clause->getMongoCriteria();
                $conditions[] = $this->mongoExpressionToPreslog($exp);
            }
        }

        return $conditions;
    }

    /**
     * replace field name ain expression with list of client field id's
     *
     * @param $expression
     *
     * @return array
     */
    private function mongoExpressionToPreslog($expression) {
        //there should only be one field/value pair in the expression
        $keys = array_keys($expression);
        $fieldName = $keys[0];
        $value = $expression[$fieldName];

        $fieldIds = array();
        $dataField = '';
        $isText = false;

        $clientModel = ClassRegistry::init('Client');

        //go through each client we have access to and get the id for the field we are searching on
        foreach($this->clients as $client) {
            $clientEntity = $clientModel->getClientEntityById((string)$client['_id']);

            $clientField = $clientEntity->getFieldTypeByName( $fieldName );
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
            }
        }

        if ($isText)
        {
            $value = new MongoRegex("/^$value$/i");
        }

        return array(
            'fields.field_id' => array('$in' => $fieldIds),
            'fields.data.' . $dataField => $value,
        );
    }
}