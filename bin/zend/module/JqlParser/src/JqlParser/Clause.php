<?php
namespace JqlParser;

use JqlParser\JqlFunction\JqlFunction;
use JqlParser\JqlOperator\JqlOperator;

class Clause {
    private $_sql;
    private $_field;
    private $_operator;
    private $_value;

    private static $STRIP_TABLENAME = TRUE;

    public function __construct($sql) {
        $this->_sql = $sql;
        $this->_populateFields();
    }

    public function getJql() {
        return $this->_field . ' ' . $this->_operator->getJqlSymbol() . ' ' . $this->_operator->formatValueForSql($this->_value);
    }

    public function getSql() {
        return $this->_field . ' ' . $this->_operator->getSqlSymbol() . ' ' . $this->_operator->formatValueForSql($this->_value);
    }

    public function getMongoCriteria() {
        $doc = "$this->_field  : ";

        $value = $this->_operator->formatValueForJql($this->_value);
        if ($this->_operator->getMongoInline()) {
            $doc .= " $value";
        } elseif ($this->_operator instanceof JqlOperator) {
            $doc .= ' { ' . $this->_operator->getMongoSymbol() . " : $value }";
        }

        return $doc;
    }

    private function _populateFields() {
        $parts = explode(' ', $this->_sql);
        $this->_field = $this->_stripTableNameFromSql($parts[0]);
        $this->_operator = $this->_findOperator($parts[1]);
        $this->_value = trim(substr($this->_sql, strpos($this->_sql, $this->_operator->getSqlSymbol()) + strlen( $this->_operator->getSqlSymbol())));
        $this->_value = $this->_executeFunctionInValue($this->_value);
    }

    private function _findOperator($operatorSymbol, $jqlSymbol = TRUE) {
        foreach (JqlOperator::listOperators() as $operator) {
            if ($jqlSymbol) {
                if (strtoupper($operatorSymbol) == $operator->getJqlSymbol())
                    return $operator;
            } else {
                if (strtoupper($operatorSymbol) == $operator->getSqlSymbol())
                    return $operator;
            }
        }

        return NULL;
    }


    private function _findFunction($functionName) {
        foreach (JqlFunction::listFunctions() as $function) {
            if (strtoupper($functionName) == $function->getName())
                return $function;
        }

        return NULL;
    }

    /**
     * find any function that is in the value section
     * Assumption there is only one function per value.
     * @param $value
     *
     * @return mixed
     */
    private function _executeFunctionInValue($value) {
        if (substr_count($value, '(') == 0) return $value;

        $parts = explode('(', $value);
        $functionName = trim($parts[0]);
        $args = trim($parts[1]);

        $args = str_replace(')', '', $args);
        $function = $this->_findFunction($functionName);

        if ($function === null) return $value;

        return $function->execute($args);
    }

    private function _stripTableNameFromSql($value) {
        if (!Clause::$STRIP_TABLENAME) {
            return $value;
        }

        $dotPos = strpos($value, '.');
        $stripped = $value;
        if ($dotPos > 0) {
            $stripped = substr($value, $dotPos + 1);
        }

        return $stripped;
    }
}