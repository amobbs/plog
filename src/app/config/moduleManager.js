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
    'stateHistory',
    'titleService',
    'ngTable',

    // Providers, Services and Interceptors'
    'fileUpload',
    'cookieStore',
    'userService',
    'queryBuilderService',
    'errorHandler',
    'loadingHandler',
    'httpAuthInterceptor',

    // Directives
    'ngConfirmClick',
    'clickWait',
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
    'dateWidget',

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

