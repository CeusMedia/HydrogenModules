<?php
/**
 *	View.
 *	@version		$Id$
 */
/**
 *	View.
 *	@version		$Id$
 *	@todo			implement
 *	@todo			code documentation
 */
class View_Work_Mission extends CMF_Hydrogen_View{

	protected function __onInit(){
		$page			= $this->env->getPage();
		$session		= $this->env->getSession();
		$monthsLong		= array_values( (array) $this->getWords( 'months' ) );
		$monthsShort	= array_values( (array) $this->getWords( 'months-short' ) );

		$page->js->addScript( 'var monthNames = '.json_encode( $monthsLong).';' );
		$page->js->addScript( 'var monthNamesShort = '.json_encode( $monthsShort).';' );

		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'WorkMissionsCalendar.js' );
		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'WorkMissionsEditor.js' );
		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'WorkMissionsFilter.js' );
		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'WorkMissionsList.js' );
		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'WorkMissions.js' );

/*		$this->config		= $this->env->getConfig();
		$this->session		= $this->env->getSession();
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
*/	}

	public function add(){
	}

/*	public function ajaxRenderLists(){
		xmp( $this->loadTemplateFile( 'work/mission/index.day.php' ) );
		die;
	}*/
	public function calendar(){
	}

	public function edit(){
	}

	public function index(){
		$page		= $this->env->getPage();
		$mode		= $this->env->getSession()->get( 'filter.work.mission.mode' );
		$page->js->addScript( '$(document).ready(function(){WorkMissions.init("now");});' );
	}

	public function remove(){
	}

	public function view(){
	}
}
?>