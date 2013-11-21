angular.module('Preslog.dashboard.dashboardModal', [])
    .controller('DashboardModalCtrl', function ($scope, $modalInstance, name, isCreate, clients, share) {
        $scope.name = name;
        if (isCreate) {
            $scope.actionName = 'Create Dashboard';
        } else {
            $scope.actionName = 'Save Changes';
        }
        $scope.isCreate = isCreate;
        $scope.clients = clients;
        $scope.share = {};
        //angular wants an object with keys and booleans, while the api just returns a list of ids
        for(var i = 0; i < share.length; i++)
        {
            $scope.share[share[i]] = true;
        }

        $scope.ok = function() {
            //set it back to a list of ids
            var returnShares = [];
            for (var key in $scope.share) {
                if (key === 'length' || !$scope.share.hasOwnProperty(key)) continue;
                else if ($scope.share[key] && key.length == 24 )
                {
                    returnShares.push(key);
                }
            }

            $modalInstance.close({
                name: $scope.name,
                shares: returnShares
            });
        };

        $scope.cancel = function() {
            $modalInstance.dismiss('cancel');
        };
    });