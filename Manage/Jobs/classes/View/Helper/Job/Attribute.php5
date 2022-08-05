<?php

use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Job_Attribute
{
	const ATTRIBUTE_DEFINITION_STATUS	= 0;
	const ATTRIBUTE_DEFINITION_MODE		= 1;
	const ATTRIBUTE_RUN_STATUS			= 2;
	const ATTRIBUTE_RUN_TYPE			= 3;
	const ATTRIBUTE_SCHEDULE_STATUS		= 4;
	const ATTRIBUTE_SCHEDULE_TYPE		= 5;

	const RUN_STATUS_CLASSES				= array(
		Model_Job_Run::STATUS_TERMINATED		=> 'label label-important',
		Model_Job_Run::STATUS_FAILED			=> 'label label-important',
		Model_Job_Run::STATUS_ABORTED			=> 'label label-important',
		Model_Job_Run::STATUS_PREPARED			=> 'label',
		Model_Job_Run::STATUS_RUNNING			=> 'label label-warning',
		Model_Job_Run::STATUS_DONE				=> 'label label-info',
		Model_Job_Run::STATUS_SUCCESS			=> 'label label-success',
	);
	const RUN_STATUS_ICON_CLASSES			= array(
		Model_Job_Run::STATUS_TERMINATED		=> 'fa fa-fw fa-times',
		Model_Job_Run::STATUS_FAILED			=> 'fa fa-fw fa-exclamation-triangle',
		Model_Job_Run::STATUS_ABORTED			=> 'fa fa-fw fa-ban',
		Model_Job_Run::STATUS_PREPARED			=> 'fa fa-fw fa-asterisk',
		Model_Job_Run::STATUS_RUNNING			=> 'fa fa-fw fa-cog fa-spin',
		Model_Job_Run::STATUS_DONE				=> 'fa fa-fw fa-check',
		Model_Job_Run::STATUS_SUCCESS			=> 'fa fa-fw fa-',
	);
	const RUN_TYPE_CLASSES					= array(
		Model_Job_Run::TYPE_MANUALLY			=> 'label label-info',
		Model_Job_Run::TYPE_SCHEDULED			=> 'label label-success',
	);
	const RUN_TYPE_ICON_CLASSES				= array(
		Model_Job_Run::TYPE_MANUALLY			=> 'fa fa-fw fa-hand-paper-o',
		Model_Job_Run::TYPE_SCHEDULED			=> 'fa fa-fw fa-clock-o',
	);
	const DEFINITION_STATUS_CLASSES			= array(
		Model_Job_Definition::STATUS_DISABLED	=> 'label label-inverse',
		Model_Job_Definition::STATUS_ENABLED	=> 'label label-success',
		Model_Job_Definition::STATUS_DEPRECATED	=> 'label label-warning',
	);
	const DEFINITION_STATUS_ICON_CLASSES	= array(
		Model_Job_Definition::STATUS_DISABLED	=> 'fa fa-fw fa-toggle-off',
		Model_Job_Definition::STATUS_ENABLED	=> 'fa fa-fw fa-toggle-on',
		Model_Job_Definition::STATUS_DEPRECATED	=> 'fa fa-fw fa-ban',
	);
	const DEFINITION_MODE_CLASSES			= array(
		Model_Job_Definition::MODE_UNDEFINED	=> 'not-label not-label-info',
		Model_Job_Definition::MODE_SINGLE		=> 'not-label not-label-info',
		Model_Job_Definition::MODE_MULTIPLE		=> 'not-label not-label-success',
		Model_Job_Definition::MODE_EXCLUSIVE	=> 'not-label not-label-success',
	);
	const DEFINITION_MODE_ICON_CLASSES		= array(
		Model_Job_Definition::MODE_UNDEFINED	=> 'fa fa-fw fa-exclamation-circle ',
		Model_Job_Definition::MODE_SINGLE		=> 'fa fa-fw fa-square',
		Model_Job_Definition::MODE_MULTIPLE		=> 'fa fa-fw fa-th-large',
		Model_Job_Definition::MODE_EXCLUSIVE	=> 'fa fa-fw fa-square-o',
	);

	const SCHEDULE_STATUS_CLASSES			= array(
		Model_Job_Schedule::STATUS_DISABLED		=> 'label label-inverse',
		Model_Job_Schedule::STATUS_ENABLED		=> 'label label-success',
		Model_Job_Schedule::STATUS_PAUSED		=> 'label label-warning',
	);
	const SCHEDULE_STATUS_ICON_CLASSES	= array(
		Model_Job_Schedule::STATUS_DISABLED		=> 'fa fa-fw fa-toggle-off',
		Model_Job_Schedule::STATUS_ENABLED		=> 'fa fa-fw fa-toggle-on',
		Model_Job_Schedule::STATUS_PAUSED		=> 'fa fa-fw fa-pause',
	);

	protected $env;
	protected $attribute		= 0;
	protected $object;
	protected $id;

	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->words	= $env->getLanguage()->getWords( 'manage/job' );
	}

	public function __toString(): string
	{
		return $this->render();
	}

	public function render(): string
	{
		if( !$this->object  )
			throw new RuntimeException( 'No object set' );
		switch( $this->attribute ){
			case static::ATTRIBUTE_DEFINITION_STATUS:
				$iconClass	= static::DEFINITION_STATUS_ICON_CLASSES[$this->object->status];
				$labelClass	= static::DEFINITION_STATUS_CLASSES[$this->object->status];
				$labelText	= $this->words['job-definition-statuses'][$this->object->status];
				break;
			case static::ATTRIBUTE_RUN_STATUS:
				$iconClass	= static::RUN_STATUS_ICON_CLASSES[$this->object->status];
				$labelClass	= static::RUN_STATUS_CLASSES[$this->object->status];
				$labelText	= $this->words['job-run-statuses'][$this->object->status];
				break;
			case static::ATTRIBUTE_DEFINITION_MODE:
				$iconClass	= static::DEFINITION_MODE_ICON_CLASSES[$this->object->mode];
				$labelClass	= static::DEFINITION_MODE_CLASSES[$this->object->mode];
				$labelText	= $this->words['job-definition-modes'][$this->object->mode];
				break;
			case static::ATTRIBUTE_RUN_TYPE:
				$iconClass	= static::RUN_TYPE_ICON_CLASSES[$this->object->type];
				$labelClass	= static::RUN_TYPE_CLASSES[$this->object->type];
				$labelText	= $this->words['job-run-types'][$this->object->type];
				break;
			case static::ATTRIBUTE_SCHEDULE_STATUS:
				$iconClass	= static::SCHEDULE_STATUS_ICON_CLASSES[$this->object->status];
				$labelClass	= static::SCHEDULE_STATUS_CLASSES[$this->object->status];
				$labelText	= $this->words['job-schedule-statuses'][$this->object->status];
				break;
		}
		$icon	= UI_HTML_Tag::create( 'i', '', array( 'class' => $iconClass ) );
		$label	= $iconClass ? $icon.'&nbsp;'.$labelText : $labelText;
		$label	= UI_HTML_Tag::create( 'span', $label, array( 'class' => $labelClass ) );
		return $label;
	}

	public function setAttribute( int $attribute ): self
	{
		$this->attribute	= $attribute;
		return $this;
	}

	public function setObject( $jobDefinitionOrRun ): self
	{
		$this->object	= $jobDefinitionOrRun;
		return $this;
	}
}
