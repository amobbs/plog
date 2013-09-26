angular.module('permission', [
        'userService'
    ])
    .directive('permission', ['userService', function (userService) {

        return {
            priority: 1000,
            compile: function(element, attrs) {

                var permission = attrs.permission;

                // Check permissions
                userService.checkPermission( permission ).then(function( result )
                {
                    // If not allowed
                    if (!result)
                    {
                        // Remove the elements
                        var children = element.children();
                        children.remove();
                        element.remove();
                    }
                });
            }
        };
    }]);