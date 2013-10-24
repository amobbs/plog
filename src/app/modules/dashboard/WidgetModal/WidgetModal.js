angular.module('Preslog.dashboard.widgetModal', [])
    .controller('WidgetCtrl', function ($scope, $modalInstance, widget, clients) {
        $scope.widget = widget;
        $scope.spanOptions = [1, 2, 3];
        $scope.clients = clients;

        $scope.addChart = function(type) { //create new widget
            $scope.widget.type = type;
            $modalInstance.close($scope.widget);
        };

        $scope.saveWidget = function() { //completion of edit widget
            $modalInstance.close($scope.widget);
        };

        $scope.cancel = function() {
            $modalInstance.dismiss('cancel');
        };
    });