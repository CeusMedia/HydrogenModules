<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

class View_Helper_Work_Time_Modal_Add extends View_Helper_Work_Time
{
	protected ?string $module		= NULL;
	protected ?string $moduleId		= NULL;
	protected ?string $projectId	= NULL;

	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	public function render(): string
	{
		$logicProject	= Logic_Project::getInstance( $this->env );
		$logicAuth		= Logic_Authentication::getInstance( $this->env );
		$currentUserId	= $logicAuth->getCurrentUserId();

		$words		= $this->getWords( '', 'work/time' );
		$modules	= View_Helper_Work_Time_Timer::getRegisteredModules();
		$module		= $modules[$this->module];
		$w			= (object) $words['add'];

		$optStatus	= $words['states'];
		$optStatus	= HtmlElements::Options( $optStatus, 0 );

		$optWorker	= [];
		$users	= $logicProject->getProjectUsers( $this->projectId, [], ['username' => 'ASC'] );
		foreach( $users as $user )
			$optWorker[$user->userId]	= $user->username;
		$optWorker	= HtmlElements::Options( $optWorker, $currentUserId );

		$fieldProject	= '';
		if( $this->projectId ){
			$project	= $this->modelProject->get( $this->projectId );
			$fieldProject	= '
			<div class="row-fluid">
				<div class="span4">
					<label for="input_workerId">'.$w->labelWorkerId.'</label>
					<select name="workerId" id="input_workerId" class="span12">'.$optWorker.'</select>
				</div>
				<div class="span8">
					<label for="input_projectId">'.$w->labelProjectId.'</label>
					<input type="text" name="project" id="input_project" class="span12" readonly="readonly" value="'.$project->title.'"/>
				</div>
			</div>';

		}
		$fieldRelation	= '';
		if( $this->moduleId ){
			$relation	= $module->model->get( $this->moduleId );
			$fieldRelation	= '
			<div class="row-fluid">
				<div class="span12">
					<label>'.$module->typeLabel.'</label>
					<input type="text" class="span12" readonly="readonly" value="'.htmlentities( $relation->title, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>';
		}

		return '
<form action="./work/time/add" method="post">
	<input type="hidden" name="from" value="'.$this->from.'"/>
	<input type="hidden" name="projectId" value="'.$this->projectId.'"/>
	<input type="hidden" name="module" value="'.$this->module.'"/>
	<input type="hidden" name="moduleId" value="'.$this->moduleId.'"/>

	<div id="myModalWorkTimeAdd" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3 id="myModalLabel">Starte Zeiterfassung</h3>
		</div>
		<div class="modal-body">
			'.$fieldRelation.'
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title" class="mandatory required">Titel</label>
					<input type="text" name="title" id="input_title" class="span12" required="required"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_description">Beschreibung</label>
					<textarea name="description" id="input_description" rows="5" class="span12"></textarea>
				</div>
			</div>
			'.$fieldProject.'
			<div class="row-fluid">
				<div class="span4">
					<label for="input_time_planned">'.$w->labelTimePlanned.'</label>
					<input type="text" name="time_planned" id="input_time_planned" class="span12" value="0h 00m"/>
				</div>
				<div class="span4">
					<label for="input_time_needed">'.$w->labelTimeNeeded.'</label>
					<input type="text" name="time_needed" id="input_time_needed" class="span12" value="0h 00m"/>
				</div>
				<div class="span4">
					<label for="input_status">'.$w->labelStatus.'</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn" data-dismiss="modal" aria-hidden="true"><i class="icon-arrow-left"></i>&nbsp;'.$w->buttonCancel.'</button>
			<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i>&nbsp;'.$w->buttonSave.'</button>
		</div>
	</div>
</form>';
	}

	/**
	 * @param		string		$module
	 * @return		self
	 */
	public function setModule( string $module ): self
	{
		$this->module		= $module;
		return $this;
	}

	/**
	 * @param		string		$moduleId
	 * @return		self
	 */
	public function setModuleId( string $moduleId ): self
	{
		$this->moduleId		= $moduleId;
		return $this;
	}

	/**
	 *	@param		string		$projectId
	 *	@return		self
	 */
	public function setProjectId( string $projectId ): self
	{
		$this->projectId	= $projectId;
		return $this;
	}
}
