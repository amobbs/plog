/**
 * Loader for modules used in this system
 */
angular.module('moduleManager', [

    // Vendor Modules
    'ui.state',
    'ui.route',
    'ui.bootstrap',
    'ui.select2',
    'restangular',
//    'queryBuilder',
    'stateHelper',
    'titleService',
    'ngTable',

    // Shared Modules
    'userService',
    'errorHandler',

    // Site Modules
    'pages',
    'Preslog.auth',
    'Preslog.users',
    'Preslog.home',
    'Preslog.log'
]);

