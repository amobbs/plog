<?php
namespace Preslog\JqlParser\JqlOperator;

use Zend\Validator\NotEmpty;

class JqlOperator {

    public static function listOperators() {
        return array(
            '!=' => new NotEqualsOperator(),
            '=' => new EqualsOperator(),
            '>' => new GreaterThanOperator(),
            '<' => new LessThanOperator(),
            'NOT IN' => new NotInOperator(),
            'IN' => new InOperator(),
            '~' => new LikeOperator(),
        );
    }

    /**
     * the character or string used to identify this operator (eg: < or IN)
     * @var String
     */
    private $_jqlSymbol;

    /**
     * equivilent sql version of the operator
     * @var String
     */
    private $_sqlSymbol;

    private $_mongoSymbol;

    private $_humanReadable;

    /**
     * when outputting mongo and criteria uses this operator should the value be in its own object?
     * @var Boolean
     */
    private $_mongoInline;

    /**
     * list of query field types (used by red query builder) defines when it is shown
     */
    private $_appliesToFields;

    /**
     * Constructor
     *
     * @param $jqlSymbol
     * @param $sqlSymbol
     * @param $mongoSymbol
     * @param $humanReadable
     * @param $appliedToFields
     * @param $mongoInline
     *
     * @internal param $symbol
     */
    public function _construct($jqlSymbol, $sqlSymbol, $mongoSymbol, $humanReadable, $appliedToFields, $mongoInline) {
        $this->_jqlSymbol= $jqlSymbol;
        $this->_sqlSymbol = $sqlSymbol;
        $this->_mongoSymbol = $mongoSymbol;
        $this->_humanReadable = $humanReadable;
        $this->_appliesToFields = $appliedToFields;
        $this->_mongoInline = $mongoInline;

    }

    /**
     * returns uppercased symbol
     * @return string
     */
    public function getJqlSymbol() {
        return strtoupper($this->_jqlSymbol);
    }

    /**
     * return upper cassed equivilent sql symbol for operator
     * @return string
     */
    public function getSqlSymbol() {
        return strtoupper($this->_sqlSymbol);
    }

    public function getMongoSymbol() {
        return $this->_mongoSymbol;
    }

    public function getHumanReadable() {
        return $this->_humanReadable;
    }

    public function isAppliedTo($fieldType) {
        foreach($this->_appliesToFields as $applies)
        {
            if ($fieldType == $applies)
            {
                return true;
            }
        }

        return false;
    }

    public function formatValueForJql($value) {
        return $value;
    }

    public function formatValueForSql($value) {
        return $value;
    }

    /**
     * @return boolean
     */
    public function getMongoInline ()
    {
        return $this->_mongoInline;
    }

    /**
     * used to evaluate in code if the value1 'operator' value2 = true
     * the idea is to use this in the case where we need to evaluate the operation in code and not the db
     * eg: in preslog the slect field data is stored on the client in mongo not the log field so we can not
     * use the db to do LIKE operators.
     * @param $value1
     * @param $value2
     *
     * @return bool
     */
    public function matches($value1, $value2)
    {
        return true;
    }


}