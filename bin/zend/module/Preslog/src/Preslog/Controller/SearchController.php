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

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class SearchController extends AbstractActionController
{

    /**
     * Search using the given query string
     * @return JsonModel
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
     */
    public function searchWizardParamsAction()
    {
        return new JsonModel(array(
            'todo' => 'TODO - Search Query Builder Wizard prams',
        ));
    }


    /**
     * Translate between QueryBuilder SQL and JQL
     * @return JsonModel
     */
    public function searchWizardTranslateAction()
    {
        return new JsonModel(array(
            'todo' => 'TODO - Search Query Builder Wizard prams',
        ));
    }
}
