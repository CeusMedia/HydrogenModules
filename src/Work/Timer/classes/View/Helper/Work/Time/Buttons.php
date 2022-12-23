<?php
use CeusMedia\Bootstrap\Modal\Dialog as BootstrapModalDialog;
use CeusMedia\Bootstrap\Modal\Trigger as BootstrapModalTrigger;
use CeusMedia\Common\UI\HTML\Tag as Html;
use CeusMedia\HydrogenFramework\Environment;


class View_Helper_Work_Time_Buttons
{
	protected array $buttons	= [];
	protected Environment $env;
	protected $from;
	protected $size;
	protected $userId;
	protected $workerId;
	protected Model_Work_Timer $modelTimer;
	protected $timerId;

	public function __construct( Environment $env )
	{
		$this->env			= $env;
		$this->modelTimer	= new Model_Work_Timer( $this->env );
	}

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
						$buttons[] 	= Html::create( 'a', $iconStart, array(
							'href'		=> $urlStart,
							'class'		=> 'btn btn-mini btn-success',
						) );
					else
						$buttons[] 	= Html::create( 'button', $iconStart, array(
							'type'		=> 'button',
							'class'		=> 'btn btn-mini btn-success',
							'disabled'	=> 'disabled',
						) );
					break;
				case 'pause':
					$iconPause	= Html::create( 'i', '', ['class' => 'fa fa-fw fa-pause'] );
					$urlPause	= './work/time/pause/'.$timer->workTimerId.'?from='.$this->from;
					if( $timer->status != 2 && ( 0 && $userIsOwner || $userIsWorker ) )
						$buttons[]	= Html::create( 'a', $iconPause, array(
							'href'		=> $urlPause,
							'class'		=> 'btn btn-mini btn-warning',
						) );
					else
						$buttons[]	= Html::create( 'button', $iconPause, array(
							'type'		=> 'button',
							'class'		=> 'btn btn-mini btn-warning',
							'disabled'	=> 'disabled',
						) );
					break;
				case 'stop':
					$iconStop	= Html::create( 'i', '', ['class' => 'fa fa-fw fa-stop'] );
					$urlStop	= './work/time/stop/'.$timer->workTimerId.'?from='.$this->from;
					if( $timer->status != 3 && ( 0 && $userIsOwner || $userIsWorker ) )
						$buttons[] 	= Html::create( 'a', $iconStop, array(
							'href'		=> $urlStop,
							'class'		=> 'btn btn-mini btn-danger',
						) );
					else
						$buttons[] 	= Html::create( 'button', $iconStop, array(
							'type'		=> 'button',
							'class'		=> 'btn btn-mini btn-danger',
							'disabled'	=> 'disabled',
						) );
					break;
				case 'mission-view':
					$iconView	= Html::create( 'i', '', ['class' => 'fa fa-fw fa-eye-open'] );
					$urlView	= './work/mission/view/'.$timer->missionId;
					$buttons[] 	= Html::create( 'a', $iconView, array(
						'href'		=> $urlView,
						'class'		=> 'btn btn-mini btn-info',
					) );
					break;
				case 'edit':
					$iconEdit	= Html::create( 'i', '', ['class' => 'fa fa-fw fa-pencil'] );
					$urlEdit	= './work/time/edit/'.$timer->workTimerId.'?from='.$this->from;
					if( $timer->status != 1 && ( $userIsOwner || $userIsWorker ) )
						$buttons[] 	= Html::create( 'a', $iconEdit, array(
							'href'		=> $urlEdit,
							'class'		=> 'btn btn-mini',
						) );
					else
						$buttons[] 	= Html::create( 'button', $iconEdit, array(
							'type'		=> 'button',
							'class'		=> 'btn btn-mini',
							'disabled'	=> 'disabled',
					) );
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

	public static function renderStatic( Environment $env, $timerId, $userId, $buttons, $size, $from )
	{
		$helper	= new self( $env );
		$helper->setTimerId( $userId );
		$helper->setUserId( $userId );
		$helper->setButtons( $buttons );
		$helper->setSize( $size );
		$helper->setFrom( $from );
		return $helper->render();
	}

	public function setButtons( array $buttons = [] ): self
	{
		$this->buttons	= $buttons;
		return $this;
	}

	public function setFrom( $from ): self
	{
		$this->from	= $from;
		return $this;
	}

	public function setSize( $size ): self
	{
		$this->size	= $size;
		return $this;
	}

	public function setTimerId( $timerId ): self
	{
		$this->timerId	= $timerId;
		return $this;
	}

	public function setUserId( $userId ): self
	{
		$this->userId	= $userId;
		return $this;
	}
}