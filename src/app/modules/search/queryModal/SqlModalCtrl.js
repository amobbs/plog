angular.module('Preslog.search.sqlModal', [])
    .controller('SqlModalCtrl', function ($scope, $modalInstance, sql, args, queryMeta) {
        $scope.sql = sql;
        $scope.args = args;
        $scope.queryMeta = queryMeta;


        $scope.ok = function() {
            $modalInstance.close($scope.sql);
        };

        $scope.cancel = function() {
            $modalInstance.dismiss('cancel');
        };
    });