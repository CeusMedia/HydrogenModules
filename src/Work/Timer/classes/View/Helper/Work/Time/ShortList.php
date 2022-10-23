<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Work_Time_ShortList extends View_Helper_Work_Time
{
	protected $ownerId		= NULL;
	protected $workerId		= NULL;
	protected $status		= NULL;
	protected $projectId	= NULL;
	protected $module		= NULL;
	protected $moduleId		= NULL;
	protected $buttons		= ['start', 'pause', 'stop'];
	protected $limits		= [0, 20];
	protected $orders		= ['createdAt' => 'ASC'];

	public function render(): string
	{
		$conditions	= [];
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

		$rows		= [];
		foreach( $timers as $timer ){
			View_Helper_Work_Time_Timer::decorateTimer( $this->env, $timer, FALSE );

			$linkRelation	= $this->renderRelationLink( $timer );
			$title			= $this->renderTitleLink( $timer );
			$worker			= $this->renderWorker( $timer );
			$time			= $this->renderTimes( $timer );
			$buttonGroup	= $this->renderButtons( $timer );

			$buttons		= HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', $buttonGroup, ['class' => 'span4']  ),
				HtmlTag::create( 'div', $time , ['class' => 'span8', 'style' => 'text-align: right'] ),
			), ['class' => 'row-fluid'] );

			$rowClass		= $timer->status == 1 ? 'success' : ( $timer->status == 2 ? 'notice' : '' );
			$rows[]			= HtmlTag::create( 'tr', array(
				HtmlTag::create( 'td', array(
					$title,
					$worker,
					$linkRelation,
					$buttons
				) ),
			), ['class' => $rowClass] );
		}
		$tableHeads	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'th', 'Aktivität' ),
		) );
		$table		= HtmlTag::create( 'table', array(
			HtmlTag::create( 'thead', $tableHeads ),
			HtmlTag::create( 'tbody', $rows ),
		), array(
			'class'	=> 'table table-striped table-condensed',
			'style'	=> 'table-layout: fixed'
		) );
		$script		= 'WorkTimer.init(".timer-short-list", "&nbsp;");';
		$this->env->getPage()->js->addScriptOnReady( $script );
		return $table;
	}

	public function setButtons( $buttons ): self
	{
		$this->buttons	= $buttons;
		return $this;
	}

	public function setLimits( $limit, $offset = 0 ): self
	{
		$limit			= min( 100, max( 1, $limit ) );
		$offset			= max( 0, $offset );
		$this->limits	= [$offset, $limit];
		return $this;
	}

	public function setModule( $module ): self
	{
		$this->module		= $module;
		return $this;
	}

	public function setModuleId( $moduleId ): self
	{
		if( !$this->module )
			throw new RuntimeException( 'No module set beforehand' );
		$this->moduleId	= $moduleId;
		return $this;
	}

	public function setOrders( $orders ): self
	{
		$this->orders		= $orders;
		return $this;
	}

	public function setOwnerId( $userId ): self
	{
		$this->ownerId	= $userId;
		return $this;
	}

	public function setProjectId( $projectId ): self
	{
		$this->projectId	= $projectId;
		return $this;
	}

	public function setStatus( $status ): self
	{
		$this->status	= $status;
		return $this;
	}

	public function setWorkerId( $userId ): self
	{
		$this->workerId	= $userId;
		return $this;
	}

	//  --  PROTECTED  --  //

	protected function renderButtons( $timer ): string
	{
		$helperButtons	= new View_Helper_Work_Time_Buttons( $this->env );
		$helperButtons->setUserId( $this->userId );
		$helperButtons->setButtons( $this->buttons );
		$helperButtons->setSize( 'mini' );
		$helperButtons->setFrom( $this->from );
		$helperButtons->setTimerId( $timer->workTimerId );
		$buttonGroup	= $helperButtons->render();
		return $buttonGroup;
	}

	protected function renderRelationLink( $timer ): string
	{
		if( !$timer->moduleId )
			return '';
		$labelType		= HtmlTag::create( 'span', $timer->type.':', array(
			'class' => 'muted',
		) );
		$linkRelation	= HtmlTag::create( 'a', htmlentities( $timer->relationTitle, ENT_QUOTES, 'UTF-8' ), array(
			'href'		=> $timer->relationLink,
			'class'		=> 'title autocut',
		) );
		$linkRelation	= HtmlTag::create( 'small', [$labelType, $linkRelation] );
		return HtmlTag::create( 'div', $linkRelation, ['class' => 'autocut'] );
	}

	protected function renderTimes( $timer ): string
	{
		$secondsPlanned	= $timer->secondsPlanned;
		$secondsNeeded	= $timer->status == 1 ? $timer->secondsNeeded + ( time() - $timer->modifiedAt ) : $timer->secondsNeeded;
		$classes		= [];
		if( $timer->status == 1 )
			$classes[]	= 'timer-short-list';
		$timeNeeded		= HtmlTag::create( 'small', View_Helper_Work_Time::formatSeconds( $secondsNeeded, '&nbsp;' ), array(
			'class'			=> join( ' ', $classes ),
			'data-value'	=> $secondsNeeded,
		) );
		$classes	= [];
		$timePlanned	= HtmlTag::create( 'small', View_Helper_Work_Time::formatSeconds( $secondsPlanned, '&nbsp;' ), array(
			'class'			=> join( ' ', $classes ),
			'data-value'	=> $secondsPlanned,
		) );
		return $timeNeeded.' / '.$timePlanned;
	}

	protected function renderTitleLink( $timer ): string
	{
		$title	= strlen( trim( $timer->title ) ) ? htmlentities( $timer->title, ENT_QUOTES, 'UTF-8' ) : '<em class="muted">unbenannt</em>';
		$title	= HtmlTag::create( 'a', $title, ['href' => './work/time/edit/'.$timer->workTimerId.'?from='.$this->from] );
		$title	= HtmlTag::create( 'div', $title, ['class' => 'autocut'] );
		return $title;
	}

	protected function renderWorker( $timer ): string
	{
		if( !class_exists( 'View_Helper_Member' ) )
			return '';
		$helper	= new View_Helper_Member( $this->env );
		$helper->setUser( $timer->workerId );
		$helper->setMode( 'inline' );
		$label	= HtmlTag::create( 'span', 'Bearbeiter: ', ['class' => 'muted'] );
		return HtmlTag::create( 'div', $label.$helper->render(), ['class' => 'autocut'] );
	}
}
