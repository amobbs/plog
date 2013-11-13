<?php
namespace Preslog\JqlParser;

use Configure;
use Exception;
use MongoDate;
use Preslog\JqlParser\JqlExceptions\JqlParseException;
use Preslog\JqlParser\JqlFunction\JqlFunction;
use Preslog\JqlParser\JqlOperator\JqlOperator;

class Clause {
    private $_unparsedClause;
    private $_originallyJql;
    private $_field;
    private $_operator;
    private $_value;

    private static $STRIP_TABLENAME = TRUE;

    public function __construct($unparsedClause, $jql = FALSE) {
        $this->_unparsedClause = trim($unparsedClause);
        $this->_originallyJql = $jql;
        $this->_populateFields(false);
    }

    public function getJql() {
        return $this->_field . ' ' . $this->_operator->getJqlSymbol() . ' "' . $this->_value . '"';
    }

    public function getSql() {
        return $this->_field . ' ' . $this->_operator->getSqlSymbol() . ' ?';
    }

    public function getValue() {
        return $this->_operator->formatValueForSql($this->_value);
    }

    public function getOperator() {
        return $this->_operator;
    }

    public function getField() { return $this->_field; }

    public function getFunctionEvaluatedForMongo() {
        //convert dates to mongo date
        if (strtotime($this->_value)) {
            return new MongoDate(strtotime($this->_value));
        }

        //cast numbers to int
        if (is_numeric($this->_value)) {
            return (int)$this->_value;
        }

        //convert duration style to seconds
        $preslogSettings = Configure::read('Preslog');
        $durationRegex = $preslogSettings['regex']['duration'];
        $matches = array();
        if (preg_match($durationRegex, $this->_value, $matches)) {
            $duration = 0;
            for($i = 1; $i < sizeof($matches); $i++) {
               switch ($matches[$i + 1]) {
                   case 'H':
                       $duration += ($matches[$i] * 60) * 60;
                       $i++;
                       break;
                   case 'M':
                       $duration += $matches[$i] * 60;
                       $i++;
                       break;
                   case 'S':
                       $duration += $matches[$i];
                       $i++;
                       break;
               }
            }
            return $duration;
        }

        //execute any functions found
        return $this->_executeFunctionInValueForMongo($this->_value);
    }

    public function getFunctionEvaluated() {
        //convert dates to mongo date
        if (strtotime($this->_value)) {
            return strtotime($this->_value);
        }

        //cast numbers to int
        if (is_numeric($this->_value)) {
            return (int)$this->_value;
        }

        //convert duration style to seconds
        $preslogSettings = Configure::read('Preslog');
        $durationRegex = $preslogSettings['regex']['duration'];
        $matches = array();
        if (preg_match($durationRegex, $this->_value, $matches)) {
            $duration = 0;
            for($i = 1; $i < sizeof($matches); $i++) {
                switch ($matches[$i + 1]) {
                    case 'H':
                        $duration += ($matches[$i] * 60) * 60;
                        $i++;
                        break;
                    case 'M':
                        $duration += $matches[$i] * 60;
                        $i++;
                        break;
                    case 'S':
                        $duration += $matches[$i];
                        $i++;
                        break;
                }
            }
            return $duration;
        }

        //execute any functions found
        return $this->_executeFunctionInValue($this->_value);
    }


    public function getMongoCriteria() {
        $value = $this->getFunctionEvaluatedForMongo($this->_operator->formatValueForJql($this->_value));

        //some mongo operators (line $in) requires a subobject, others like '=' are inline.
        if ($this->_operator instanceof JqlOperator && !$this->_operator->getMongoInline()) {
            $value = array($this->_operator->getMongoSymbol() => $value);
        }

        return array(
            $this->_field => $value,
        );
    }

    public function getMongoCriteriaAsString() {
        $doc = "'$this->_field'  : ";

        $value = $this->getFunctionEvaluatedForMongo($this->_operator->formatValueForJql($this->_value));
        if ($this->_operator->getMongoInline()) {
            $doc .= " '$value'";
        } elseif ($this->_operator instanceof JqlOperator) {
            $doc .= ' { ' . $this->_operator->getMongoSymbol() . " : $value }";
        }

        return $doc;
    }

    private function _populateFields() {
        $parts = $this->_explodeClause($this->_originallyJql);

        if ( sizeof($parts) == 0 )
        {
            throw new Exception('invalid jql clause "' . $this->_unparsedClause . '". Clause should be in the form <field name> <operator> <value>');
        }

        $this->_field = strtolower($this->_stripTableName($parts['field']));
        $this->_operator = $this->_findOperator($parts['operator']);

        $value = $parts['value'];
        //remove " for encapsulated strings but not every occurance
        if (substr($value, 0, 1) == '"' && substr($value, -1) == '"')
        {
            $value = substr($value, 1, -1);
        }
        $this->_value = $value;
    }

    private function _explodeClause() {
        $parts = array();

        $operators = JqlOperator::listOperators();

        foreach(JqlOperator::listOperators() as $operator) {
            $operatorPos = false;
            $operatorSymbol = $operator->getJqlSymbol();
            if (!$this->_originallyJql) {
                $operatorSymbol = $operator->getSqlSymbol();
            }

            $operatorPos = strpos($this->_unparsedClause, $operatorSymbol);
            if ($operatorPos !== false ) {
                $parts['field'] = str_replace('"', '', trim(substr($this->_unparsedClause, 0, $operatorPos)));
                $parts['operator'] = trim(substr($this->_unparsedClause, $operatorPos, strlen($operatorSymbol)));
                $parts['value'] = trim(substr($this->_unparsedClause, $operatorPos + strlen($operatorSymbol)));

                return $parts;
            }
        }
        return $parts;
    }

    private function _findOperator($operatorSymbol) {
        foreach (JqlOperator::listOperators() as $operator) {
            if ($this->_originallyJql) {
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
    private function _executeFunctionInValueForMongo($value) {
        if (substr_count($value, '(') == 0) return $value;

        $parts = explode('(', $value);
        $functionName = trim($parts[0]);
        $args = trim($parts[1]);

        $args = str_replace(')', '', $args);
        $function = $this->_findFunction($functionName);

        if ($function === null) return $value;

        return $function->executeForMongo($args);
    }

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

    private function _stripTableName($value) {
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