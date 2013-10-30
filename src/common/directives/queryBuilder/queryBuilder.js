/**
 * Red Query builder, places a div that can then be used to graphically build sql queries
 * http://redquerybuilder.appspot.com/
 */
angular.module('redQueryBuilder', [])
.directive('redQueryBuilder', [
    function() {
        return {
            //element only
            restrict:'E',

            transclude: true,

            //data binding
            scope: {
                sql: '=',
                args: '=',
                queryMeta: '=',
                selectOptions: '='
            },

            link : function(scope, element, attrs) {
                scope.element = element;

                RedQueryBuilderFactory.create({
                        targetId : 'rqb',
                        meta : scope.queryMeta,
                        onSqlChange : function(sql, args) {
                            //$parent.$parent - i'm sorry, fix to get the directive to respond to correct scope.
                            scope.$parent.$parent.sql = sql;
                            scope.$parent.$parent.args = args;
                        },
                        enumerate : function(request, response) {
                            if (!scope.selectOptions[request.columnName])
                            {
                                response([{value: -1, label: 'Error retrieving list'}]);
                                return;
                            }
                            response(scope.selectOptions[request.columnName]);
                        },
                        editors : [ {
                            name : 'DATE',
                            format : 'yyyy/MM/dd'
                        } ],
                        suggest: function(args, callback) {
                            console.log(args);
                        }
                    },
                    scope.sql,
                    scope.args);
            }
        };
    }
]);