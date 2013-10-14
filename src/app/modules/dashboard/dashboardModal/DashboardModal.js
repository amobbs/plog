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
        $scope.share ={};

        $scope.ok = function() {
            $modalInstance.close({'name': $scope.name, 'share': $scope.share});
        };

        $scope.cancel = function() {
            $modalInstance.dismiss('cancel');
        };
    });