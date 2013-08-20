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
    'queryBuilder',
    'stateHelper',

    // Site Modules
    'pages',
    'Preslog.auth',
    'Preslog.home'
]);

