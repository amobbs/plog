/**
 * Angular permissions related directives
 * @author  Dave Newson <dave@4mation.com.au>
 * @copy    4mation Technologies
 * @link    http://www.4mation.com.au
 */
angular.module('permission', [
        'userService'
    ])

    /**
     * Permission directive
     * - Only compile this element into existance if the user has the correct permissions.
     * - Element will not show up if the users permissions change; it is permanently excluded.
     */
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
    }])

    /**
     * ng-Permissions directive
     * - Only compile this element into statement passed evalutes to true.
     * - Element will not show up if the users permissions change; it is permanently excluded.
     */
    .directive('ngPermission', [function () {

        return {
            priority: 1000,
            compile: function(element, attrs) {

                // If not allowed
                if (!attrs.ngPermission)
                {
                    // Remove the elements
                    var children = element.children();
                    children.remove();
                    element.remove();
                }
            }
        };
    }])
;