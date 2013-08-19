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
     * @SWG\Operation(
     *      partial="search",
     *      summary="Return log list based on POST JQL search criteria",
     *      notes="Users can only search across those Clients to which they have access."
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
     * @SWG\Operation(
     *      partial="search.export",
     *      summary="Instigate download of XLS containing search results",
     *      notes="Replicates the functionality of Search, with XLS output."
     * )
     */
    public function searchExportAsXlsAction()
    {
        return new ViewModel(array(
            'todo' => 'TODO - Search using the given query string and export as XLS',
        ));
    }


    /**
     * Fetch params for the Search Query Builder Wizard
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="search.wizard.params",
     *      summary="Return field parameters for Query Builder",
     *      notes="Search params are limited to those from Clients to which this User has access."
     * )
     */
    public function searchWizardParamsAction()
    {
        return new JsonModel(array(
            'todo' => 'TODO - Search Query Builder Wizard params.',
        ));
    }


    /**
     * Translate between QueryBuilder SQL and JQL
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="search.wizard.translate",
     *      summary="Translate between SQL and JS. Bi-directional.",
     *      notes=""
     * )
     */
    public function searchWizardTranslateAction()
    {
        return new JsonModel(array(
            'todo' => 'TODO - Translate between JQL and SQL.',
        ));
    }
}
