/**
 * Loader for modules used in this system
 */
angular.module('moduleManager', [

    // Vendor Modules
    'ui.state',
    'ui.route',
    'ui.bootstrap',
    'restangular',
    'queryBuilder',
    'stateHelper',

    // Site Modules
    'pages',
    'Preslog.auth',
    'Preslog.home'
]);

