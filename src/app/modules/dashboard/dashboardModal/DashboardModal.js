angular.module('Preslog.dashboard.dashboardModal', [])
    .controller('DashboardModalCtrl', function ($scope, $modalInstance, name, isCreate, clients) {
        $scope.name = name;
        if (isCreate) {
            $scope.actionName = 'Create';
        } else {
            $scope.actionName = 'Update';
        }
        $scope.isCreate = isCreate;
        $scope.clients = clients;


        $scope.ok = function() {
            $modalInstance.close($scope.name);
        };

        $scope.cancel = function() {
            $modalInstance.dismiss('cancel');
        };
    });