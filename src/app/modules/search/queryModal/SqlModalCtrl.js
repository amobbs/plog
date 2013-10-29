angular.module('Preslog.search.sqlModal', [])
    .controller('SqlModalCtrl', function ($scope, $modalInstance, sql, args, queryMeta, selectOptions) {
        $scope.sql = sql;
        $scope.sqlreturn = '';
        $scope.args = args;
        $scope.queryMeta = queryMeta;
        $scope.selectOptions = selectOptions;

        $scope.ok = function() {
            var result = {
                sql: $scope.sql,
                args: $scope.args
            };
            $modalInstance.close(result);
        };

        $scope.cancel = function() {
            $modalInstance.dismiss('cancel');
        };
    });