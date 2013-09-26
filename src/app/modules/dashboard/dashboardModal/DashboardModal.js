angular.module('Preslog.dashboard.dashboardModal', [])
    .controller('DashboardModalCtrl', function ($scope, $modalInstance) {
        $scope.name = '';

        $scope.ok = function() {
            $modalInstance.close($scope.name);
        };

        $scope.cancel = function() {
            $modalInstance.dismiss('cancel');
        };
    });