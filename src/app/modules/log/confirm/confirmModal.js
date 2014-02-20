angular.module('Preslog.log.confirmModal', [])
    .controller('ConfirmModalCtrl', function ($scope, $modalInstance, message) {
        $scope.message = message;

        $scope.ok = function() {
            //delete log

            $modalInstance.close();
        };

        $scope.cancel = function() {
            $modalInstance.dismiss('cancel');
        };
    });