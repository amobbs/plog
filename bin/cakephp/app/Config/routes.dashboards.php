<?php

/**
 * Dashboard and Widget routes
 */

use Swagger\Annotations as SWG;

/**
 * Dashboard.Favourites: GET (Fetch my favourite dashboards)
 * @SWG\Resource(
 *      resourcePath="/dashboards",
 *      @SWG\Api(
 *          path="/dashboards/favourites",
 *          @SWG\Operation(
 *              @SWG\Partial("dashboards.favourites.read"),
 *              nickname="dashboards.favourites.read",
 *              method="GET"
 *          )
 *      )
 * )
 */
Router::connect(
    '/dashboards/favourites',
    array('controller' => 'Dashboards', 'action' => 'listFavouriteDashboards', '[method]' => 'GET')
);


/**
 * Dashboard.Favourites: POST (Save a favourite)
 * @SWG\Resource(
 *      resourcePath="/dashboards",
 *      @SWG\Api(
 *          path="/dashboards/favourites",
 *          @SWG\Operation(
 *              @SWG\Partial("dashboards.favourites.update"),
 *              nickname="dashboards.favourites.update",
 *              method="POST"
 *          )
 *      )
 * )
 */
Router::connect(
    '/dashboards/favourites',
    array('controller' => 'Dashboards', 'action' => 'editFavouriteDashboards', '[method]' => 'POST')
);


/**
 * Dashboard.Favourites: DELETE (Delete a favourite)
 * @SWG\Resource(
 *      resourcePath="/dashboards",
 *      @SWG\Api(
 *          path="/dashboards/favourites",
 *          @SWG\Operation(
 *              @SWG\Partial("dashboards.favourites.delete"),
 *              nickname="dashboards.favourites.delete",
 *              method="DELETE"
 *          )
 *      )
 * )
 */
Router::connect(
    '/dashboards/favourites/:dashboard_id',
    array('controller' => 'Dashboards', 'action' => 'editFavouriteDashboards', '[method]' => 'DELETE')
);

/**
 * Dashboards: GET (read all dashboards)
 * @SWG\Resource(
 *      resourcePath="/dashboards",
 *      @SWG\Api(
 *          path="/dashboards",
 *          @SWG\Operation(
 *              @SWG\Partial("dashboards.list"),
 *              nickname="dashboards.list",
 *              method="GET"
 *          )
 *      )
 * )
 */
Router::connect(
    '/dashboards',
    array('controller' => 'Dashboards', 'action' => 'listAllDashboards', '[method]' => 'GET')
);


/**
 * Dashboards.Specific: GET (read dashboard)
 * @SWG\Resource(
 *      resourcePath="/dashboards",
 *      @SWG\Api(
 *          path="/dashboards/{dashboard_id}",
 *          @SWG\Operation(
 *              @SWG\Partial("dashboards.specific.read"),
 *              nickname="dashboards.specific.read",
 *              method="GET"
 *          )
 *      )
 * )
 */
Router::connect(
    '/dashboards/:dashboard_id',
    array('controller' => 'Dashboards', 'action' => 'editDashboard', '[method]' => 'GET'),
    array('pass'=>array('dashboard_id'), 'dashboard_id'=>'[0-9a-z]+')
);


/**
 * Dashboards.Specific: POST (update dashboard)
 * @SWG\Resource(
 *      resourcePath="/dashboards",
 *      @SWG\Api(
 *          path="/dashboards/{dashboard_id}",
 *          @SWG\Operation(
 *              @SWG\Partial("dashboards.specific.update"),
 *              nickname="dashboards.specific.update",
 *              method="POST"
 *          )
 *      )
 * )
 */
Router::connect(
    '/dashboards/:dashboard_id',
    array('controller' => 'Dashboards', 'action' => 'editDashboard', '[method]' => 'POST'),
    array('pass'=>array('dashboard_id'), 'dashboard_id'=>'[0-9a-z]+')
);


/**
 * Dashboards.Specific: DELETE (delete dashboard)
 * @SWG\Resource(
 *      resourcePath="/dashboards",
 *      @SWG\Api(
 *          path="/dashboards/{dashboard_id}",
 *          @SWG\Operation(
 *              @SWG\Partial("dashboards.specific.delete"),
 *              nickname="dashboards.specific.delete",
 *              method="DELETE"
 *          )
 *      )
 * )
 */
Router::connect(
    '/dashboards/:dashboard_id',
    array('controller' => 'Dashboards', 'action' => 'deleteDashboard', '[method]' => 'DELETE'),
    array('pass'=>array('dashboard_id'), 'dashboard_id'=>'[0-9a-z]+')
);


/**
 * Dashboard.Specific.Widgets.Create: POST (create widget on dashboard)
 * @SWG\Resource(
 *      resourcePath="/dashboards",
 *      @SWG\Api(
 *          path="/dashboards/{dashboard_id}/widgets",
 *          @SWG\Operation(
 *              @SWG\Partial("dashboards.specific.widgets.create"),
 *              nickname="dashboards.specific.widgets.create",
 *              method="POST"
 *          )
 *      )
 * )
 */
Router::connect(
    '/dashboards/:dashboard_id/widgets',
    array('controller' => 'Dashboards', 'action' => 'editWidget', '[method]' => 'POST'),
    array('pass'=>array('dashboard_id'), 'dashboard_id'=>'[0-9a-z]+')
);

/**
 * Dashboards.Specific.Widgets.Specific.Read: GET (read existing widget)
 * @SWG\Resource(
 *      resourcePath="/dashboards",
 *      @SWG\Api(
 *          path="/dashboards/{dashboard_id}/widgets/{widget_id}",
 *          @SWG\Operation(
 *              @SWG\Partial("dashboards.specific.widgets.specific.read"),
 *              nickname="dashboards.specific.widgets.specific.read",
 *              method="GET"
 *          )
 *      )
 * )
 */
Router::connect(
    '/dashboards/:dashboard_id/widgets/:widget_id',
    array('controller' => 'Dashboards', 'action' => 'editWidget', '[method]' => 'GET'),
    array('pass'=>array('dashboard_id', 'widget_id'), 'dashboard_id'=>'[0-9a-z]+', 'widget_id'=>'[0-9a-z]+')
);


/**
 * Dashboards.Specific.Widgets.Specific.Update: POST (update existing widget)
 * @SWG\Resource(
 *      resourcePath="/dashboards",
 *      @SWG\Api(
 *          path="/dashboards/{dashboard_id}/widgets/{widget_id}",
 *          @SWG\Operation(
 *              @SWG\Partial("dashboards.specific.widgets.specific.update"),
 *              nickname="dashboards.specific.widgets.specific.update",
 *              method="POST"
 *          )
 *      )
 * )
 */
Router::connect(
    '/dashboards/:dashboard_id/widgets/:widget_id',
    array('controller' => 'Dashboards', 'action' => 'editWidget', '[method]' => 'POST'),
    array('pass'=>array('dashboard_id', 'widget_id'), 'dashboard_id'=>'[0-9a-z]+', 'widget_id'=>'[0-9a-z]+')
);


/**
 * Dashboards.Specific.Widgets.Specific.Delete: DELETE (remove existing widget)
 * @SWG\Resource(
 *      resourcePath="/dashboards",
 *      @SWG\Api(
 *          path="/dashboards/{dashboard_id}/widgets/{widget_id}",
 *          @SWG\Operation(
 *              @SWG\Partial("dashboards.specific.widgets.specific.delete"),
 *              nickname="dashboards.specific.widgets.specific.delete",
 *              method="DELETE"
 *          )
 *      )
 * )
 */
Router::connect(
    '/dashboards/:dashboard_id/widgets/:widget_id',
    array('controller' => 'Dashboards', 'action' => 'deleteWidget', '[method]' => 'DELETE'),
    array('pass'=>array('dashboard_id', 'widget_id'), 'dashboard_id'=>'[0-9a-z]+', 'widget_id'=>'[0-9a-z]+')
);


/**
 * Dashboards.specific.export
 * @SWG\Resource(
 *      resourcePath="/dashboards",
 *      @SWG\Api(
 *          path="/dashboards/{dashboard_id}/export",
 *          @SWG\Operation(
 *              @SWG\Partial("dashboards.specific.export"),
 *              nickname="dashboards.specific.export",
 *              method="GET"
 *          )
 *      )
 * )
 */
Router::connect(
    '/dashboards/:dashboard_id/export',
    array('controller' => 'Dashboards', 'action' => 'exportDashboard', '[method]' => 'GET'),
    array('pass'=>array('dashboard_id'), 'dashboard_id'=>'[0-9a-z]+')
);


/**
 * Dashboard.Create: POST (create new dashboard)
 * @SWG\Resource(
 *      resourcePath="/dashboards",
 *      @SWG\Api(
 *          path="/dashboards",
 *          @SWG\Operation(
 *              @SWG\Partial("dashboards.create"),
 *              nickname="dashboards.create",
 *              method="POST"
 *          )
 *      )
 * )
 */
Router::connect(
    '/dashboards',
    array('controller' => 'Dashboards', 'action' => 'editDashboard', '[method]' => 'POST')
);

/**
 * Widgets
 * @SWG\Resource(
 *      resourcePath="/widgets",
 *      @SWG\Api(
 *          path="/widgets",
 *          @SWG\Operation(
 *              @SWG\Partial("widgets.list"),
 *              nickname="widgets.list",
 *              method="GET"
 *          )
 *      )
 * )
 */
Router::connect(
    '/widgets',
    array('controller' => 'Dashboards', 'action' => 'listWidgets', '[method]' => 'GET')
);


/**
 * Widgets.Options
 * @SWG\Resource(
 *      resourcePath="/widgets",
 *      @SWG\Api(
 *          path="/widgets/{widget_type}",
 *          @SWG\Operation(
 *              @SWG\Partial("widgets.options"),
 *              nickname="widgets.options",
 *              method="GET"
 *          )
 *      )
 * )
 */
Router::connect(
    '/widgets/:widget_type',
    array('controller' => 'Dashboards', 'action' => 'readWidgetOptions', '[method]' => 'GET'),
    array('pass'=>array('widget_type'), 'widget_type'=>'[0-9a-z]+')
);