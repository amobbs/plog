<?php

namespace Preslog\Notifications\Types;

use Preslog\Notifications\Types\TypeAbstract;

/**
 * Preslog Notification: Severity 1
 * Sends alerts for:
 * - New Logs
 * - Severity 1 only
 * - Via email and SMS
 */
class SeverityOne extends TypeAbstract
{
    protected $key  = 'severity-one';
    protected $name = 'Severity 1';

    public $settings = array(
        'email'=>array(
            'template'=>'severity',
        ),
        'sms'=>array(
            'template'=>'severity',
        ),
    );


    /**
     * Check this log is:
     * - Severity One
     * - New
     * @return  bool
     */
    public function checkCriteria()
    {
        // TODO

        // Validate: new log?
        if (false)
        {
            return false;
        }

        // Validate: Severity one log?
        if (false)
        {
            return false;
        }

        // Pass
        return true;
    }


    /**
     * Construct Data
     * @return  array       Fields for view
     */
    public function getTemplateData()
    {
        // Get standard
        $out = parent::getTemplateData();

        // Locate Severity field selected option
        $field = $this->log->getFieldByName('severity');
        $severity = ($field ? current($field->convertToFields()) : 'ERROR_NO_SEVERITY_FIELD');

        // Locate description
        $field = $this->log->getFieldByName('what');
        $description = ($field ? current($field->convertToFields()) : 'ERROR_NO_DESCRIPTION_FIELD');

        // Locate the HRID
        $hrid = (isset($this->log->data['hrid']) ? $this->log->data['hrid'] : 'ERROR_NO_LOG_ID');
        $slug = (isset($this->log->data['slug']) ? $this->log->data['slug'] : 'ERROR_NO_LOG_SLUG');

        // Output
        $out['severity'] = $severity;   // Severity name
        $out['subject'] = $hrid.' ['.$severity.'] '.$description;   // "WIN_#123 [Sev Level] Description"
        $out['slug'] = $slug;   // Log Slug
        $out['hrid'] = $hrid;   // Log HRID

        return $out;
    }
}