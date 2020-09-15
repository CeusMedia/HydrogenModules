<?php
class View_Helper_Work_Time_ShortList extends View_Helper_Work_Time{

	protected $ownerId		= NULL;
	protected $workerId		= NULL;
	protected $status		= NULL;
	protected $projectId	= NULL;
	protected $module		= NULL;
	protected $moduleId		= NULL;
	protected $buttons		= array( 'start', 'pause', 'stop' );
	protected $limits		= array( 0, 20 );
	protected $orders		= array( 'createdAt' => 'ASC' );

	public function render(){
		$conditions	= array();
//		$conditions['userId']		= (int) $this->userId;
		if( $this->ownerId )
			$conditions['userId']	= $this->ownerId;
		if( $this->workerId )
			$conditions['workerId']	= $this->workerId;
		if( $this->status )
			$conditions['status']	= $this->status;
		if( $this->module )
			$conditions['module']	= $this->module;
		if( $this->moduleId )
			$conditions['moduleId']	= $this->moduleId;

		$total		= $this->modelTimer->count( $conditions );
		$timers		= $this->modelTimer->getAll( $conditions, $this->orders, $this->limits );
		if( !$timers )
			return '';

		$rows		= array();
		foreach( $timers as $timer ){
			View_Helper_Work_Time_Timer::decorateTimer( $this->env, $timer );

			$linkRelation	= $this->renderRelationLink( $timer );
			$title			= $this->renderTitleLink( $timer );
			$worker			= $this->renderWorker( $timer );
			$time			= $this->renderTimes( $timer );
			$buttonGroup	= $this->renderButtons( $timer );

			$buttons		= UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', $buttonGroup, array( 'class' => 'span4' )  ),
				UI_HTML_Tag::create( 'div', $time , array( 'class' => 'span8', 'style' => 'text-align: right' ) ),
			), array( 'class' => 'row-fluid' ) );

			$rowClass		= $timer->status == 1 ? 'success' : ( $timer->status == 2 ? 'notice' : '' );
			$rows[]			= UI_HTML_Tag::create( 'tr', array(
				UI_HTML_Tag::create( 'td', array(
					$title,
					$worker,
					$linkRelation,
					$buttons
				) ),
			), array( 'class' => $rowClass ) );
		}
		$tableHeads	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'th', 'Aktivität' ),
		) );
		$table		= UI_HTML_Tag::create( 'table', array(
			UI_HTML_Tag::create( 'thead', $tableHeads ),
			UI_HTML_Tag::create( 'tbody', $rows ),
		), array(
			'class'	=> 'table table-striped table-condensed',
			'style'	=> 'table-layout: fixed'
		) );
		$script		= 'WorkTimer.init(".timer-short-list", "&nbsp;");';
		$this->env->getPage()->js->addScriptOnReady( $script );
		return $table;
	}

	protected function renderButtons( $timer ){
		$helperButtons	= new View_Helper_Work_Time_Buttons( $this->env );
		$helperButtons->setUserId( $this->userId );
		$helperButtons->setButtons( $this->buttons );
		$helperButtons->setSize( 'mini' );
		$helperButtons->setFrom( $this->from );
		$helperButtons->setTimerId( $timer->workTimerId );
		$buttonGroup	= $helperButtons->render();
		return $buttonGroup;
	}

	protected function renderRelationLink( $timer ){
		if( !$timer->moduleId )
			return;
		$labelType		= UI_HTML_Tag::create( 'span', $timer->type.':', array(
			'class' => 'muted',
		) );
		$linkRelation	= UI_HTML_Tag::create( 'a', htmlentities( $timer->relationTitle, ENT_QUOTES, 'UTF-8' ), array(
			'href'		=> $timer->relationLink,
			'class'		=> 'title autocut',
		) );
		$linkRelation	= UI_HTML_Tag::create( 'small', array( $labelType, $linkRelation ) );
		return UI_HTML_Tag::create( 'div', $linkRelation, array( 'class' => 'autocut' ) );
	}

	protected function renderTimes( $timer ){
		$secondsPlanned	= $timer->secondsPlanned;
		$secondsNeeded	= $timer->status == 1 ? $timer->secondsNeeded + ( time() - $timer->modifiedAt ) : $timer->secondsNeeded;
		$classes		= array();
		if( $timer->status == 1 )
			$classes[]	= 'timer-short-list';
		$timeNeeded		= UI_HTML_Tag::create( 'small', View_Helper_Work_Time::formatSeconds( $secondsNeeded, '&nbsp;' ), array(
			'class'			=> join( ' ', $classes ),
			'data-value'	=> $secondsNeeded,
		) );
		$classes	= array();
		$timePlanned	= UI_HTML_Tag::create( 'small', View_Helper_Work_Time::formatSeconds( $secondsPlanned, '&nbsp;' ), array(
			'class'			=> join( ' ', $classes ),
			'data-value'	=> $secondsPlanned,
		) );
		return $timeNeeded.' / '.$timePlanned;
	}

	protected function renderTitleLink( $timer ){
		$title	= strlen( trim( $timer->title ) ) ? htmlentities( $timer->title, ENT_QUOTES, 'UTF-8' ) : '<em class="muted">unbenannt</em>';
		$title	= UI_HTML_Tag::create( 'a', $title, array( 'href' => './work/time/edit/'.$timer->workTimerId.'?from='.$this->from ) );
		$title	= UI_HTML_Tag::create( 'div', $title, array( 'class' => 'autocut' ) );
		return $title;
	}

	protected function renderWorker( $timer ){
		if( !class_exists( 'View_Helper_Member' ) )
			return;
		$helper	= new View_Helper_Member( $this->env );
		$helper->setUser( $timer->workerId );
		$helper->setMode( 'inline' );
		$label	= UI_HTML_Tag::create( 'span', 'Bearbeiter: ', array( 'class' => 'muted' ) );
		return UI_HTML_Tag::create( 'div', $label.$helper->render(), array( 'class' => 'autocut' ) );
	}

	public function setButtons( $buttons ){
		$this->buttons	= $buttons;
	}

	public function setLimits( $limit, $offset = 0 ){
		$limit			= min( 100, max( 1, $limit ) );
		$offset			= max( 0, $offset );
		$this->limits	= array( $offset, $limit );
	}

	public function setModule( $module ){
		$this->module		= $module;
	}

	public function setModuleId( $moduleId ){
		if( !$this->module )
			throw new RuntimeException( 'No module set beforehand' );
		$this->moduleId	= $moduleId;
	}

	public function setOrders( $orders ){
		$this->orders		= $orders;
	}

	public function setOwnerId( $userId ){
		$this->ownerId	= $userId;
	}

	public function setProjectId( $projectId ){
		$this->projectId	= $projectId;
	}

	public function setStatus( $status ){
		$this->status	= $status;
	}

	public function setWorkerId( $userId ){
		$this->workerId	= $userId;
	}
}
?>
