angular.module('Preslog.dashboard.dashboardModal', [])
    .controller('DashboardModalCtrl', function ($scope, $modalInstance, name, isCreate, clients) {
        $scope.name = name;
        if (isCreate) {
            $scope.actionName = 'Create Dashboard';
        } else {
            $scope.actionName = 'Save Changes';
        }
        $scope.isCreate = isCreate;
        $scope.clients = clients;
        $scope.share ={};

        $scope.ok = function() {
            $modalInstance.close({
                name: $scope.name,
                shares: $scope.share
            });
        };

        $scope.cancel = function() {
            $modalInstance.dismiss('cancel');
        };
    });