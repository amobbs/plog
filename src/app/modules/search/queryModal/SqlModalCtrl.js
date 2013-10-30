angular.module('Preslog.search.sqlModal', [])
    .controller('SqlModalCtrl', function ($scope, $modalInstance, sql, args, queryMeta, selectOptions) {

        $scope.sql = sql;
        $scope.args = args;
        $scope.queryMeta = queryMeta;
        $scope.selectOptions = selectOptions;

        $scope.ok = function(doSearch) {
            var result = {
                sql: $scope.sql,
                args: $scope.args,
                search: doSearch
            };
            $modalInstance.close(result);
        };

        $scope.cancel = function() {
            $modalInstance.dismiss('cancel');
        };
    });