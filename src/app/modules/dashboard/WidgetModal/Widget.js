var WidgetCtrl = function WidgetController($scope, $modalInstance, data) {
    $scope.data = data;

    $scope.addChart = function(type) { //create new widget
        $scope.data.type = type;
        $modalInstance.close($scope.data);
    };

    $scope.saveWidget = function() { //completion of edit widget
        $modalInstance.close($scope.data);
    };

    $scope.cancel = function() {
        $modalInstance.dismiss('cancel');
    };
};