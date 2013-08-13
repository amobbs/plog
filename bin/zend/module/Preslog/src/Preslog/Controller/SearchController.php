<?php
/**
 * Preslog Search Controller
 * - Perform searched based on JQL
 * - Initialise Query Builder with system data
 * - Translate between QueryBuilder/SQL and JQL (bidirectional)
 *
 * @author      4mation Technlogies
 * @link        http://www.4mation.com.au
 * @author      Dave Newson <dave@4mation.com.au>
 * @copyright   Copyright (c) MediaHub Australia
 * @link        http://mediahubaustralia.com.au
 */

namespace Preslog\Controller;

use Preslog\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Swagger\Annotations as SWG;

class SearchController extends AbstractRestfulController
{


    /**
     * Search using the given query string
     * @return JsonModel
     *
     * @SWG\Resource(
     *      resourcePath="/",
     *      @SWG\Api(
     *          path="/search",
     *          @SWG\Operation(
     *              nickname="index",
     *              summary="Search using a given query string",
     *              httpMethod="GET",
     *              responseClass="User"
     *          )
     *      )
     * )
     */
    public function searchAction()
    {
        return new JsonModel(array(
            'todo' => 'TODO - Search using the given query string',
        ));
    }


    /**
     * Search using the given query string and export as an XLS
     * @return ViewModel
     *
     * @SWG\Resource(
     *      resourcePath="/",
     *      @SWG\Api(
     *          path="/search/export-xls",
     *          @SWG\Operation(
     *              nickname="index",
     *              summary="Search and Export to XLS using a given query string",
     *              httpMethod="GET",
     *              responseClass="User"
     *          )
     *      )
     * )
     */
    public function searchExportAsXlsAction()
    {
        return new ViewModel(array(
            'todo' => 'TODO - Search using the given query string',
        ));
    }


    /**
     * Fetch params for the Search Query Builder Wizard
     * @return JsonModel
     *
     * @SWG\Resource(
     *      resourcePath="/",
     *      @SWG\Api(
     *          path="/search/params",
     *          @SWG\Operation(
     *              nickname="index",
     *              summary="Fetch params for Query Builder Wizard",
     *              httpMethod="GET",
     *              responseClass="User"
     *          )
     *      )
     * )
     */
    public function searchWizardParamsAction()
    {
        return new JsonModel(array(
            'todo' => 'TODO - Search Query Builder Wizard params',
        ));
    }


    /**
     * Translate between QueryBuilder SQL and JQL
     * @return JsonModel
     *
     * @SWG\Resource(
     *      resourcePath="/",
     *      @SWG\Api(
     *          path="/search/translate",
     *          @SWG\Operation(
     *              nickname="index",
     *              summary="Translate between an SQL and JQL statement",
     *              httpMethod="GET",
     *              responseClass="User"
     *          )
     *      )
     * )
     */
    public function searchWizardTranslateAction()
    {
        return new JsonModel(array(
            'todo' => 'TODO - Search Query Builder Wizard params',
        ));
    }
}
