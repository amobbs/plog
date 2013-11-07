angular.module('Preslog.dashboard.widgetModal', [])
    .controller('WidgetCtrl', function ($scope, $modalInstance, $filter, Restangular, widget, clients) {
        $scope.widget = widget;
        $scope.spanOptions = [1, 2, 3];
        $scope.clients = clients;

        $scope.queryValid = true;
        $scope.queryErrors = [];

        $scope.addChart = function(type) { //create new widget
            $scope.widget.type = type;
            $modalInstance.close($scope.widget);
        };

        $scope.saveWidget = function() { //completion of edit widget
            Restangular.one('search/validate')
                .get({'query': $scope.widget.details.query})
                .then(function (result) {
                    if (result.ok)
                    {
                        if ($scope.widget.details.start)
                        {
                            startDate = new Date($scope.widget.details.start);
                            $scope.widget.details.start = $filter('date')(startDate, 'EEE, dd MMM yyyy hh:mm:ss Z');
                        }

                        if ($scope.widget.details.end)
                        {
                            endDate = new Date($scope.widget.details.end);
                            $scope.widget.details.end = $filter('date')(endDate, 'EEE, dd MMM yyyy hh:mm:ss Z');
                        }

                        $modalInstance.close($scope.widget);
                    }
                    else
                    {
                       $scope.queryValid = false;
                       $scope.queryErrors = result.errors;
                    }
                });
        };

        $scope.cancel = function() {
            $modalInstance.dismiss('cancel');
        };
    });