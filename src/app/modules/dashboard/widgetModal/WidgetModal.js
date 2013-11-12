angular.module('Preslog.dashboard.widgetModal', [])
    .controller('WidgetCtrl', function ($scope, $modalInstance, $filter, Restangular, widget, clients) {
        $scope.widget = widget;
        $scope.spanOptions = [1, 2, 3];
        $scope.clients = clients;

        $scope.queryValid = true;
        $scope.refreshValid = true;
        $scope.queryErrors = [];

        $scope.addChart = function(type) { //create new widget
            $scope.widget.type = type;
            $modalInstance.close($scope.widget);
        };

        $scope.saveWidget = function() { //completion of edit widget
            if ($scope.widget.details.refresh && $scope.widget.details.refresh < 1)
            {
                $scope.refreshValid = false;
                $scope.queryErrors = ['Refresh interval can not be below 1 minute.'];
                return;
            }

            Restangular.one('search/validate')
                .get({'query': $scope.widget.details.query})
                .then(function (result) {
                    if (result.ok)
                    {
//                        if ($scope.widget.details.start)
//                        {
//                            if ($scope.widget.details.start == '{dynamic}')
//                            {
//                                startDate = new Date();
//                            }
//                            else
//                            {
//                                startDate = new Date($scope.widget.details.start);
//                                $scope.widget.details.start = $filter('date')(startDate, 'dd/MM/yyyy');
//                            }
//                        }
//
//                        if ($scope.widget.details.end)
//                        {
//                            if ($scope.widget.details.end == '{dynamic}')
//                            {
//                                endDate = new Date();
//                            }
//                            else
//                            {
//                                endDate = new Date($scope.widget.details.end);
//                                $scope.widget.details.end = $filter('date')(endDate, 'dd/MM/yyyy');
//                            }
//                        }

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