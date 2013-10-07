<?php

namespace Preslog\JqlParser\JqlKeyword;

class JqlKeyword {

    public static function listKeywords() {
       return array(
            'or' => new OrKeyword(),
            'and' => new AndKeyword(),
        );
    }

    private $_sqlSymbol;
    private $_jqlSymbol;
    private $_mongoSymbol;

    public function __construct($sql, $jql, $mongo) {
        $this->_sqlSymbol = $sql;
        $this->_jqlSymbol = $jql;
        $this->_mongoSymbol = $mongo;
    }

    /**
     * @return mixed
     */
    public function getJqlSymbol ()
    {
        return $this->_jqlSymbol;
    }

    /**
     * @return mixed
     */
    public function getMongoSymbol ()
    {
        return $this->_mongoSymbol;
    }

    /**
     * @return mixed
     */
    public function getSqlSymbol ()
    {
        return $this->_sqlSymbol;
    }



}