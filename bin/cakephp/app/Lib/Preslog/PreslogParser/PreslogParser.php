<?php

namespace Preslog\PreslogParser;

use MongoId;
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
        $keys = array_keys($expression);
        $fieldName = $keys[0];
        $value = $expression[$fieldName];

        //TODO replace with clientEntity
        $fieldIds = array();
        $dataField = 'seconds';
        foreach($this->clients as $client) {
            foreach($client['fields'] as $field) {
                if ($fieldName == 'loginfo' &&
                    ($field['name'] == 'created' || $field['name'] == 'modified')) {
                    $dataField = $field['name'];
                    $fieldIds[] = new MongoId($field['_id']);
                }
                if ($field['name'] == $fieldName) {
                    $fieldIds[] = new MongoId($field['_id']);
                }
            }
        }

        return array(
            'fields.field_id' => array('$in' => $fieldIds),
            'fields.data.' . $dataField => $value,
        );
    }
}