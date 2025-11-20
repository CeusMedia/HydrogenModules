<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Modal_Work_Meeting_Display extends View_Helper_Bootstrap_Modal
{
	public View_Helper_Bootstrap_Modal_Trigger $trigger;

	protected ?Entity_Work_Meeting $meeting		= NULL;
	protected array $moduleWords;
	protected bool $userRoles					= TRUE;

	public function __construct( Environment $env )
	{
		parent::__construct( $env );
		$this->setHeading( 'Meeting' )
			->setFade( FALSE )
			->setButtonLabelCancel( 'Okay' );
		$this->moduleWords	= $this->env->getLanguage()->getWords( 'work/meeting' );
	}

	public function render(): string
	{
		$this->setBody( $this->renderBody() );
		return parent::render();
	}

	public function setMeeting( Entity_Work_Meeting $meeting ): self
	{
		$this->meeting	= $meeting;
		$this->setId('meeting-'.$meeting->meetingId );
		return $this;
	}

	public function useRoles( bool $switch ): self
	{
		$this->useRoles	= $switch;
		return $this;
	}


	//  --  PROTECTED  --  //


	protected function renderBody(): string
	{
		$location	= $this->meeting->location;
		if( '' !== ( $this->meeting->link ?? '' ) )
			$location	= HtmlTag::create( 'a', $location, ['href' => $this->meeting->link] );
		$blockLocation = join( [
			HtmlTag::create( 'div', 'Ort', ['class' => 'form-view-label'] ),
			HtmlTag::create( 'div', $location, ['class' => 'form-view-value'] ),
		] );

		$participants	= [];
		foreach( $this->meeting->participants as $participant )
			$participants[]	= HtmlTag::create( 'div', $this->renderParticipant( $participant ), [ 'class' => 'user-avatar-item' ] );
		$blockParticipants = join( [
			HtmlTag::create( 'div', 'Teilnehmer', ['class' => 'form-view-label'] ),
			HtmlTag::create( 'div', $participants, ['class' => 'user-avatar-list'] ),
		] );

		$blockContent	= '';
		if( '' !== ( $this->meeting->content ?? '' ) )
			$blockContent = join( [
				HtmlTag::create( 'div', 'Beschreibung', ['class' => 'form-view-label'] ),
				HtmlTag::create( 'div', $this->meeting->content, ['class' => ''] ),
			] );

		$status	= $this->moduleWords['statuses'][$this->meeting->status];

		$w	= (object) $this->moduleWords['edit'];
		$blockDateTime	= '
					<div class="row-fluid">
						<div class="span4">
							<div class="form-view-label">'.$w->labelDateStart_date.'</div>
							<div class="form-view-value">'.date( 'Y-m-d', strtotime( $this->meeting->dateStart ) ).'</div>
						</div>
						<div class="span4">
							<div class="form-view-label">'.$w->labelDateEnd_date.'</div>
							<div class="form-view-value">'.date( 'Y-m-d', strtotime( $this->meeting->dateEnd ) ).'</div>
						</div>
						<div class="span4">
							'.join( [
				HtmlTag::create( 'div', 'Ort', ['class' => 'form-view-label'] ),
				HtmlTag::create( 'div', $location, ['class' => 'form-view-value'] ),
			] ).'
						</div>
					</div>
					<div class="row-fluid">
						<div class="span4">
							<div class="form-view-label">'.$w->labelDateStart_time.'</div>
							<div class="form-view-value">'.date( 'H:i:s', strtotime( $this->meeting->dateStart ) ).'</div>
						</div>
						<div class="span4">
							<div class="form-view-label">'.$w->labelDateEnd_time.'</div>
							<div class="form-view-value">'.date( 'H:i:s', strtotime( $this->meeting->dateEnd ) ).'</div>
						</div>
						<div class="span4">
							'.join( [
				HtmlTag::create( 'div', 'aktueller Zustand', ['class' => 'form-view-label'] ),
				HtmlTag::create( 'div', $status, ['class' => 'form-view-value'] ),
			] ).'
						</div>
					</div>
		';

		return '
<h4>'.$this->meeting->title.'</h4>
'.$blockParticipants.'<br/>
'.$blockDateTime.'
'.$blockContent;
	}

	/**
	 *	@param		Entity_Work_Meeting_Participant	$participant
	 *	@param		bool							$editMode		Flag: add remove button, default: no
	 *	@return		string
	 */
	protected function renderParticipant( Entity_Work_Meeting_Participant $participant, bool $editMode = FALSE ): string
	{
		$gravatar	= 'https://www.gravatar.com/avatar/'.md5( strtolower( trim( $participant->user->email ) ) ).'?s=32&d=mm&r=g';
		$gravatar	= HtmlTag::create( 'img', NULL, ['src' => $gravatar, 'class' => 'avatar'] );

		$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
		$remove		= '';
		if( $editMode )
			$remove		= HtmlTag::create( 'a', $iconRemove, [
				'href'	=> './work/meeting/removeParticipant/'.$this->meeting->meetingId.'/'.$participant->userId,
				'class'	=> 'pull-right',
				'title'	=> 'entfernen',
			] );

		if( $this->useRoles )
			$subtext	= HtmlTag::create( 'small', $this->moduleWords['types'][$participant->type], ['class' => 'muted'] );
		else
			$subtext	= HtmlTag::create( 'small', $participant->user->firstname.' '.$participant->user->surname, ['class' => 'muted'] );

		$username	= HtmlTag::create( 'div', $participant->user->username.$remove, [
			'class'		=> 'username',
			'title'		=> $participant->user->firstname.' '.$participant->user->surname],
		);
		return $gravatar.$username.$subtext;
	}
}



