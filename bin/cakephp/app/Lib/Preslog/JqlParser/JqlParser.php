<?php

namespace Preslog\JqlParser;

use Preslog\JqlParser\JqlFunction\JqlFunction as JqlFunction;
use Preslog\JqlParser\JqlKeyword\JqlKeyword;

class JqlParser {

    /**
     * jql version of expression
     * @var string
     */
    private $_jql = '';

    /**
     * sql version of expression
     * @var string
     */
    private $_sql = '';

    /**
     * generic version of expression
     * @var array
     */
    private $_expression = array();

    public function getJql() {
        return $this->_jql;
    }

    public function getSql() {
        return $this->_sql;
    }

    public function getArguments() {
        $args = array();
        $this->_getArgumentsFromArray($this->_expression, $args);
        return $args;
    }

    private function _getArgumentsFromArray($clauses, &$args) {
        if(is_array($clauses)) {
            foreach($clauses as $clause) {
                if($clause instanceof Clause) {
                    $args[] = $clause->getValue();
                } else if (is_array($clause)) {
                    $this->_getArgumentsFromArray($clause, $args);
                }
            }
        } else if($clauses instanceof Clause) {
            $args[] = $clauses->getValue();
        }
        return $args;
    }

    public function setJqlFromSql($sql, $args) {
        $this->_sql = strtoupper($sql);
        $where = $this->_getWhereFromSql($this->_sql, $args);

        $this->_expression = $this->_seperateIntoGroups($where, false);

        if (!$this->_expression) {
            $this->_jql = "";
        } else {
            $this->_jql = $this->_expressionToJql($this->_expression);
        }
    }

    public function setSqlFromJql($jql) {
        $jql = strtoupper($jql);
        $this->_jql = $jql;

        $this->_expression = $this->_seperateIntoGroups($jql, true);

        if (!$this->_expression) {
            $this->_sql = "";
        } else {
            $this->_sql = 'SELECT * FROM "LOGS" WHERE ' . $this->_expressionToSql($this->_expression);
        }
    }

    public function getMongoCriteria() {
        return $this->_buildMongoCriteria($this->_expression);
    }

    private function _buildMongoCriteria($expression) {
        if ($expression instanceof Clause) {
            return $expression->getMongoCriteria();
        }

        $conditions = array();
        foreach($expression as $keyword => $clause) {
            $keyword = $this->_findKeyword($keyword);
            $subClauses = $this->_buildMongoCriteria($clause);

            if ($keyword instanceof JqlKeyword) {
                return array(
                    $keyword->getMongoSymbol() => $subClauses,
                );
            }

            if (is_array($clause)) {
                foreach ($clause as $subKeyWord => $subClauses) {
                    $subKeyWordObj = $this->_findKeyword($subKeyWord);
                    $conditions[] = array(
                        $subKeyWordObj->getMongoSymbol() => $this->_buildMongoCriteria($subClauses),
                    );
                }
            } else {
                $conditions[] = $clause->getMongoCriteria();
            }
        }

        return $conditions;
    }

    public function getMongoCriteriaAsString() {
        return '{ ' . $this->_buildMongoCriteria($this->_expression) . ' }';
    }

    private function _buildMongoCriteriaAsString($expression) {
        if ($expression instanceof Clause) {
            return $expression->getMongoCriteria();
        }

        $doc = '';
        foreach($expression as $keyword => $clause) {
            $keyword = $this->_findKeyword($keyword);
            $subClauses = $this->_buildMongoCriteria($clause);

            if ($keyword instanceof JqlKeyword) {
                return "'" . $keyword->getMongoSymbol() . "' : {" . $this->_buildMongoCriteria($clause)  .' }';
            }

            if (is_array($clause)) {
                foreach ($clause as $subKeyWord => $subClauses) {
                    $subKeyWordObj = $this->_findKeyword($subKeyWord);
                    $doc .= " '" . $subKeyWordObj->getMongoSymbol() . "' : '" . $this->_buildMongoCriteria($subClauses) . "' ";
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
        //there is no where clause so just return nothing.
        if (!strpos($sql, 'WHERE')) {
            return '';
        }

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
     * given a string. parse it and make it in to an array similar to the way cakePHP conditions work
     * @param $string
     * @param $jql
     *
     * @return array|bool|Clause
     */
    private function _seperateIntoGroups($string, $jql) {
        if (empty($string)) {
            return FALSE;
        }

        if (!$this->_groupsOnlyInString($string)) { //no grouping
            return $this->_seperateClauses($string, $jql);
        }


        $groups = array();

        //find any groups
        $openPos = FALSE;
        $lastChar = '';
        $i = 0;
        //a group starts with a ( which is preceded with a space or another ( anything else and it is a function
        while (!$openPos) {
            $thisChar = substr($string, $i, 1);
            if($thisChar == '(' && ($lastChar == '(' || $lastChar == ' ')) {
                $openPos = $i + 1;
            }
            $lastChar = $thisChar;
            $i++;
        }

        $close = -1;
        $groupCount = 0;
        for($i = $openPos; $i < strlen($string); $i++) {
            $char = substr($string, $i, 1);
            if($char == '(') {
                $groupCount++;
            } else if ($char == ')') {
                if ($groupCount > 0) {
                    $groupCount--;
                } else {
                    $close = $i - $openPos;
                    break;
                }
            }
        }

        //find any groups in string
        if ($close > -1) {
            $groupString = strtoupper(substr($string, $openPos, $close));
        } else {
            $groupString = strtoupper(substr($string, $openPos));
        }

        //any clauses at beginning of string
        $startOfString = '';
        $firstKeyword = 'NO KEYWORD';
        $startClause = false;
        if ($openPos > 1) {
            $startOfString = substr($string, 0, $openPos - 1);
            $firstKeyword = $this->_findFirstKeyword($string, $jql);
            if (!$firstKeyword) { //just a single clause eg: a = 1
                //throw exception there should be a clause before the first group at this point
            } else { //s  eg: a = 1 AND (b = 2) assumption there is only on
                $startClause = $this->_seperateClauses(substr($startOfString, 0, strpos($startOfString, $firstKeyword)), $jql);
            }
        }

        //there are groups inside this group
        $clauses = false;
        if ($this->_groupsOnlyInString($groupString)) {
            $subGroups = $this->_seperateIntoGroups($groupString, $jql);
            if (sizeof($subGroups) > 1) {
                $clauses[] = array($this->_findFirstKeyword($groupString, $jql) => $subGroups);
            } else {
                $clauses[array_keys($subGroups)[0]] = array_values($subGroups)[0];
            }
        }  else if ($this->_findFirstKeyword($groupString, $jql)) {
            $clauses = $this->_seperateClauses($groupString, $jql);
        } else {
            $clauses = new Clause($groupString, $jql);
        }

        if($clauses == false) {
            //wtf how did that happen?
        }

        //add group to start of string
        if($startClause) {
            if (is_array($clauses)) {
                $groups[$firstKeyword] = array($startClause, array_keys($clauses)[0] => array_values($clauses)[0]);
            } else {
                $groups[$firstKeyword] = array($startClause, $clauses);
            }
        }

        //get anything after any groups
        $groupStringLen = strlen($startOfString) + strlen($groupString) + 2; //+2 to include the ( and )
        $restOfString = substr($string, $groupStringLen);

        if (!empty($restOfString)) {
            $joiningKeyword = $this->_findFirstKeyword($restOfString, $jql);
            $clausesInRestOfString = substr($restOfString, strpos($joiningKeyword, $restOfString) + strlen($joiningKeyword));
            $clauses = $this->_seperateIntoGroups($clausesInRestOfString, $jql);
            if (isset($groups[$joiningKeyword])) {
                if (is_array($groups[$joiningKeyword])) {
                    $groups[$joiningKeyword][] = $clauses;
                } else {
                    $currentValue = $groups[$joiningKeyword];
                    $groups[$joiningKeyword] = array($currentValue, $clauses);
                }
            } else {
                $groups[$joiningKeyword] = $clauses;
            }
        }

        if (sizeof($groups) == 2) {
            if (!(is_array(array_values($clauses)[0]) && is_array(array_values($groups)[1]))) {
                return new Clause($groups, $jql);
            } else {
                return array($this->_findFirstKeyword($string, $jql) => $groups);
            }
        } else {
            return $groups;
        }
    }

    private function _seperateClauses($string, $jql) {
        if (empty($string)) {
            return FALSE;
        }

        if (!$this->_findFirstKeyword($string, $jql)) { //there are no keywords just one clause
            return new Clause($string);
        }

        //there are some keywords, seperate them
        $workingString = $string;
        $keyword = $this->_findFirstKeyword($workingString, $jql);
        $keywordPos = strpos($workingString, $keyword);
        $clauseString = substr($workingString, 0, $keywordPos);
        if (empty($clauseString)) {
            return new Clause(substr($workingString,$keywordPos + strlen($keyword)));
        }

        $clause = new Clause($clauseString);
        $workingString = substr($workingString,$keywordPos + strlen($keyword));
        $subClauses = $this->_seperateClauses($workingString, $jql);
        $clauses = array($keyword => array($clause, $subClauses));

        return $clauses;
    }

    /**
     * given a string find the keyword that is closest to the begining of the string.
     *
     * @param $string
     * @param $jql
     *
     * @return bool
     */
    private function _findFirstKeyword($string, $jql) {
        $closestKeywordPos = strlen($string);
        $closestKeyword = false;
        foreach(JqlKeyword::listKeywords() as $keyword) {
            $keywordSymbol = $keyword->getJqlSymbol();
            if (!$jql) {
                $keywordSymbol = $keyword->getSqlSymbol();
            }
            $keywordPos = strpos($string, $keywordSymbol);
            if ($keywordPos !== false && $keywordPos < $closestKeywordPos) {
                $closestKeywordPos = strpos($string, $keywordSymbol);
                $closestKeyword = $keywordSymbol;
            }
        }
        return $closestKeyword;
    }

    private function _expressionToJql($expression) {
        $jql = '';
        if ($expression instanceof Clause) {
            return $expression->getJql();
        }

        foreach($expression as $operator => $clauses) {
            if (is_array($clauses) && sizeof($clauses) == 1){
                $jql .= $this->_expressionToSql($clauses);
            } else if (is_array($clauses)) {
                $jql .= '(';

                foreach($clauses as $key => $value) {
                    $jql .= (is_array($value) ? $this->_expressionToJql(array($key =>$value)) : $value->getJql());
                    $jql .= $operator;
                }
                $jql = substr($jql, 0, strlen($jql) - strlen($operator));

                $jql .= ')';
            } else if ($clauses instanceof Clause) {
                $jql .= $clauses->getJql();
            }
        }
        return $jql;
    }

    private function _expressionToSql($expression) {
        $sql = '';
        if ($expression instanceof Clause) {
            return $sql . $expression->getSql();
        }

        foreach($expression as $operator => $clauses) {
            if (is_array($clauses) && sizeof($clauses) == 1){
                $sql .= $this->_expressionToSql($clauses);
            } else if (is_array($clauses)) {
                $sql .= '(';

                foreach($clauses as $key => $value) {
                    $sql .= (is_array($value) ? $this->_expressionToSql(array($key =>$value)) : $value->getSql());
                    $sql .= $operator;
                }
                $sql = substr($sql, 0, strlen($sql) - strlen($operator));

                $sql .= ')';
            } else if ($clauses instanceof Clause) {
                    $sql .= $clauses->getSql();
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
}