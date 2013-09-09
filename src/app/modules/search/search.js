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
        'titleService'
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
    .controller( 'SearchCtrl', function LogController( $scope, $http, titleService, Restangular ) {
       titleService.setTitle( 'Search' );

       // $scope.jql = '(created > startofweek[] AND created < startofday[-1d]) AND operator not in ("jim", "pete") AND (duration > 1d AND duration < 1d10m) ';
        $scope.jql = 'id=1 and (id = 2 or (id = 3 or id = 3.1)) and name = 4 and (id =5 or id =6) and id =7';
        $scope.sql = 'SELECT * FROM "LOGS" WHERE "ID" = ?';

        $scope.args = ['1'];

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

        $scope.search = function() {
            Restangular.one('search').get({jql: $scope.jql}).then(function(logs) {
                console.log(logs);
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
           if ($scope.jql === "") {
               return;
           }
           Restangular.one('search/wizard/params').get({jql : $scope.jql}).then(function(data) {
               if (data) {
                   $scope.sql = data.sql;
                   $scope.args = data.args;
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