<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/**
 *	@deprecated		not used anymore, see template index.active
 */
class View_Helper_Work_Time_Timer extends View_Helper_Work_Time
{
	static protected $modules	= [];

	protected $missionId		= NULL;

	static public function decorateTimer( Environment $env, $timer, bool $strict = TRUE )
	{
		$modelProject		= new Model_Project( $env );
		if( $timer->projectId )
			$timer->project	= $modelProject->get( $timer->projectId );

		$timer->type			= NULL;
		$timer->relation		= NULL;
		$timer->relationTitle	= NULL;
		$timer->relationLink	= NULL;
		if( $timer->module ){
			if( !array_key_exists( $timer->module, self::$modules ) )
				throw new Exception( 'Module "'.$timer->module.'" not registered' );
			$module	= self::$modules[$timer->module];
//print_m( self::$modules );
//print_m( $timer );
//print_m( $module );
//die;
			$entry	= $module->model->get( $timer->moduleId );
			if( !$entry ){
				if( $strict )
					throw new Exception( 'Relation between timer and module is invalid' );
				return;
			}
			$timer->type			= $module->typeLabel;
			$timer->relation		= $entry;
			$timer->relationTitle	= $entry->{$module->column};
			$timer->relationLink	= str_replace( "{id}", $timer->moduleId, $module->link );
		}
	}

	static public function getRegisteredModules()
	{
		return self::$modules;
	}

	public function registerModule( $module )
	{
		$arguments		= [$this->env];
		$modelInstance	= Alg_Object_Factory::createObject( $module->modelClass, $arguments );
		self::$modules[$module->moduleId]	= (object) array(
			'id'			=> $module->moduleId,
			'title'			=> $module->moduleId,
			'modelClass'	=> $module->modelClass,
			'typeLabel'		=> $module->typeLabel,
			'model'			=> $modelInstance,
			'link'			=> './'.$module->linkDetails,
			'column'		=> 'title',
		);
	}

	public function render(): string
	{
		$conditions	= [];
		$conditions['userId']	= (int) $this->userId;
		$conditions['status']	= 1;
		if( $this->module )
			$conditions['module']	= $this->module;
		if( $this->moduleId )
			$conditions['moduleId']	= $this->moduleId;
		$timer		= $this->modelTimer->getByIndices( $conditions );
		if( !$timer )
			return '';
		View_Helper_Work_Time_Timer::decorateTimer( $this->env, $timer );

		$linkProject	= HtmlTag::create( 'a', $timer->project->title, array(
			'href'	=> './manage/project/view/'.$timer->project->projectId,
			'class'	=> 'autocut',
		) );
		$linkModule		= HtmlTag::create( 'a', $timer->relationTitle, array(
			'href'	=> $timer->relationLink,
			'class'	=> 'autocut',
		) );
		$secondsNeeded	= $timer->secondsNeeded + ( time() - $timer->modifiedAt );
		return '
	<div class="not-well not-well-large well alert alert-info">
		<div class="row-fluid">
			<div class="span10">
					<dl class="dl-horizontal">
					<dt>Projekt</dt>
					<dd>'.$linkProject.'</dd>
					<dt>Aufgabe</dt>
					<dd>'.$linkModule.'</dd>
					<dt>Aktivität</dt>
					<dd><div class="autocut">'.$timer->title.'&nbsp;</div></dd>
					<dt>geplante Zeit</dt>
					<dd>'.View_Helper_Work_Time::formatSeconds( $timer->secondsPlanned ).'</dd>
					<dt>erfasste Zeit</dt>
					<dd id="timer" data-value="'.$secondsNeeded.'">'.View_Helper_Work_Time::formatSeconds( $secondsNeeded ).'</dd>
				</dl>
			</div>
			<div class="span2 pull-right">
				<br/>
				<a href="./work/time/pause/'.$timer->workTimerId.'?from='.$this->from.'" class="btn btn-large not-btn-danger pull-right" title="pausieren"><i class="icon-pause not-icon-white"></i></a>
			</div>
		</div>
	</div>
	<style>
.well .dl-horizontal {
	margin: 0px;
	}
.well .dl-horizontal dt {
	width: 120px;
	}
.well .dl-horizontal dd {
	margin-left: 130px;
	}
	</style>
	<script src="scripts/str_pad.js"></script>
	<script>
$(document).ready(function(){
	WorkTimer.init();
});
	</script>';
	}

	public function setModule( $module ): self
	{
		$this->module		= $module;
		return $this;
	}

	public function setModuleId( $moduleId ): self
	{
		$this->moduleId	= $moduleId;
		return $this;
	}
}
