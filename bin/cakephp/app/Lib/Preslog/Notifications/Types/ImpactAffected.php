<?php

namespace Preslog\Notifications\Types;
use Preslog\Logs\FieldTypes\SelectImpact;


/**
 * Preslog Notification: On Air Impact affected transmission
 * Sends alerts for:
 * - New Logs
 * - "on-air impact" affected transmission
 */
class ImpactAffected extends TypeAbstract
{
    protected $key  = 'impact-affected';
    protected $name = 'Impact Affected Transmission';

    public $settings = array(
        'email'=>array(
            'template'=>'impact',
        ),
    );


    /**
     * Check this log is:
     * - On Air Impact affected transmission
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
        $field = $this->log->getFieldByName('impact');
        if ( !$field instanceof SelectImpact)
        {
            return false;
        }

        $level = ($field ? $field->getSelectedImpact() : 'ERROR');
        if ( 'affected' != $level)
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

        // Locate Severity field selected option
        $field = $this->log->getFieldByName('impact');
        $impact = ($field ? current($field->convertToFields()) : 'ERROR_NO_IMPACT_FIELD');

        // Locate description
        $field = $this->log->getFieldByName('what');
        $description = ($field ? current($field->convertToFields()) : 'ERROR_NO_DESCRIPTION_FIELD');

        // Locate the HRID
        $hrid = (isset($this->log->data['hrid']) ? $this->log->data['hrid'] : 'ERROR_NO_LOG_ID');
        $slug = (isset($this->log->data['slug']) ? $this->log->data['slug'] : 'ERROR_NO_LOG_SLUG');

        // Output
        $out['impact'] = $impact;   // Severity name
        $out['subject'] = $hrid.' ['.$impact.'] '.$description;   // "WIN_#123 [Sev Level] Description"
        $out['slug'] = $slug;   // Log Slug
        $out['hrid'] = $hrid;   // Log HRID

        return $out;
    }
}