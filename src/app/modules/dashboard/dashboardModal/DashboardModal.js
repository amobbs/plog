angular.module('Preslog.dashboard.dashboardModal', [])
    .controller('DashboardModalCtrl', function ($scope, $modalInstance, name) {
        $scope.name = name;

        $scope.ok = function() {
            $modalInstance.close($scope.name);
        };

        $scope.cancel = function() {
            $modalInstance.dismiss('cancel');
        };
    });