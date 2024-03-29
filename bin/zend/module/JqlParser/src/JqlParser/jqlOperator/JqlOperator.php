<?php
namespace JqlParser\JqlOperator;

class JqlOperator {

    public static function listOperators() {
        return array(
            '=' => new EqualsOperator(),
            '>' => new GreaterThanOperator(),
            '<' => new LessThanOperator(),
            'NOT IN' => new NotInOperator(),
            'IN' => new InOperator(),
            'LIKE' => new LikeOperator(),
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

    /**
     * when outputting mongo and criteria uses this operator should the value be in its own object?
     * @var Boolean
     */
    private $_mongoInline;

    /**
     * Constructor
     * @param $symbol
     * @param $sqlSymbol
     */
    public function _construct($jqlSymbol, $sqlSymbol, $mongoSymbol, $mongoInline) {
        $this->_jqlSymbol= $jqlSymbol;
        $this->_sqlSymbol = $sqlSymbol;
        $this->_mongoSymbol = $mongoSymbol;
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


}