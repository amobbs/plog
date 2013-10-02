angular.module('Preslog.search.sqlModal', [])
    .controller('SqlModalCtrl', function ($scope, $modalInstance, jql) {
        $scope.jql = jql;


        $scope.ok = function() {
            $modalInstance.close($scope.sql);
        };

        $scope.cancel = function() {
            $modalInstance.dismiss('cancel');
        };
    });