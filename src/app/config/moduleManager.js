/**
 * Loader for modules used in this system
 */
angular.module('moduleManager', [

    // Vendor Modules
    'ui.router',
    'ui.route',
    'ui.bootstrap',
    'ui.select2',
    'restangular',
//    'queryBuilder',
    'stateHelper',
    'titleService',
    'ngTable',

    // Services and Interceptors
    'userService',
    'errorHandler',

    // Directives
    'ngConfirmClick',
    'hierarchyFields',
    'validateServer',
    'validatePassword',
    'permission',

    // Site Modules
    'pages',
    'Preslog.auth',
    'Preslog.users',
    'Preslog.clients',
    'Preslog.home',
    'Preslog.log',
    'Preslog.search',
    'Preslog.dashboard'
]);

