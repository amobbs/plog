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
                    config: '=config',
                    sql: '=sql',
                    args: '=args'
                },
                template: '<div class="queryBuilderContainer" id="rqb"></div>',
                controller: function($scope, $element) {
                    if (typeof $scope.sql === 'undefined') {
                        $scope.sql = '';
                    }

                    if (typeof $scope.args === 'undefined') {
                        $scope.args = [];
                    }

                    createQueryBuilder($scope, $element);
                }
            };
        }
    ]);