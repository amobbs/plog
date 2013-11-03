/**
 * Widget Controller
 */
angular.module('Preslog.dashboard.widgetModal', [])
    .controller('WidgetCtrl', function ($scope, $modalInstance, Restangular, widget, clients) {

        // Widget data
        $scope.widget = widget;

        // Dashboard column span capabilities
        $scope.spanOptions = [1, 2, 3];

        // Client info
        $scope.clients = clients;

        // Query validation
        $scope.queryValid = true;
        $scope.queryErrors = [];


        /**
         * Create new widget
         * @param type
         */
        $scope.addChart = function(type) {
            $scope.widget.type = type;
            $modalInstance.close($scope.widget);
        };


        /**
         * Edit Widget: Close and save
         */
        $scope.saveWidget = function() {
            Restangular.one('search/validate')
                .get({'query': $scope.widget.details.query})
                .then(function (result) {
                    if (result.ok)
                    {
                        $modalInstance.close($scope.widget);
                    }
                    else
                    {
                       $scope.queryValid = false;
                       $scope.queryErrors = result.errors;
                    }
                });
        };


        /**
         * Dismiss without saving
         */
        $scope.cancel = function() {
            $modalInstance.dismiss('cancel');
        };
    });