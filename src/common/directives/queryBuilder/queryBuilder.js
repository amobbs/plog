/**
 * Red Query builder, places a div that can then be used to graphically build sql queries
 * http://redquerybuilder.appspot.com/
 */
angular.module('redQueryBuilder', [])
.directive('redQueryBuilder', [
    function() {
        return {
            //element only
            restrict:'EA',

            //data binding
            scope: {
                sql: '=',
                args: '=',
                queryMeta: '=',
                selectOptions: '='
            },

            link : function(scope, element, attrs) {

                // TODO: Watch scope args and reinit RedQueryBuilder with a new copy on change

                var sql = angular.copy(scope.sql);
                var args = angular.copy(scope.args);

                RedQueryBuilderFactory.create({
                        targetId : 'rqb',
                        meta : scope.queryMeta,
                        onLoad: function() {
                        },
                        onSqlChange : function(sql, args) {
                            scope.sql = sql;
                            scope.args = args;

                            scope.$apply();
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
                            format : 'dd/MM/yyyy'
                        } ],
                        suggest: function(args, callback) {
                        }
                    },
                    sql,
                    args);
            }
        };
    }
]);