/**
 * Relies on jQuery 1.9.1+
 */
angular.module('queryBuilder', [])
    .directive('queryBuilder', [
        function () {
            var restyleQueryBuilder = function(element) {
                    element.find('button.gwt-Button').filter(function() {
                        return $(this).text() === '-';
                    }).addClass('btn btn-mini btn-danger').removeClass('gwt-Button');
                    element.find('button.gwt-Button').filter(function() {
                        return $(this).text() === '+';
                    }).addClass('btn btn-mini btn-success').removeClass('gwt-Button');
                    element.find('button.gwt-Button').filter(function() {
                       return $(this).text() === 'Add condition';
                    }).addClass('btn btn-primary').removeClass('gwt-Button');
                },
                createQueryBuilder = function($scope, $element) {
                    $element.find('#rqb').empty();
                    RedQueryBuilderFactory.create({
                        targetId: 'rqb',
                        meta: $scope.config.meta,
                        onLoad: function() {
                            $element.find('select.rqbWhat option:eq(1)')
                                    .attr('selected', 'selected')
                                    .parent()
                                .trigger('change')
                                .hide();

                            if ($scope.sql === '') {
                                $element.find('button.gwt-Button').filter(function() {
                                    return $(this).text() == 'Add condition';
                                }).trigger('click');
                            }

                            restyleQueryBuilder($element);
                        },
                        onSqlChange: function (sql, args) {
                            $scope.sql = sql;
                            $scope.args = args;

                            // Make sure the digest is not running
                            if (! $scope.$root.$$phase) {
                                $scope.$apply();
                            }

                            restyleQueryBuilder($element);
                        },
                        enumerate: $scope.config.enumerate,
                        editors: $scope.config.editors
                    }, $scope.sql, $scope.args);
                };

            return {
                scope: {

                },
                template: '<div class="queryBuilderContainer" id="rqb"></div>',
                controller: function($scope, $element, $timeout) {
                    if (typeof $scope.sql === 'undefined') {
                        $scope.sql = '';
                    }

                    if (typeof $scope.args === 'undefined') {
                        $scope.args = [];
                    }

                    // :DEBUG:
                    $scope.config = {};
                    $scope.config.meta = {
                            tables : [ {
                                "name" : "PERSON",
                                "label" : "Person",
                                "columns" : [ {
                                    "name" : "NAME",
                                    "label" : "Name",
                                    "type" : "STRING",
                                    "size" : 10
                                }, {
                                    "name" : "DOB",
                                    "label" : "Date of birth",
                                    "type" : "DATE"
                                }, {
                                    "name" : "SEX",
                                    "label" : "Sex",
                                    "type" : "STRING",
                                    "editor" : "SELECT"
                                }, {
                                    "name" : "CATEGORY",
                                    "label" : "Category",
                                    "type" : "REF"
                                }  ],
                                fks : []
                            } ],

                            types : [ {
                                "name" : "STRING",
                                "editor" : "TEXT",
                                "operators" : [ {
                                    "name" : "=",
                                    "label" : "is",
                                    "cardinality" : "ONE"
                                }, {
                                    "name" : "<>",
                                    "label" : "is not",
                                    "cardinality" : "ONE"
                                }, {
                                    "name" : "LIKE",
                                    "label" : "like",
                                    "cardinality" : "ONE"
                                }, {
                                    "name" : "<",
                                    "label" : "less than",
                                    "cardinality" : "ONE"
                                }, {
                                    "name" : ">",
                                    "label" : "greater than",
                                    "cardinality" : "ONE"
                                } ]
                            }, {
                                "name" : "DATE",
                                "editor" : "DATE",
                                "operators" : [ {
                                    "name" : "=",
                                    "label" : "is",
                                    "cardinality" : "ONE"
                                }, {
                                    "name" : "<>",
                                    "label" : "is not",
                                    "cardinality" : "ONE"
                                }, {
                                    "name" : "<",
                                    "label" : "before",
                                    "cardinality" : "ONE"
                                }, {
                                    "name" : ">",
                                    "label" : "after",
                                    "cardinality" : "ONE"
                                } ]
                            }, {
                                "name" : "REF",
                                "editor" : "SELECT",
                                "operators" : [ {
                                    "name" : "IN",
                                    "label" : "any of",
                                    "cardinality" : "MULTI"
                                }]
                            }  ]
                        };

                    $scope.config.enumerate = function(request, response) {
                        if (request.columnName == 'CATEGORY') {
                            response([{value:'A', label:'Small'}, {value:'B', label:'Medium'}]);
                        } else {
                            response([{value:'M', label:'Male'}, {value:'F', label:'Female'}]);
                        }
                    };

                    $scope.config.editors = [ {
                        name : 'DATE',
                        format : 'dd.MM.yyyy'
                    } ];

                    // :HACK: T:DEBUG: this si but debugging only. Scrap this.
                    $scope.delay = $timeout(function()
                    {
                        createQueryBuilder($scope, $element);
                    }, 100);

                }
            };
        }
    ]);