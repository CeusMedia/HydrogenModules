<?php
class View_Helper_Work_Time_Buttons
{
	protected $buttons	= array();
	protected $env;
	protected $from;
	protected $size;
	protected $userId;
	protected $workerId;
	protected $modelTimer;

	public function __construct( CMF_Hydrogen_Environment $env )
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
		$buttons	= array();
		$modals		= array();
		foreach( $this->buttons as $buttonKey ){
			switch( $buttonKey ){
				case 'start':
					$iconStart	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-play' ) );
					$urlStart	= './work/time/start/'.$timer->workTimerId.'?from='.$this->from;
					if( $timer->status != 1 && ( 0 && $userIsOwner || $userIsWorker ) )
						$buttons[] 	= UI_HTML_Tag::create( 'a', $iconStart, array(
							'href'		=> $urlStart,
							'class'		=> 'btn btn-mini btn-success',
						) );
					else
						$buttons[] 	= UI_HTML_Tag::create( 'button', $iconStart, array(
							'type'		=> 'button',
							'class'		=> 'btn btn-mini btn-success',
							'disabled'	=> 'disabled',
						) );
					break;
				case 'pause':
					$iconPause	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-pause' ) );
					$urlPause	= './work/time/pause/'.$timer->workTimerId.'?from='.$this->from;
					if( $timer->status != 2 && ( 0 && $userIsOwner || $userIsWorker ) )
						$buttons[]	= UI_HTML_Tag::create( 'a', $iconPause, array(
							'href'		=> $urlPause,
							'class'		=> 'btn btn-mini btn-warning',
						) );
					else
						$buttons[]	= UI_HTML_Tag::create( 'button', $iconPause, array(
							'type'		=> 'button',
							'class'		=> 'btn btn-mini btn-warning',
							'disabled'	=> 'disabled',
						) );
					break;
				case 'stop':
					$iconStop	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-stop' ) );
					$urlStop	= './work/time/stop/'.$timer->workTimerId.'?from='.$this->from;
					if( $timer->status != 3 && ( 0 && $userIsOwner || $userIsWorker ) )
						$buttons[] 	= UI_HTML_Tag::create( 'a', $iconStop, array(
							'href'		=> $urlStop,
							'class'		=> 'btn btn-mini btn-danger',
						) );
					else
						$buttons[] 	= UI_HTML_Tag::create( 'button', $iconStop, array(
							'type'		=> 'button',
							'class'		=> 'btn btn-mini btn-danger',
							'disabled'	=> 'disabled',
						) );
					break;
				case 'mission-view':
					$iconView	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye-open' ) );
					$urlView	= './work/mission/view/'.$timer->missionId;
					$buttons[] 	= UI_HTML_Tag::create( 'a', $iconView, array(
						'href'		=> $urlView,
						'class'		=> 'btn btn-mini btn-info',
					) );
					break;
				case 'edit':
					$iconEdit	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-pencil' ) );
					$urlEdit	= './work/time/edit/'.$timer->workTimerId.'?from='.$this->from;
					if( $timer->status != 1 && ( $userIsOwner || $userIsWorker ) )
						$buttons[] 	= UI_HTML_Tag::create( 'a', $iconEdit, array(
							'href'		=> $urlEdit,
							'class'		=> 'btn btn-mini',
						) );
					else
						$buttons[] 	= UI_HTML_Tag::create( 'button', $iconEdit, array(
							'type'		=> 'button',
							'class'		=> 'btn btn-mini',
							'disabled'	=> 'disabled',
					) );
					break;
				case 'not-yet-message':
					$iconMessage	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-envelope-o' ) );
					if( $userIsOwner || !$userIsWorker ){
						$modal		= new \CeusMedia\Bootstrap\Modal( 'work-time-timer-message-'.$timer->workTimerId );
						$modal->setHeading( 'Nachricht bezüglicher dieser Aufgabe' );
						$modal->setBody( '...' );
//						$modal->setFormUrl( '...' );
						$modals[]	= $modal;
						$trigger	= new \CeusMedia\Bootstrap\Modal\Trigger();
						$trigger->setModalId( 'work-time-timer-message-'.$timer->workTimerId );
						$trigger->setLabel( $iconMessage );
						$trigger->setAttributes( array( 'class' => 'btn btn-mini btn-info' ) );
						$buttons[]	= $trigger->render();
					}
					break;
			}
		}
		return UI_HTML_Tag::create( 'div', $buttons, array( 'class' => 'btn-group' ) ).join( $modals );
	}

	static public function renderStatic( CMF_Hydrogen_Environment $env, $timerId, $userId, $buttons, $size, $from ){
		$helper	= new self( $env );
		$helper->setTimerId( $userId );
		$helper->setUserId( $userId );
		$helper->setButtons( $buttons );
		$helper->setSize( $size );
		$helper->setFrom( $from );
		return $helper->render();
	}

	public function setButtons( array $buttons = array() ): self
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
