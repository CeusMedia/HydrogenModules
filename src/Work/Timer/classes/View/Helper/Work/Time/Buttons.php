<?php
use CeusMedia\Bootstrap\Modal\Dialog as BootstrapModalDialog;
use CeusMedia\Bootstrap\Modal\Trigger as BootstrapModalTrigger;
use CeusMedia\Common\UI\HTML\Tag as Html;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Work_Time_Buttons
{
	protected Environment $env;
	protected Model_Work_Timer $modelTimer;
	protected array $buttons					= [];
	protected ?string $from						= NULL;
	protected ?string $size						= NULL;
	protected int|string|NULL $userId			= NULL;
	protected int|string|NULL $workerId			= NULL;
	protected int|string|NULL $timerId			= NULL;

	/**
	 *	@param		Environment		$env
	 */
	public function __construct( Environment $env )
	{
		$this->env			= $env;
		$this->modelTimer	= new Model_Work_Timer( $this->env );
	}

	/**
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function render(): string
	{
		if( !$this->userId )
			throw new RuntimeException( 'No user ID set' );
		if( !$this->timerId )
			throw new RuntimeException( 'No timer ID set' );
		$timer	= $this->modelTimer->get( $this->timerId );
		if( !$timer )
			throw new RangeException( 'Invalid ID set' );
		$userIsOwner	= $timer->userId == $this->userId;
		$userIsWorker	= $timer->workerId == $this->userId;
		$buttons	= [];
		$modals		= [];
		foreach( $this->buttons as $buttonKey ){
			switch( $buttonKey ){
				case 'start':
					$iconStart	= Html::create( 'i', '', ['class' => 'fa fa-fw fa-play'] );
					$urlStart	= './work/time/start/'.$timer->workTimerId.'?from='.$this->from;
					if( $timer->status != 1 && ( 0 && $userIsOwner || $userIsWorker ) )
						$buttons[] 	= Html::create( 'a', $iconStart, [
							'href'		=> $urlStart,
							'class'		=> 'btn btn-mini btn-success',
						] );
					else
						$buttons[] 	= Html::create( 'button', $iconStart, [
							'type'		=> 'button',
							'class'		=> 'btn btn-mini btn-success',
							'disabled'	=> 'disabled',
						] );
					break;
				case 'pause':
					$iconPause	= Html::create( 'i', '', ['class' => 'fa fa-fw fa-pause'] );
					$urlPause	= './work/time/pause/'.$timer->workTimerId.'?from='.$this->from;
					if( $timer->status != 2 && ( 0 && $userIsOwner || $userIsWorker ) )
						$buttons[]	= Html::create( 'a', $iconPause, [
							'href'		=> $urlPause,
							'class'		=> 'btn btn-mini btn-warning',
						] );
					else
						$buttons[]	= Html::create( 'button', $iconPause, [
							'type'		=> 'button',
							'class'		=> 'btn btn-mini btn-warning',
							'disabled'	=> 'disabled',
						] );
					break;
				case 'stop':
					$iconStop	= Html::create( 'i', '', ['class' => 'fa fa-fw fa-stop'] );
					$urlStop	= './work/time/stop/'.$timer->workTimerId.'?from='.$this->from;
					if( $timer->status != 3 && ( 0 && $userIsOwner || $userIsWorker ) )
						$buttons[] 	= Html::create( 'a', $iconStop, [
							'href'		=> $urlStop,
							'class'		=> 'btn btn-mini btn-danger',
						] );
					else
						$buttons[] 	= Html::create( 'button', $iconStop, [
							'type'		=> 'button',
							'class'		=> 'btn btn-mini btn-danger',
							'disabled'	=> 'disabled',
						] );
					break;
				case 'mission-view':
					$iconView	= Html::create( 'i', '', ['class' => 'fa fa-fw fa-eye-open'] );
					$urlView	= './work/mission/view/'.$timer->missionId;
					$buttons[] 	= Html::create( 'a', $iconView, [
						'href'		=> $urlView,
						'class'		=> 'btn btn-mini btn-info',
					] );
					break;
				case 'edit':
					$iconEdit	= Html::create( 'i', '', ['class' => 'fa fa-fw fa-pencil'] );
					$urlEdit	= './work/time/edit/'.$timer->workTimerId.'?from='.$this->from;
					if( $timer->status != 1 && ( $userIsOwner || $userIsWorker ) )
						$buttons[] 	= Html::create( 'a', $iconEdit, [
							'href'		=> $urlEdit,
							'class'		=> 'btn btn-mini',
						] );
					else
						$buttons[] 	= Html::create( 'button', $iconEdit, [
							'type'		=> 'button',
							'class'		=> 'btn btn-mini',
							'disabled'	=> 'disabled',
					] );
					break;
				case 'not-yet-message':
					$iconMessage	= Html::create( 'i', '', ['class' => 'fa fa-fw fa-envelope-o'] );
					if( $userIsOwner || !$userIsWorker ){
						$modal		= new BootstrapModalDialog( 'work-time-timer-message-'.$timer->workTimerId );
						$modal->setHeading( 'Nachricht bezüglicher dieser Aufgabe' );
						$modal->setBody( '...' );
//						$modal->setFormUrl( '...' );
						$modals[]	= $modal;
						$trigger	= new BootstrapModalTrigger( 'work-time-timer-message-'.$timer->workTimerId );
						$trigger->setLabel( $iconMessage );
						$trigger->setAttributes( ['class' => 'btn btn-mini btn-info'] );
						$buttons[]	= $trigger->render();
					}
					break;
			}
		}
		return Html::create( 'div', $buttons, ['class' => 'btn-group'] ).join( $modals );
	}

	/**
	 *	@param		Environment		$env
	 *	@param		int|string		$timerId
	 *	@param		int|string		$userId
	 *	@param		array			$buttons
	 *	@param		string			$size
	 *	@param		string			$from
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public static function renderStatic( Environment $env, int|string $timerId, int|string $userId, array $buttons, string $size, string $from ): string
	{
		$helper	= new self( $env );
		$helper->setTimerId( $timerId );
		$helper->setUserId( $userId );
		$helper->setButtons( $buttons );
		$helper->setSize( $size );
		$helper->setFrom( $from );
		return $helper->render();
	}

	/**
	 *	@param		array		$buttons
	 *	@return		self
	 */
	public function setButtons( array $buttons = [] ): self
	{
		$this->buttons	= $buttons;
		return $this;
	}

	/**
	 *	@param		string		$from
	 *	@return		self
	 */
	public function setFrom( string $from ): self
	{
		$this->from	= $from;
		return $this;
	}

	/**
	 *	@param		string		$size
	 *	@return		self
	 */
	public function setSize( string $size ): self
	{
		$this->size	= $size;
		return $this;
	}

	/**
	 *	@param		int|string		$timerId
	 *	@return		self
	 */
	public function setTimerId( int|string $timerId ): self
	{
		$this->timerId	= $timerId;
		return $this;
	}

	/**
	 *	@param		int|string		$userId
	 *	@return		self
	 */
	public function setUserId( int|string $userId ): self
	{
		$this->userId	= $userId;
		return $this;
	}
}
