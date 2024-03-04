<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Work_Time_ShortList extends View_Helper_Work_Time
{
	protected array $buttons		= ['start', 'pause', 'stop'];
	protected array $limits			= [0, 20];
	protected array $orders			= ['createdAt' => 'ASC'];
	protected int|string|NULL $ownerId		= NULL;
	protected int|string|NULL $workerId		= NULL;
	protected array $status			= [];
	protected ?string $projectId	= NULL;
	protected ?string $module		= NULL;
	protected ?string $moduleId		= NULL;

	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
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

//		$total		= $this->modelTimer->count( $conditions );
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

			$buttons		= HtmlTag::create( 'div', [
				HtmlTag::create( 'div', $buttonGroup, ['class' => 'span4']  ),
				HtmlTag::create( 'div', $time , ['class' => 'span8', 'style' => 'text-align: right'] ),
			], ['class' => 'row-fluid'] );

			$rowClass		= $timer->status == 1 ? 'success' : ( $timer->status == 2 ? 'notice' : '' );
			$rows[]			= HtmlTag::create( 'tr', [
				HtmlTag::create( 'td', [
					$title,
					$worker,
					$linkRelation,
					$buttons
				] ),
			], ['class' => $rowClass] );
		}
		$tableHeads	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'th', 'Aktivität' ),
		] );
		$table		= HtmlTag::create( 'table', [
			HtmlTag::create( 'thead', $tableHeads ),
			HtmlTag::create( 'tbody', $rows ),
		], [
			'class'	=> 'table table-striped table-condensed',
			'style'	=> 'table-layout: fixed'
		] );
		$script		= 'WorkTimer.init(".timer-short-list", "&nbsp;");';
		$this->env->getPage()->js->addScriptOnReady( $script );
		return $table;
	}

	/**
	 *	@param		array		$buttons
	 *	@return		self
	 */
	public function setButtons( array $buttons ): self
	{
		$this->buttons	= $buttons;
		return $this;
	}

	/**
	 *	@param		int			$limit
	 *	@param		int			$offset
	 *	@return		self
	 */
	public function setLimits( int $limit, int $offset = 0 ): self
	{
		$limit			= min( 100, max( 1, $limit ) );
		$offset			= max( 0, $offset );
		$this->limits	= [$offset, $limit];
		return $this;
	}

	/**
	 *	@param		string		$module
	 *	@return		self
	 */
	public function setModule( string $module ): self
	{
		$this->module		= $module;
		return $this;
	}

	/**
	 *	@param		string		$moduleId
	 *	@return		self
	 */
	public function setModuleId( string $moduleId ): self
	{
		if( !$this->module )
			throw new RuntimeException( 'No module set beforehand' );
		$this->moduleId	= $moduleId;
		return $this;
	}

	/**
	 *	@param		array		$orders
	 *	@return		self
	 */
	public function setOrders( array $orders ): self
	{
		$this->orders		= $orders;
		return $this;
	}

	/**
	 *	@param		int|string		$userId
	 *	@return		self
	 */
	public function setOwnerId( int|string $userId ): self
	{
		$this->ownerId	= $userId;
		return $this;
	}

	/**
	 *	@param		int|string		$projectId
	 *	@return		self
	 */
	public function setProjectId( int|string $projectId ): self
	{
		$this->projectId	= $projectId;
		return $this;
	}

	public function setStatus( $status ): self
	{
		$this->status	= $status;
		return $this;
	}

	/**
	 *	@param		int|string		$userId
	 *	@return		self
	 */
	public function setWorkerId( int|string $userId ): self
	{
		$this->workerId	= $userId;
		return $this;
	}

	//  --  PROTECTED  --  //

	/**
	 *	@param		object		$timer
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function renderButtons( object $timer ): string
	{
		$helperButtons	= new View_Helper_Work_Time_Buttons( $this->env );
		$helperButtons->setUserId( $this->userId );
		$helperButtons->setButtons( $this->buttons );
		$helperButtons->setSize( 'mini' );
		$helperButtons->setFrom( $this->from );
		$helperButtons->setTimerId( $timer->workTimerId );
		return $helperButtons->render();
	}

	/**
	 *	@param		object		$timer
	 *	@return		string
	 */
	protected function renderRelationLink( object $timer ): string
	{
		if( !$timer->moduleId )
			return '';
		$labelType		= HtmlTag::create( 'span', $timer->type.':', [
			'class' => 'muted',
		] );
		$linkRelation	= HtmlTag::create( 'a', htmlentities( $timer->relationTitle, ENT_QUOTES, 'UTF-8' ), [
			'href'		=> $timer->relationLink,
			'class'		=> 'title autocut',
		] );
		$linkRelation	= HtmlTag::create( 'small', [$labelType, $linkRelation] );
		return HtmlTag::create( 'div', $linkRelation, ['class' => 'autocut'] );
	}

	/**
	 *	@param		object		$timer
	 *	@return		string
	 */
	protected function renderTimes( object $timer ): string
	{
		$secondsPlanned	= $timer->secondsPlanned;
		$secondsNeeded	= $timer->status == 1 ? $timer->secondsNeeded + ( time() - $timer->modifiedAt ) : $timer->secondsNeeded;
		$classes		= [];
		if( $timer->status == 1 )
			$classes[]	= 'timer-short-list';
		$timeNeeded		= HtmlTag::create( 'small', View_Helper_Work_Time::formatSeconds( $secondsNeeded, '&nbsp;' ), [
			'class'			=> join( ' ', $classes ),
			'data-value'	=> $secondsNeeded,
		] );
		$classes	= [];
		$timePlanned	= HtmlTag::create( 'small', View_Helper_Work_Time::formatSeconds( $secondsPlanned, '&nbsp;' ), [
			'class'			=> join( ' ', $classes ),
			'data-value'	=> $secondsPlanned,
		] );
		return $timeNeeded.' / '.$timePlanned;
	}

	/**
	 *	@param		object		$timer
	 *	@return		string
	 */
	protected function renderTitleLink( object $timer ): string
	{
		$title	= strlen( trim( $timer->title ) ) ? htmlentities( $timer->title, ENT_QUOTES, 'UTF-8' ) : '<em class="muted">unbenannt</em>';
		$title	= HtmlTag::create( 'a', $title, ['href' => './work/time/edit/'.$timer->workTimerId.'?from='.$this->from] );
		return HtmlTag::create( 'div', $title, ['class' => 'autocut'] );
	}

	/**
	 *	@param		object		$timer
	 *	@return		string
	 */
	protected function renderWorker( object $timer ): string
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
