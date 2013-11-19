<?php

namespace Preslog\Notifications\Types;
use Preslog\Logs\FieldTypes\SelectSeverity;


/**
 * Preslog Notification: Other Types
 * Sends notifications for:
 * - New Logs
 * - Not Severity 1 or 2
 * - Email only
 */
class Others extends TypeAbstract
{
    protected $key  = 'other';
    protected $name = 'Everything else';

    public $settings = array(
        'email'=>array(
            'template'=>'other',
        ),
    );

    /**
     * Check this log is:
     * - Not Severity One OR Severity Two
     * - New
     * @return  bool
     */
    public function checkCriteria()
    {
        // Validate: Must be a new log
        $field = $this->log->getFieldByName('version');
        if ( !$field instanceof LogInfo)
        {
            return false;
        }

        $version = ($field ? $field->convertToFields()['Version']: 'ERROR');
        if ($version != 1)
        {
            return false;
        }

        // Validate: Must be a Severity one log
        $field = $this->log->getFieldByName('severity');
        if ( !$field instanceof SelectSeverity)
        {
            return false;
        }

        $level = ($field ? $field->getSelectedSeverityLevel() : 'ERROR');
        if ( 'level-1' == $level || 'level-2' == $level )
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
        $out = parent::getEmailTemplateData();

        // Locate description
        $field = $this->log->getFieldByName('what');
        $description = ($field ? current($field->convertToFields()) : 'ERROR_NO_DESCRIPTION_FIELD');

        // Locate the HRID
        $hrid = (isset($this->log->data['hrid']) ? $this->log->data['hrid'] : 'ERROR_NO_LOG_ID');
        $slug = (isset($this->log->data['slug']) ? $this->log->data['slug'] : 'ERROR_NO_LOG_SLUG');

        // Output
        $out['subject'] = $hrid.' [Other] '.$description;   // "WIN_#123 [Sev Level] Description"
        $out['slug'] = $slug;   // Log Slug
        $out['hrid'] = $hrid;   // Log HRID

        return $out;
    }
}