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
    'ui.sortable',
//    'queryBuilder',
    'stateHelper',
    'titleService',
    'ngTable',

    // Services and Interceptors
    'userService',
    'errorHandler',
    'loadingHandler',

    // Directives
    'ngConfirmClick',
    'hierarchyFields',
    'validateServer',
    'validatePassword',
    'permission',
    'logFields',
    'inputFieldLoginfo',
    'inputFieldDatetime',
    'inputFieldDuration',
    'logWidget',
    'fileUpload',

    // Site Modules
    'pages',
    'Preslog.auth',
    'Preslog.nav',
    'Preslog.users',
    'Preslog.usersAdmin',
    'Preslog.clients',
    'Preslog.home',
    'Preslog.log',
    'Preslog.search',
    'Preslog.dashboard'
]);

