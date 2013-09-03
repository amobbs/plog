<?php

namespace JqlParser;

use JqlParser\JqlFunction\JqlFunction;
use JqlParser\JqlKeyword\JqlKeyword;

class Parser {

    /**
     *
     * @var string
     */
    private $_jql = '';

    private $_sql = '';

    private $_expression = array();

    private function _findKeyword($keywordSymbol, $jqlSymbol = TRUE) {
        foreach (JqlKeyword::listKeywords() as $keyword) {
            if ($jqlSymbol) {
                if (strtoupper($keywordSymbol) == $keyword->getJqlSymbol())
                    return $keyword;
            } else {
                if (strtoupper($keywordSymbol) == $keyword->getSqlSymbol())
                    return $keyword;
            }
        }

        return NULL;
    }

    public function getJql() {
        return $this->_jql;
    }

    public function getSql() {
        return $this->_sql;
    }

    public function setJqlFromSql($sql) {
        $this->_sql = $sql;
        $where = $this->_getWhereFromSql($sql, "");

        $this->_expression = $this->_seperateIntoGroups($where);

        $this->_jql = $this->_experssionToJql($this->_expression);
    }

    public function setSqlFromJql($jql) {
        $this->_jql = $jql;

        $this->_expression = $this->_seperateIntoGroups($jql);

        $this->_sql = $this->_expressionToSql($this->_expression);
    }

    public function getMongoCriteria() {
        return '{ ' . $this->_buildMongoCriteria($this->_expression) . ' }';
    }

    private function _buildMongoCriteria($expression) {
        if ($expression instanceof Clause) {
            return '{ ' . $expression->getMongoCriteria() . ' }';
        }

        $doc = '';
        foreach($expression as $keyword => $clause) {
            $keyword = $this->_findKeyword($keyword);
            $subClauses = $this->_buildMongoCriteria($clause);

            if ($keyword instanceof JqlKeyword) {
                return $keyword->getMongoSymbol() . ' : {' . $this->_buildMongoCriteria($clause)  .' }';
            }

            if (is_array($clause)) {
                foreach ($clause as $subKeyWord => $subClauses) {
                    $subKeyWordObj = $this->_findKeyword($subKeyWord);
                    $doc .= ' ' . $subKeyWordObj->getMongoSymbol() . ' : ' . $this->_buildMongoCriteria($subClauses) . ' ';
                }
            } else {
                $doc .= '{ '. $clause->getMongoCriteria() . ' }, ';
            }
        }

        if (substr($doc, -2) == ', ') {
            $doc = substr($doc, 0, -2);
        }

        return $doc;
    }

    private function _getWhereFromSql($sql, $args)
    {
        // Don't need anything before the WHERE clause
        $ret = preg_replace('%(^.*?WHERE\s|"x\d"\.)%sm', '', $sql);

        // Remove the first and last parentheses
        //$ret = substr($ret, 1);
        //$ret = substr($ret, 0, -1);

        // Recursively remove excess parentheses
        do {
            $ret = preg_replace('%(\()("[^"]+"\s(?:=|<|>|>=|<=|!=)\s\?)(\))%', '$2', $ret, -1, $count);
        } while ($count > 0);

        // Replace ? with the list of arguments
        $ret = str_replace('?', '"%s"', $ret);
        $ret = vsprintf($ret, $args);

        // Remove quotes around just numbers
        $ret = preg_replace('%"(\d+)"%', '$1', $ret);

        return $ret;
    }

    /**
     * given an SQL string. parse it and make it in to an array similar to the way cakePHP conditions for,
     * @param $string
     *
     * @return array
     */
    private function _seperateIntoGroups($string) {
        $groups = array();

        $open = strpos($string, '(');
        if ($open === FALSE) {
            return new Clause($string);
        } else { //add one to remove the first ( from the string
            $open++;
        }

        $close = -1;
        $groupCount = 0;
        for($i = $open; $i < strlen($string); $i++) {
            $char = substr($string, $i, 1);
            if($char == '(') {
                $groupCount++;
            } else if ($char == ')') {
                if ($groupCount > 0) {
                    $groupCount--;
                } else {
                    $close = $i - $open;
                    break;
                }
            }
        }

        if ($close > -1) {
            $groupString = strtoupper(substr($string, $open, $close));
        } else {
            $groupString = strtoupper(substr($string, $open));
        }

        //there is more grouping then just the IN keyword
        if ($this->_groupsOnlyInString($groupString)) {
            $subGroups = $this->_seperateIntoGroups($groupString);
            if (sizeof($subGroups) > 1) {
                $groups[] = array($this->_findFirstKeyword($groupString, $subGroups) => $subGroups);
            } else {
                $groups[] = $subGroups;
            }
        }  else {
            $groups[] =  new Clause($groupString);
        }

        $groupStringLen = strlen($groupString) + 2; //+2 to include the ( and )
        $restOfString = substr($string, $groupStringLen);

        if ($this->_groupsOnlyInString($restOfString)) {
            $subGroups = $this->_seperateIntoGroups($restOfString);
            if (sizeof($subGroups) > 1) {
                $groups[] = array($this->_findFirstKeyword($groupString, $subGroups) => $subGroups);
            } else {
                $groups[] =  $subGroups;
            }
        }

        if (sizeof($groups) == 2) {
            if (is_array($groups[0]) && is_array($groups[1])) {
                return new Clause($groups);
            } else {
                return array($this->_findFirstKeyword($string, $groups) => $groups);
            }
        } else {
            return $groups[0];
        }
    }

    /**
     * given a string find the keyword that is closest to the begining of the string.
     * @param $string
     *
     * @return mixed
     */
    private function _findFirstKeyword($string) {
        $closestKeywordPos = strlen($string);
        $closestKeyword = null;
        foreach(JqlKeyword::listKeywords() as $keyword) {
            $keywordPos = strpos($string, $keyword->getJqlSymbol());
            if ($keywordPos !== false && $keywordPos < $closestKeywordPos) {
                $closestKeywordPos = strpos($string, $keyword->getJqlSymbol());
                $closestKeyword = $keyword->getJqlSymbol();
            }
        }
        return $closestKeyword;
    }

    private function _experssionToJql($expression) {
        $jql = '';
        if ($expression instanceof Clause) {
            return $expression->getJql();
        }

        foreach($expression as $operator => $clauses) {
            if (is_array($clauses) && sizeof($clauses) == 2) {
                $jql .= '(';
                $jql .= (is_array($clauses[0]) ? $this->_experssionToJql($clauses[0]) : $clauses[0]->getJql());
                $jql .= ' ' . $operator . ' ';
                $jql .= (is_array($clauses[1]) ? $this->_experssionToJql($clauses[1]) : $clauses[1]->getJql());
                $jql .= ')';
            } else {
                $jql .= $clauses->getJql();
            }
        }
        return $jql;
    }

    private function _expressionToSql($expression) {
        $sql = 'SELECT * FROM "Logs" WHERE ';
        if ($expression instanceof Clause) {
            return $sql . $expression->getSql();
        }

        foreach($expression as $operator => $clauses) {
            if (is_array($clauses) && sizeof($clauses) == 2) {
                $sql .= '(';
                $sql .= (is_array($clauses[0]) ? $this->_experssionToSql($clauses[0]) : $clauses[0]->getSql());
                $sql .= ' ' . $operator . ' ';
                $sql .= (is_array($clauses[1]) ? $this->_experssionToSql($clauses[1]) : $clauses[1]->getSql());
                $sql .= ')';
            } else {
                $sql .= $clauses->getJql();
            }
        }

        return $sql;
    }

    /**
     * given an sql/jql string return if there are any groups that need to be broken up or if there are just 'IN'
     * keywords and functions in the string. this is needed because In keywords, groups and functiosn all use ( and )
     *
     * @param $string
     *
     * @return bool
     */
    private function _groupsOnlyInString($string) {
        $groupStartCount = substr_count($string, '(');
        $inStartCount = substr_count($string, 'IN (');
        $functionCount = $this->_countFunctionsInString($string);

        if ($groupStartCount > ($inStartCount + $functionCount)) {
            return true;
        }

        return false;
    }

    private function _countFunctionsInString($string) {
        $count = 0;
        foreach (JqlFunction::listFunctions() as $function) {
            $count += substr_count($string, $function->getName() . '(');
        }
        return $count;
    }
}