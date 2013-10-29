angular.module('Preslog.search.sqlModal', [])
    .controller('SqlModalCtrl', function ($scope, $modalInstance, sql, args, queryMeta, selectOptions) {
        $scope.sql = sql;
        $scope.args = args;
        $scope.queryMeta = queryMeta;
        $scope.selectOptions = selectOptions;


        $scope.ok = function() {
            $modalInstance.close($scope.sql);
        };

        $scope.cancel = function() {
            $modalInstance.dismiss('cancel');
        };
    });