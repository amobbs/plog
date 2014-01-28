<?php

namespace Preslog\JqlParser;

use Exception;
use Preslog\JqlParser\JqlFunction\JqlFunction as JqlFunction;
use Preslog\JqlParser\JqlKeyword\JqlKeyword;
use Preslog\JqlParser\JqlOperator\JqlOperator;

class JqlParser {

    /**
     * jql version of expression
     * @var string
     */
    protected $_jql = '';

    /**
     * sql version of expression
     * @var string
     */
    protected $_sql = '';

    /**
     * generic version of expression
     * @var array
     */
    protected $_expression = array();

    protected $_errors = array();


    public function getJql() {
        return $this->_jql;
    }

    public function getSql() {
        return $this->_sql;
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * populate an array with all the values found in the expression
     *
     * @param bool $evaluateFunction - if true run any functions found in the arguments
     *
     *
     * @return array
     */
    public function getArguments($evaluateFunction = false) {
        $args = array();
        $this->_getArgumentsFromArray($this->_expression, $args, $evaluateFunction);
        return $args;
    }

    /**
     * recursively populate array with all values found in an expression
     *
     * @param $clauses
     * @param $args
     *
     * @param $evaluateFunction - if true run any functions found in the arguments
     *
     * @return mixed
     */
    private function _getArgumentsFromArray($clauses, &$args, $evaluateFunction) {
        if(is_array($clauses)) {
            foreach($clauses as $clause) {
                if($clause instanceof Clause) {
                    $this->_addValue($clause, $args, $evaluateFunction);
                } else if (is_array($clause)) {
                    $this->_getArgumentsFromArray($clause, $args, $evaluateFunction);
                }
            }
        } else if($clauses instanceof Clause) {
            $this->_addValue($clauses, $args, $evaluateFunction);
        }
        return $args;
    }

    /**
     * add the data type onto the value so that we know how to format it on the other end
     *
     * @param $clause
     * @param $args
     */
    private function _addValue($clause, &$args, $evaluateFunction = false)
    {
        $arg = array(
            'type' => 'string',
            'value' => $clause->getValue(),
        );

        if ($evaluateFunction)
        {
            $arg['value'] = $clause->getFunctionEvaluated();
            if (is_numeric($arg['value'])) //all the function either return boolean or date functions. if its a number it is really a date
            {
                $arg['type'] = 'date';
            }
        }

        if (strtotime($arg['value']))
        {
            $arg['type'] = 'date';
        }
        $args[] = $arg;
    }

    /**
     * Given an sql string, split up the expression in to logical groups and then into seperate clauses.
     * once done output the expression as jql
     * @param $sql
     * @param $args
     */
    public function setJqlFromSql($sql, $args) {
        $this->_sql = strtoupper($sql);
        $where = $this->_getWhereFromSql($this->_sql, $args);

        try
        {
            $this->_expression = $this->_seperateIntoGroups($where, false);
        }
        catch(Exception $e)
        {
            $this->_errors = array($e->getMessage());
        }

        if (!$this->_expression) {
            $this->_jql = "";
        } else {
            $this->_jql = $this->_expressionToJql($this->_expression);
        }
    }

    /**
     * given a jql string split up the expression in to logical groups and then into seperate clauses.
     * once done output the expression as sql
     * @param $jql
     */
    public function setSqlFromJql($jql) {
        if (empty($jql)) {
            $this->_sql = 'SELECT * FROM "LOGS"';
            return;
        }

        $jql = strtoupper($jql);

        $jql = str_replace("\n", ' ', $jql);

        $this->_jql = $jql;

        try
        {
            $this->_expression = $this->_seperateIntoGroups($jql, true);
        }
        catch(Exception $e)
        {
            $this->_errors = array($e->getMessage());
        }

        if (!$this->_expression) {
            $this->_sql = "";
        } else {
            $this->_sql = 'SELECT * FROM "LOGS" WHERE ' . $this->_expressionToSql($this->_expression);
        }
    }

    /**
     * given the expression this object holds out put an array that can be used to match in mongo
     * @return array
     */
    public function getMongoCriteria() {
        return $this->_buildMongoCriteria($this->_expression);
    }

    private function _buildMongoCriteria($expression) {
        if (!$expression) {
            return array();
        }

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

    /***
     * find all the mongo id's for fields in the query and return as string
     *
     * @return array
     */
    public function getFieldList() {

        $fieldList = array();
        $this->_getFields($this->_expression, $fieldList);
        return $fieldList;
    }

    /***
     * recursivly find all the fields in the expression
     *
     * @param $clauses
     * @param $fieldList
     *
     * @return array
     */
    private function _getFields($clauses, &$fieldList) {
        if(is_array($clauses)) {
            foreach($clauses as $clause) {
                if($clause instanceof Clause) {
                    $fieldList[] = $clause->getField();
                } else if (is_array($clause)) {
                    $this->_getFields($clause, $fieldList);
                }
            }
        } else if($clauses instanceof Clause) {
            $fieldList[] = $clauses->getField();
        }

        return $fieldList;
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

    public function validate()
    {
        return $this->_validateExpression($this->_expression);
    }

    protected function _validateExpression($expression)
    {
        if ( $expression instanceof Clause )
        {
            return $this->_validateClause($expression);
        }

        if ( ! is_array($expression) )
        {
            return array('Malformed query');
        }

        $errors = array();

        foreach( $expression as $clause )
        {
            if ($clause instanceof Clause)
            {
                $errors = array_merge($this->_validateClause($clause), $errors);
            }
            else
            {
                $errors = array_merge($this->_validateExpression($clause), $errors);
            }
        }

        return $errors;
    }

    protected function _validateClause($clause)
    {
        $errors = array();

        //check the functions exist

        return $errors;
    }


    /**
     * given a string. parse it and make it in to an array similar to the way cakePHP conditions work
     * @param $string
     * @param $jql
     *
     * @return array|bool|Clause
     */
    private function _seperateIntoGroups($string, $jql) {
        //there are no groups to find
        if (empty($string)) {
            return FALSE;
        }

        //there are no groups in this string ( or )
        if (!$this->_groupsOnlyInString($string)) {
            return $this->_seperateClauses($string, $jql);
        }


        //ok so now we know there are some groups we need to separate all the groups
        $groups = array();

        $openPos = FALSE; //the position of last ( we find
        $quoteCount = 0; //counting how many " there are to make sure we ignore group start/end and keywords inside quotes

        $lastChar = ' '; //the last character we looked at, start with white space so if the first character is a ( we recognize it as the begining of a group
        $thisChar = ''; //the current character we are looking at,
        $i = 0; //position we are up to in the string

        //a group starts with a ( which is preceded with a space or another (, anything else and it is a function
        //loop through each character in the string to find groups
        while ( ! $openPos && $thisChar !== false) {
            $thisChar = substr($string, $i, 1);
            if($thisChar == '(' &&
                ($lastChar == '(' || $lastChar == ' ') &&   //if it dosn't have a space or  ( in front it is a function
                ($quoteCount % 2 == 0) //if there are an odd number of quotes before this character we are inside quotes and it is a literal string
            )
            {
                $openPos = $i + 1; //record the position
            }

            if ($thisChar == '"') //increment quote count, so we can mod it later
            {
                $quoteCount++;
            }
            $lastChar = $thisChar;
            $i++;
        }

        //find corrosponding close ) and count number of groups in the string
        $close = -1;
        $groupCount = 0;
        if ($openPos !== false) //if there are no opens no need to find a close.
        {
            for($i = $openPos; $i < strlen($string); $i++) {
                $char = substr($string, $i, 1);

                if ($char == '"')
                {
                    $quoteCount++;
                }

                //if there are an odd number of quotes then we are inside a string literal and we will ignore open/closes
                if ($quoteCount % 2 != 0)
                {
                    continue;
                }

                if($char == '(')
                {
                    $groupCount++;
                }
                else if ($char == ')')
                {
                    if ($groupCount > 0)
                    {
                        $groupCount--;
                    }
                    else
                    {
                        $close = $i - $openPos;
                        break;
                    }
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
        if ($openPos > 1) { //openPos will be greater then one if we found some groups
            $startOfString = substr($string, 0, $openPos - 1); //grab anything before the first (
            $firstKeyword = $this->_findFirstKeyword($string, $jql);

            //just a single clause eg: a = 1
            if (!$firstKeyword)
            {
                //throw exception there should be a clause before the first group at this point
            }
            //eg: a = 1 AND (b = 2) assumption there is only one clause before the group
            else
            {
                $startClause = $this->_seperateClauses(substr($startOfString, 0, strpos($startOfString, $firstKeyword)), $jql);
            }
        }

        //there are groups inside this group
        $clauses = false;
        if ($this->_groupsOnlyInString($groupString))
        {
            $subGroups = $this->_seperateIntoGroups($groupString, $jql);
            if (sizeof($subGroups) > 1)
            {
                $clauses[] = array($this->_findFirstKeyword($groupString, $jql) => $subGroups);
            }
            else
            {
                $subGroupKeys = array_keys($subGroups);
                $subGroupValues = array_values($subGroups);
                $clauses[$subGroupKeys[0]] = $subGroupValues[0];
            }
        }
        //no group but there are a couple of clauses eg: a = 1 and b = 2
        else if ($this->_findFirstKeyword($groupString, $jql))
        {
            $clauses = $this->_seperateClauses($groupString, $jql);
        }
        //only one clause found
        else
        {
            $clauses = new Clause($groupString, $jql);
        }

        //we found there was something before the group, so include that into the output
        if($startClause)
        {
            //there are many clauses, add them into the groups in the correct format
            if (is_array($clauses))
            {
                $clausesKeys = array_keys($clauses);
                $clausesValues = array_values($clauses);
                $groups[$firstKeyword] = array($startClause, array($clausesKeys[0] => $clausesValues[0]));
            }
            //just one clause, add to group in the correct format
            else
            {
                $groups[$firstKeyword] = array($startClause, $clauses);
            }
        }
        else if ($clauses instanceof Clause)
        {
            $groups = array(' AND ' => $clauses);
        }
        //no start clause just the groups, already in correct format so just set
        else
        {
            $groups = $clauses;
        }

        //get anything after any groups
        $groupStringLen = strlen($startOfString) + strlen($groupString) + 2; //+2 to include the ( and )
        $restOfString = substr($string, $groupStringLen);

        if (!empty($restOfString))
        {
            $joiningKeyword = $this->_findFirstKeyword($restOfString, $jql);
            $clausesInRestOfString = substr($restOfString, strpos($joiningKeyword, $restOfString) + strlen($joiningKeyword));
            $clauses = $this->_seperateIntoGroups($clausesInRestOfString, $jql);
            //we need to make sure that if they keyword is already used in this group we add the clause to the existing keyword and do not overwrite it.
            if (isset($groups[$joiningKeyword]))
            {
                if (is_array($groups[$joiningKeyword]))
                {
                    $groups[$joiningKeyword][] = $clauses;
                }
                else
                {
                    $currentValue = $groups[$joiningKeyword];
                    $groups[$joiningKeyword] = array($currentValue, $clauses);
                }
            }
            //this keyword has not been used in the group before so add it in.
            else
            {
                $groups[$joiningKeyword] = $clauses;
            }
        }

        //i have no idea why we are looking for groups that are the size of 2. as far as i can tell it is something to do with formatting the output if there is an OR + AND in the array
        if (sizeof($groups) == 2)
        {
            $clausesValues = array_values($clauses);
            $groupValues = array_values($groups);
            if (!(is_array($clausesValues[0]) && is_array($groupValues[1])))
            {
                return new Clause($groups, $jql);
            }
            else
            {
                return array(
                    array($this->_findFirstKeyword($string, $jql) => $groups)
                );
            }
        }
        else
        {
            return $groups;
        }
    }

    private function _seperateClauses($string, $jql) {
        if (empty($string)) {
            return FALSE;
        }

        if (!$this->_findFirstKeyword($string, $jql)) { //there are no keywords just one clause
            return new Clause($string, $jql);
        }

        //there are some keywords, seperate them
        $workingString = $string;
        $keyword = $this->_findFirstKeyword($workingString, $jql);
        $keywordPos = strpos($workingString, $keyword);
        $clauseString = substr($workingString, 0, $keywordPos);
        if (empty($clauseString)) {
            return new Clause(substr($workingString,$keywordPos + strlen($keyword)));
        }

        $clause = new Clause($clauseString, $jql);
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

            $offset = 0;
            $continue = true;
            while ($continue)
            {
                $keywordPos = strpos($string, $keywordSymbol, $offset);
                if ($keywordPos !== false && $keywordPos < $closestKeywordPos) {
                    //we found a keyword make sure it is not inside some quotes

                    //keyword is at start of string there are no quotes in front
                    if ($keywordPos == 0)
                    {
                        $quoteCount = 0;
                    }
                    //keyword has at least one character in front which might be a"
                    else
                    {
                        $quoteCount = substr_count($string, '"', 0, $keywordPos);
                    }

                    //we determine that it is in quotes if there is an odd number of " before the keyword
                    if ($quoteCount % 2 == 0)
                    {
                        $closestKeywordPos = strpos($string, $keywordSymbol);
                        $closestKeyword = $keywordSymbol;
                        $continue = false;
                    }
                    else
                    {
                        $offset = $keywordPos + 1;
                        $continue = true;
                    }
                }
                //the keyword was not in the string, stop looking
                else
                {
                    $continue = false;
                }
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
                $jql .= $this->_expressionToJql($clauses);
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
     * this will ignore any ( or ) inside quotes (string literals)
     * @param $string
     *
     * @return bool
     */
    private function _groupsOnlyInString($string) {
        $groupStartCount = 0;
        $quoteCount = 0;
        for($i = 0; $i < strlen($string); $i++)
        {
            $char = substr($string, $i, 1);
            if ($char == '"')
            {
                $quoteCount++;
            }

            if (
                $char == '(' && //new group!!
                (substr($string, $i, $i - 3) !== 'IN(' ||  //nope not a new group just an IN
                substr($string, $i, $i - 4) !== 'IN (') &&
                ($quoteCount % 2 == 0) //nope not a new group it is in a string literal
            )
            {
                $groupStartCount++;
            }
        }

        $functionCount = $this->_countFunctionsInString($string);

        if ($groupStartCount > $functionCount) {
            return true;
        }

        return false;
    }

    private function _countFunctionsInString($string) {
        $count = 0;
        foreach (JqlFunction::listFunctions() as $function) {
            $count += substr_count(strtoupper($string), $function->getName() . '(');
        }
        return $count;
    }

    protected function _findKeyword($keywordSymbol, $jqlSymbol = TRUE) {
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