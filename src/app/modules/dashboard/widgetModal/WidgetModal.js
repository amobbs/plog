angular.module('Preslog.dashboard.widgetModal', [])
    .controller('WidgetCtrl', function ($scope, $modalInstance, $filter, Restangular, widget, clients) {
        $scope.widget = widget;
        $scope.spanOptions = [1, 2, 3];
        $scope.clients = clients;

        $scope.queryValid = true;
        $scope.refreshValid = true;
        $scope.queryErrors = [];

        /**
         * Create a new widget on current dashboard
         *
         * @param type
         * @param preset - false means user will be given the option to edit settings right away
         */
        $scope.addChart = function(type, preset) {
            $scope.widget.type = type;

            var result = {
                widget: $scope.widget,
                preset: preset
            };

            $modalInstance.close(result);
        };

        /**
         * Completion of edit widget
         */
        $scope.saveWidget = function() {
            if ($scope.widget.details.refresh && $scope.widget.details.refresh < 1)
            {
                $scope.refreshValid = false;
                $scope.queryErrors = ['Refresh interval can not be below 1 minute.'];
                return;
            }

            var widgetResult = {
                widget: $scope.widget,
                preset: false
            };

            if ($scope.widget.type !== 'date')
            {
                Restangular.one('search/validate')
                    .get({'query': $scope.widget.details.query})
                    .then(function (result) {
                        if (result.ok)
                        {
                            $modalInstance.close(widgetResult);
                        }
                        else
                        {
                           $scope.queryValid = false;
                           $scope.queryErrors = result.errors;
                        }
                    });
            }
            else
            {
                $modalInstance.close(widgetResult);
            }
        };

        $scope.cancel = function() {
            $modalInstance.dismiss('cancel');
        };
    });