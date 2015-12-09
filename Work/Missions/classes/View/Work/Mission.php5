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

	public function help(){
		$topic	= $this->getData( 'topic' );
		if( $topic == "sync" ){
			return $this->loadContentFile( 'html/work/mission/export.html' );
		}
		return "HELP";
	}

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

	public function edit(){
	}

	public function index(){
		$page		= $this->env->getPage();
//		$page->js->addScriptOnReady( 'WorkMissions.init("now");' );			//  @deprecated use Page::runScript instead
		$page->runScript( 'WorkMissions.init("now");', 9 );
	}

	public function remove(){
	}

	public function view(){
		$page			= $this->env->getPage();
		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'WorkMissionsViewer.js' );
	}
}
?>
