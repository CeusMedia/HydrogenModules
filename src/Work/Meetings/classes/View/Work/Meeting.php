<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View as View;

class View_Work_Meeting extends View
{
	protected bool $useRoles	= TRUE;

	public function add(): void
	{
	}

	public function edit(): void
	{
	}

	public function index(): void
	{
	}

	public function remove(): void
	{
	}

	public function view(): void
	{
	}

	/**
	 *	@param		Entity_Work_Meeting	$meeting
	 *	@return		string
	 */
	public function renderEditPanel( Entity_Work_Meeting $meeting ): string
	{
		$w			= $this->getWords( 'edit', NULL, TRUE );
		$iconBack	= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-arrow-left"] ).'&nbsp;';
		$iconSave	= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-check"] ).'&nbsp;';
		$iconView	= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-eye"] ).'&nbsp;';

		$warning	= '';
		if( Model_Work_Meeting::STATUS_ACTIVE === $meeting->status )
			$warning	= '
			<div class="row-fluid">
				<div class="span12">
					<div class="alert alert-danger"><strong>Achtung:</strong> Bei Änderungen werden E-Mails an die Teilnehmer versendet. Bitte sparsam mit diesem Formular umgeben.</div>
				</div>
			</div>';

		return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./work/meeting/edit/'.$meeting->meetingId.'" method="post">
			<div class="row-fluid">
				<div class="span8">
					<div class="row-fluid">
						<div class="span8">
							<label for="input_title" class="mandatory">'.$w->labelTitle.'</label>
							<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $meeting->title ?? '', ENT_QUOTES, 'UTF-8' ).'" required/>
						</div>
						<div class="span4">
							<label for="input_location" class="mandatory">'.$w->labelLocation.'</label>
							<input type="text" name="location" id="input_location" class="span12" value="'.htmlentities( $meeting->location ?? '', ENT_QUOTES, 'UTF-8' ).'" required/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_link">'.$w->labelLink.'</label>
							<input type="text" name="link" id="input_link" class="span12" value="'.htmlentities( $meeting->link ?? '', ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
				</div>
				<div class="span4">
					<div class="row-fluid">
						<div class="span6">
							<label for="input_dateStart_date" class="mandatory">'.$w->labelDateStart_date.'</label>
							<input type="date" name="dateStart_date" id="input_dateStart_date" class="span12" value="'.date( 'Y-m-d', strtotime( $meeting->dateStart ) ).'"/>
						</div>
						<div class="span6">
							<label for="input_dateEnd_date" class="mandatory">'.$w->labelDateEnd_date.'</label>
							<input type="date" name="dateEnd_date" id="input_dateEnd_date" class="span12" value="'.date( 'Y-m-d', strtotime( $meeting->dateEnd ) ).'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span6">
							<label for="input_dateStart_time" class="mandatory">'.$w->labelDateStart_time.'</label>
							<input type="time" name="dateStart_time" id="input_dateStart_time" class="span12" value="'.date( 'H:i:s', strtotime( $meeting->dateStart ) ).'"/>
						</div>
						<div class="span6">
							<label for="input_dateEnd_time" class="mandatory">'.$w->labelDateEnd_time.'</label>
							<input type="time" name="dateEnd_time" id="input_dateEnd_time" class="span12" value="'.date( 'H:i:s', strtotime( $meeting->dateEnd ) ).'"/>
						</div>
					</div>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_content" class="mandatory">'.$w->labelContent.'</label>
					<textarea name="content" id="input_content" class="span12 TinyMCE" data-tinymce-mode="minimal" data-tinymce-height="250">'.htmlentities( $meeting->content ?? '', ENT_QUOTES, 'UTF-8' ).'</textarea>
				</div>
			</div>
			'.$warning.'
			<div class="row-fluid">
				<div class="buttonbar">
					<a href="./work/meeting" class="btn btn-small">'.$iconBack.$w->buttonBack.'</a>
					<a href="./work/meeting/edit/'.$meeting->meetingId.'" class="btn btn-small">'.$iconView.$w->buttonView.'</a>
					<button type="submit" class="btn btn-primary" name="save">'.$iconSave.$w->buttonSave.'</button>
				</div>
			</div>
		</form>
	</div>
</div>';
	}

	/**
	 *	@param		Entity_Work_Meeting	$meeting
	 *	@param		array				$roles
	 *	@param		array				$groups
	 *	@param		array				$users
	 *	@return		string
	 */
	public function renderEditParticipantsPanel( Entity_Work_Meeting $meeting, array $roles = [], array $groups = [], array $users = [] ): string
	{
		$iconAdd	= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-plus"] ).'&nbsp;';

		$modal		= '';
		$buttonAdd	= '';
		if( Model_Work_Meeting::STATUS_NEW === $meeting->status ){
			$modal		= new View_Modal_Work_Meeting_Participants( $this->env );
			$modal->setMeeting( $meeting )
				->setRoles( $roles )
				->setGroups( $groups )
				->setUsers( $users )
//				->setClass( 'modal-wide')
				->setAttributes( ['class' => 'modal-wide'] )
				->render();
			$buttonAdd	= $modal->trigger
				->setLabel( $iconAdd.'&nbsp;hinzufügen' )
				->setClass( 'btn btn-primary' )
				->render();
		}
		return '
		<div class="content-panel">
			<h4>Teilnehmer</h4>
			<div class="content-panel-inner">
				<div class="boxed not-boxed-large user-avatar-list">
					'.$this->renderParticipantsList( $meeting, $meeting->participants, Model_Work_Meeting::STATUS_NEW === $meeting->status ).'
				</div>
				<div class="buttonbar">
					'.$buttonAdd.'
				</div>
			</div>
		</div>
	'.$modal;
	}

	/**
	 *	@param		Entity_Work_Meeting	$meeting
	 *	@return		string
	 *	@throws		DateInvalidOperationException
	 */
	public function renderEditStatusPanel( Entity_Work_Meeting $meeting ): string
	{
		$w	= $this->getWords( 'panel-status', NULL, TRUE );
		$dateStart	= DateTime::createFromFormat( 'Y-m-d H:i:s', $meeting->dateStart );
		$dateEnd	= DateTime::createFromFormat( 'Y-m-d H:i:s', $meeting->dateEnd );

		$text	= '';
		$button	= '';
		if( Model_Work_Meeting::STATUS_NEW === $meeting->status ){
			$iconPlay			= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-play'] );
			$dateValidBefore	= $dateStart->sub( new DateInterval( 'PT1H' ) );
			$isValid			= new DateTime() < $dateValidBefore;
			if( $isValid ){
				$text	= HtmlTag::create( 'div', $w->msgActivate, [
					'class'	=> 'alert alert-info'
				] );
				$button	= HtmlTag::create( 'a', $iconPlay.'&nbsp;'.$w->btnActivate, [
					'href'	=> './work/meeting/setStatus/'.$meeting->meetingId.'/'.Model_Work_Meeting::STATUS_ACTIVE,
					'class'	=> 'btn btn-large btn-success',
				] );
			} else {
				$text	= HtmlTag::create( 'div', $w->msgInvalid, [
					'class'	=> 'alert alert-warning'
				] );
				$button	= HtmlTag::create( 'button', $iconPlay.'&nbsp;'.$w->btnActivate, [
					'type'		=> 'button',
					'class'		=> 'btn btn-large btn-success btn-disabled',
					'disabled'	=> 'disabled',
				] );
			}
		}
		else if( Model_Work_Meeting::STATUS_ACTIVE === $meeting->status ){
			$iconStop	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-stop'] );
			if( time() > $dateEnd->getTimestamp()  )
				return '';
			$text	= HtmlTag::create( 'div', $w->msgCancel, [
				'class'	=> 'alert alert-info'
			] );
			$button	= HtmlTag::create( 'a', $iconStop.'&nbsp;'.$w->btnCancel, [
				'href'	=> './work/meeting/setStatus/'.$meeting->meetingId.'/'.Model_Work_Meeting::STATUS_CANCELLED,
				'class'	=> 'btn btn-large',
			] );
		}
		else if( Model_Work_Meeting::STATUS_CANCELLED === $meeting->status ){
			$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-trash'] );
			$button		= HtmlTag::create( 'a', $iconRemove.'&nbsp;'.$w->btnRemove, [
				'href'	=> './work/meeting/remove/'.$meeting->meetingId,
				'class'	=> 'btn btn-large btn-primary',
			] );
			$iconReuse	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-refresh'] );
			$button		= HtmlTag::create( 'a', $iconReuse.'&nbsp;'.$w->btnRecycle, [
				'href'	=> './work/meeting/reuse/'.$meeting->meetingId,
				'class'	=> 'btn btn-large',
			] );
		}
		else if( Model_Work_Meeting::STATUS_OUTDATED === $meeting->status || Model_Work_Meeting::STATUS_DONE === $meeting->status ){
			$iconReuse	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-refresh'] );
			$button		= HtmlTag::create( 'a', $iconReuse.'&nbsp;'.$w->btnRecycle, [
				'href'	=> './work/meeting/reuse/'.$meeting->meetingId,
				'class'	=> 'btn btn-large',
			] );
		}

		return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$text.'
		'.$button.'
	</div>
</div>';
	}

	/**
	 *	@param		Entity_Work_Meeting	$meeting
	 *	@return		string
	 */
	function renderEditViewPanel( Entity_Work_Meeting $meeting ): string
	{
		$w			= $this->getWords( 'edit', NULL, TRUE );
		$iconBack	= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-arrow-left"] ).'&nbsp;';
		$iconEdit	= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-pencil"] ).'&nbsp;';

		$buttonEdit	= '';
		if( in_array( $meeting->status, [Model_Work_Meeting::STATUS_NEW, Model_Work_Meeting::STATUS_ACTIVE] ) )
			$buttonEdit	= HtmlTag::create( 'a', $iconEdit.$w->buttonEdit, [
				'href'	=> './work/meeting/edit/'.$meeting->meetingId.'/1',
				'class' => 'btn btn-primary',
			] );

		return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./work/meeting/edit/'.$meeting->meetingId.'" method="post">
			<div class="row-fluid">
				<div class="span8">
					<div class="row-fluid">
						<div class="span8">
							<div class="form-view-label">'.$w->labelTitle.'</div>
							<div class="form-view-value">'.htmlentities( $meeting->title ?? '', ENT_QUOTES, 'UTF-8' ).'</div>
						</div>
						<div class="span4">
							<div class="form-view-label">'.$w->labelLocation.'</div>
							<div class="form-view-value">'.htmlentities( $meeting->location ?? '', ENT_QUOTES, 'UTF-8' ).'</div>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<div class="form-view-label">'.$w->labelLink.'</div>
							<div class="form-view-value">'.htmlentities( $meeting->link ?? '', ENT_QUOTES, 'UTF-8' ).'</div>
						</div>
					</div>
				</div>
				<div class="span4">
					<div class="row-fluid">
						<div class="span6">
							<div class="form-view-label">'.$w->labelDateStart_date.'</div>
							<div class="form-view-value">'.date( 'Y-m-d', strtotime( $meeting->dateStart ) ).'</div>
						</div>
						<div class="span6">
							<div class="form-view-label">'.$w->labelDateEnd_date.'</div>
							<div class="form-view-value">'.date( 'Y-m-d', strtotime( $meeting->dateEnd ) ).'</div>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span6">
							<div class="form-view-label">'.$w->labelDateStart_time.'</div>
							<div class="form-view-value">'.date( 'H:i:s', strtotime( $meeting->dateStart ) ).'</div>
						</div>
						<div class="span6">
							<div class="form-view-label">'.$w->labelDateEnd_time.'</div>
							<div class="form-view-value">'.date( 'H:i:s', strtotime( $meeting->dateEnd ) ).'</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<div class="form-view-label">'.$w->labelContent.'</div>
					<div class="not-form-view-value">'.( $meeting->content ?? '–' ).'</div>
				</div>
			</div>
			<div class="row-fluid">
				<div class="buttonbar">
					<a href="./work/meeting" class="btn btn-small">'.$iconBack.$w->buttonBack.'</a>
					'.$buttonEdit.'
				</div>
			</div>
		</form>
	</div>
</div>';
	}

	/**
	 *	@param		Entity_Work_Meeting[]	$meetings
	 *	@param		int|string				$currentUserId
	 *	@return		string
	 */
	public function renderViewCards( array $meetings, int|string $currentUserId ): string
	{
		$list	= [];
		$modals	= [];
		$myRole	= 'nicht dabei';
		foreach( $meetings as $meeting ){
			$nrParticipants	= 0;
			foreach( $meeting->participants as $participant ){
				if( Model_Work_Meeting_Participant::TYPE_INFORMED !== $participant->type )
					$nrParticipants++;
				if( $currentUserId == $participant->userId ){
					$myRole	= $this->getWords( 'types', NULL, FALSE )[$participant->type] ?? '';
				}
			}
			/** @var int $timestampStart */
			$timestampStart	= strtotime( $meeting->dateStart );
			/** @var int $timestampEnd */
			$timestampEnd	= strtotime( $meeting->dateEnd );
			$weekdayStart	= $this->getWords( 'weekdays-short', NULL, FALSE )[date( 'w',  )].', ';
			$weekdayEnd		= $this->getWords( 'weekdays-short', NULL, FALSE )[date( 'w', $timestampEnd )].', ';
			$dateStart	= date( 'j.n.y', $timestampStart );
			$dateEnd	= date( 'j.n.y', $timestampEnd );
			$timeStart	= date( 'H:i', $timestampStart );
			$timeEnd	= date( 'H:i', $timestampEnd );

			$dateTimeRange	= $weekdayStart.$dateStart.' '.$timeStart.' – '.$weekdayEnd.$dateEnd.' '.$timeEnd;
			if( $dateStart === $dateEnd )
				$dateTimeRange	= $weekdayStart.$dateStart.' '.$timeStart.' – '.$timeEnd;

			$location	= $meeting->location;
			if( '' !== ( $meeting->link ?? '' ) )
				$location	= HtmlTag::create( 'a', $location, ['href' => $meeting->link] );

			$modals[]	= View_Modal_Work_Meeting_Display::create( $this->env )->setMeeting( $meeting )->useRoles( $this->useRoles );
			$trigger	= $this->renderViewModalTrigger( $meeting );

			$labelCount	= '<strong>'.$nrParticipants.'</strong> Teilnehmer';
			if( $this->useRoles )
				$labelCount	.= ', du bist <strong>'.$myRole.'</strong>';

			$list[]	= HtmlTag::create( 'li', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'div', [
						HtmlTag::create( 'div', $this->renderTimeLeft( $meeting ) ),
						HtmlTag::create( 'h4', $meeting->title ),
						HtmlTag::create( 'p', [
							HtmlTag::create( 'div', $labelCount ),
							HtmlTag::create( 'div', 'Wann: '.$dateTimeRange.' Uhr' ),
							HtmlTag::create( 'div', 'Wo: '.$location ),
						], ['class' => ''] ),
						HtmlTag::create( 'div', $trigger, ['class' => 'meeting-card-modal-trigger'] ),
					], ['class' => 'caption'] ),
				], ['class' => 'thumbnail'] ),
			], ['class' => 'meeting-card span4'] );
		}
		return HtmlTag::create( 'ul', $list, ['class' => 'meeting-cards thumbnails'] ).join( $modals );
	}

	/**
	 *	@param		Entity_Work_Meeting		$meeting
	 *	@return		string
	 */
	public function renderViewModalTrigger( Entity_Work_Meeting $meeting ): string
	{
		$iconEye	= HtmlTag::create( 'i', '', ['class' => 'fa fa-eye' ] );
		$trigger	= new View_Helper_Bootstrap_Modal_Trigger( $this->env );
		return $trigger->setModalId( 'meeting-'.$meeting->meetingId )
			->setId( 'trigger-meeting-'.$meeting->meetingId )
			->setClass( 'btn btn-block btn-primary' )
			->setLabel( $iconEye.'&nbsp;mehr anzeigen' )
			->render();
	}

	/**
	 *	@param		Entity_Work_Meeting				$meeting
	 *	@param		Entity_Work_Meeting_Participant	$participant
	 *	@param		bool							$editMode		Flag: add remove button, default: no
	 *	@return		string
	 */
	public function renderParticipant( Entity_Work_Meeting $meeting, Entity_Work_Meeting_Participant $participant, bool $editMode = FALSE ): string
	{
		$gravatar	= 'https://www.gravatar.com/avatar/'.md5( strtolower( trim( $participant->user->email ) ) ).'?s=32&d=mm&r=g';
		$gravatar	= HtmlTag::create( 'img', NULL, ['src' => $gravatar, 'class' => 'avatar'] );

		$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
		$remove		= '';
		if( $editMode )
			$remove		= HtmlTag::create( 'a', $iconRemove, [
				'href'	=> './work/meeting/removeParticipant/'.$meeting->meetingId.'/'.$participant->userId,
				'class'	=> 'pull-right',
				'title'	=> 'entfernen',
			] );

		if( $this->useRoles )
			$subtext	= HtmlTag::create( 'small', $this->getWords( 'types' )[$participant->type], ['class' => 'muted'] );
		else
			$subtext	= HtmlTag::create( 'small', $participant->user->firstname.' '.$participant->user->surname, ['class' => 'muted'] );

		$username	= HtmlTag::create( 'div', $participant->user->username.$remove, [
			'class'		=> 'username',
			'title'		=> $participant->user->firstname.' '.$participant->user->surname],
		);
		return $gravatar.$username.$subtext;
	}

	/**
	 *	@param		Entity_Work_Meeting								$meeting
	 *	@param		Entity_Work_Meeting_Participant[]				$participants
	 *	@param		bool											$editMode			Flag: add remove button, default: no
	 *	@return		string
	 */
	public function renderParticipantsList( Entity_Work_Meeting $meeting, array $participants, bool $editMode = FALSE ): string
	{
		$w		= (object) $this->getWords( 'panel-edit-results' );
		$list	= [];
		foreach( $participants as $participant )
			$list[]	= HtmlTag::create( 'li', $this->renderParticipant( $meeting, $participant, $editMode ) );
		if( [] === $list )
			$list[]	= HtmlTag::create( 'div', $w->labelRecipientsEmpty, ['class' => 'alert alert-info'] );
		return HtmlTag::create( 'ul', $list, ['class' => 'unstyled'] );
	}

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
//		$this->env->getPage()->js->addModuleFile( 'module.work.meetings.js' );
		$this->env->getPage()->addCommonStyle( 'module.work.meetings.css' );
		$this->useRoles	= $this->env->getModules()->get( 'Work_Meetings' )->getConfigAsDictionary()->get( 'useRoles' );
		$this->addData( 'useRoles', $this->useRoles );
	}

	/**
	 *	@param		Entity_Work_Meeting		$meeting
	 *	@return		string
	 */
	protected function renderTimeLeft( Entity_Work_Meeting $meeting ): string
	{
		/** @var int $timestampStart */
		$timestampStart	= strtotime( $meeting->dateStart );
		if( $timestampStart < time() )
			return '';
		$seconds	= $timestampStart - time();

		$class	= 'important';
		if( $seconds > 24 * 60 * 60 )
			$class	= 'success';
		else if( $seconds > 60 * 60 )
			$class	= 'warning';

		if( $this->env->getModules()->has( 'UI_Helper_TimePhraser' ) ){
			$helperTime	= new View_Helper_TimePhraser( $this->env );
			$timeLeft	= 'noch '.$helperTime->convert( $timestampStart - 2 * $seconds );
		}
		else if( $this->env->getModules()->has( 'UI_Helper_Datetime' ) ){
			$helperTime	= new View_Helper_Datetime( $this->env );
			$timeLeft	= 'noch '.$helperTime->getDurationPhraseFromSeconds( $seconds );
		}
		return HtmlTag::create( 'span', $timeLeft, ['class' => 'label label-'.$class] );
	}
}
