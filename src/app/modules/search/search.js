/**
 * Each section of the site has its own module. It probably also has
 * submodules, though this boilerplate is too simple to demonstrate it. Within
 * `src/app/home`, however, could exist several additional folders representing
 * additional modules that would then be listed as dependencies of this one.
 * For example, a `note` section could have the submodules `note.create`,
 * `note.delete`, `note.edit`, etc.
 *
 * Regardless, so long as dependencies are managed correctly, the build process
 * will automatically take take of the rest.
 *
 * The dependencies block here is also where component dependencies should be
 * specified, as shown below.
 */
angular.module( 'Preslog.search', [
        'titleService',
        'Preslog.search.sqlModal'
    ])

    .config(function(stateHelperProvider) {
        stateHelperProvider.addState('mainLayout.search', {
            url: '/search',
            views: {
                "main@mainLayout": {
                    controller: 'SearchCtrl',
                    templateUrl: 'modules/search/search.tpl.html'
                }
            }
        });
    })

/**
 * And of course we define a controller for our route.
 */
    .controller( 'SearchCtrl', function LogController( $scope, $http, $modal, titleService, Restangular ) {
       titleService.setTitle( 'Search' );

       // $scope.jql = '(created > startofweek[] AND created < startofday[-1d]) AND operator not in ("jim", "pete") AND (duration > 1d AND duration < 1d10m) ';
        //$scope.jql = 'id=1 and (id = 2 or (id = 3 or id = 3.1))';
        $scope.jql = 'hrid=1 or hrid=1234';
        $scope.sql = 'SELECT * FROM "LOGS" WHERE "ID" = ?';

        $scope.args = ['1'];
        $scope.results = {
            data: [],
            fields: []
        };

        $scope.logWidgetParams = {
            page: 1,
            total: 0,
            perPageOptions: [3, 5, 10, 20],
            perPage: 3,
            sorting: {
                name: 'created'
            },
            query: '',
            logs: [],
            lastUpdated: new Date()
        };

        $scope.$watch('logWidgetParams', function(params) {
            $scope.search();
        }, true);

        $scope.queryMeta = function() {
            return {
                "tables": [
                    {
                        "name": "LOGS",
                        "columns": [
                            {
                                "name": "ID",
                                "label": "ID",
                                "type": "INTEGER",
                                "size": 10
                            },
                            {
                                "name": "NAME",
                                "label": "NAME",
                                "type": "CHAR",
                                "size": 35
                            },
                            {
                                "name": "COUNTRYCODE",
                                "label": "COUNTRYCODE",
                                "type": "CHAR",
                                "size": 3
                            },
                            {
                                "name": "DISTRICT",
                                "label": "DISTRICT",
                                "type": "CHAR",
                                "size": 20
                            },
                            {
                                "name": "POPULATION",
                                "label": "POPULATION",
                                "type": "INTEGER",
                                "size": 10
                            }
                        ],
                        "fks": []
                    }
                ],
                "types": [
                    {
                        "editor": "SUGGEST",
                        "name": "CHAR",
                        "operators": [
                            {
                                "name": "=",
                                "label": "is",
                                "cardinality": "ONE"
                            },
                            {
                                "name": "<>",
                                "label": "is not",
                                "cardinality": "ONE"
                            },
                            {
                                "name": "LIKE",
                                "label": "like",
                                "cardinality": "ONE"
                            },
                            {
                                "name": "<",
                                "label": "less than",
                                "cardinality": "ONE"
                            },
                            {
                                "name": ">",
                                "label": "greater than",
                                "cardinality": "ONE"
                            }
                        ]
                    },
                    {
                        "editor": "TEXT",
                        "name": "NUMERIC",
                        "operators": [
                            {
                                "name": "=",
                                "label": "is",
                                "cardinality": "ONE"
                            },
                            {
                                "name": "<>",
                                "label": "is not",
                                "cardinality": "ONE"
                            },
                            {
                                "name": "<",
                                "label": "less than",
                                "cardinality": "ONE"
                            },
                            {
                                "name": ">",
                                "label": "greater than",
                                "cardinality": "ONE"
                            }
                        ]
                    },
                    {
                        "editor": "TEXT",
                        "name": "INTEGER",
                        "operators": [
                            {
                                "name": "=",
                                "label": "is",
                                "cardinality": "ONE"
                            },
                            {
                                "name": "<>",
                                "label": "is not",
                                "cardinality": "ONE"
                            },
                            {
                                "name": "<",
                                "label": "less than",
                                "cardinality": "ONE"
                            },
                            {
                                "name": ">",
                                "label": "greater than",
                                "cardinality": "ONE"
                            }
                        ]
                    },
                    {
                        "editor": "TEXT",
                        "name": "DECIMAL",
                        "operators": [
                            {
                                "name": "=",
                                "label": "is",
                                "cardinality": "ONE"
                            },
                            {
                                "name": "<>",
                                "label": "is not",
                                "cardinality": "ONE"
                            },
                            {
                                "name": "<",
                                "label": "less than",
                                "cardinality": "ONE"
                            },
                            {
                                "name": ">",
                                "label": "greater than",
                                "cardinality": "ONE"
                            }
                        ]
                    },
                    {
                        "editor": "TEXT",
                        "name": "SMALLINT",
                        "operators": [
                            {
                                "name": "=",
                                "label": "is",
                                "cardinality": "ONE"
                            },
                            {
                                "name": "<>",
                                "label": "is not",
                                "cardinality": "ONE"
                            },
                            {
                                "name": "<",
                                "label": "less than",
                                "cardinality": "ONE"
                            },
                            {
                                "name": ">",
                                "label": "greater than",
                                "cardinality": "ONE"
                            }
                        ]
                    },
                    {
                        "editor": "SELECT",
                        "name": "BOOLEAN",
                        "operators": [
                            {
                                "name": "=",
                                "label": "is",
                                "cardinality": "ONE"
                            }
                        ]
                    }
                ]
            };
        };

        $scope.doSearch = function() {
            $scope.logWidgetParams.page = 1;
            $scope.search();
        };

        $scope.search = function() {
            if ($scope.jql.length  === 0) {
                return;
            }

            var offset = (($scope.logWidgetParams.page - 1) * $scope.logWidgetParams.perPage);
            if ($scope.logWidgetParams.page === 1) {
                offset = 0;
            }
            Restangular.one('search').get({query: $scope.jql, limit: $scope.logWidgetParams.perPage, start: offset}).then(function(result) {
                console.log(result);
                $scope.results = result;
                var params = angular.copy($scope.logWidgetParams);
                params.total = result.total;
                params.logs = result.logs;
                $scope.logWidgetParams = params;
            });
        };

        $scope.sqlToJql = function() {
            if ($scope.sql === "") {
                return;
            }
            Restangular.one('search/wizard/translate').get({sql : $scope.sql, args : JSON.stringify($scope.args)}).then(function(data) {
                if (data) {
                    $scope.jql = data.jql;
                    $scope.args = data.args;
                }
            });
        };

       $scope.jqlToSql = function() {
           Restangular.one('search/wizard/params')
               .get({jql : $scope.jql})
               .then(function(data) {
                   if (data) {
                       $scope.sql = data.sql;
                       $scope.args = data.args;

                       var modal = $modal.open({
                           templateUrl: 'modules/search/queryModal/sqlQueryModal.tpl.html',
                           controller: 'SqlModalCtrl',
                           resolve: {
                                sql: function() { return $scope.sql; },
                                args: function() { return $scope.args; }
                           }
                       });
                       modal.result.then(function(jql) {

                       });

                   }
               });

       };

    })
    .directive('redQueryBuilder', ['$timeout', 'Restangular',
        function($timeout, Restangular) {
            return {
                link : function(scope, element, attrs) {
                    if (scope.jql === "") {
                        return;
                    }
                    Restangular.one('search/wizard/params').get({jql : scope.jql}).then(function(data) {
                        if (data) {
                            scope.sql = data.sql;
                            scope.args = data.args;

                            $timeout(function() {
                                RedQueryBuilderFactory.create({
                                        targetId : 'rqb',
                                        meta : scope.queryMeta(),
                                        onSqlChange : function(sql, args) {
                                            scope.sql = sql;
                                            scope.args = args;
                                        },
                                        enumerate : function(request, response) {
                                            if (request.columnName == 'CATEGORY') {
                                                response([{value:'A', label:'Small'}, {value:'B', label:'Medium'}]);
                                            } else {
                                                response([{value:'M', label:'Male'}, {value:'F', label:'Female'}]);
                                            }
                                        },
                                        editors : [ {
                                            name : 'DATE',
                                            format : 'dd.MM.yyyy'
                                        } ],
                                        suggest: function(args, callback) {
                                            console.log(args);
                                        }
                                    },
                                    scope.sql,
                                    scope.args);
                            }, 100);
                        }
                    });
                }
            };
        }
    ]);