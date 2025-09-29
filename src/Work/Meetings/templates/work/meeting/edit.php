<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var array $words */
/** @var object $meeting */
/** @var bool $editMode */
/** @var Entity_Role[] $roles */
/** @var Entity_Group[] $groups */

$w			= (object) $words['edit'];
$iconBack	= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-arrow-left"] ).'&nbsp;';
$iconSave	= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-check"] ).'&nbsp;';

function renderPanelEdit( Entity_Work_Meeting $meeting, array $words ): string
{
	$w			= (object) $words['edit'];
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
function renderPanelView( Entity_Work_Meeting $meeting, array $words ): string
{
	$w			= (object) $words['edit'];
	$iconBack		= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-arrow-left"] ).'&nbsp;';
	$iconEdit		= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-pencil"] ).'&nbsp;';
	$iconSave		= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-check"] ).'&nbsp;';
	$iconActivate	= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-play"] ).'&nbsp;';

	$buttonCancel	= '';
	$buttonActivate	= '';
	if( Model_Work_Meeting::STATUS_ACTIVE === $meeting->status )
		$buttonCancel	= '';
	else if( Model_Work_Meeting::STATUS_NEW === $meeting->status )
		$buttonActivate	= '<a href="./work/meeting/setStatus/'.$meeting->meetingId.'/'.Model_Work_Meeting::STATUS_ACTIVE.'" class="btn">'.$iconActivate.$w->buttonActivate.'</a>
';

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
					<a href="./work/meeting/edit/'.$meeting->meetingId.'/1" class="btn btn-primary">'.$iconEdit.$w->buttonEdit.'</a>
				</div>
			</div>
		</form>
	</div>
</div>';
}

/**
 *	@param		array		$words
 *	@param		object{username: string, email: string, seenAt: int}	$recipient
 *	@return		string
 */
function renderParticipant( array $words, Entity_Work_Meeting_Participant $participant ): string
{
	$gravatar	= 'https://www.gravatar.com/avatar/'.md5( strtolower( trim( $participant->user->email ) ) ).'?s=32&d=mm&r=g';
	$gravatar	= HtmlTag::create( 'img', NULL, ['src' => $gravatar, 'class' => 'avatar'] );

	$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
	$remove		= HtmlTag::create( 'a', $iconRemove, [
		'href'	=> './work/meeting/1',
		'class'	=> 'pull-right',
		'title'	=> 'entfernen',
	] );

	$type		= HtmlTag::create( 'small', $words['types'][$participant->type], ['class' => 'muted'] );
	$username	= HtmlTag::create( 'div', $participant->user->username.$remove, [
		'class'		=> 'username',
		'title'		=> $participant->user->firstname.' '.$participant->user->surname],
	);
	return $gravatar.$username.$type;
}

/**
 *	@param		array<string,array<string,int|float|string>>	$words
 *	@param		Entity_Work_Meeting_Participant[]				$participants
 *	@return		string
 */
function renderParticipantsList( array $words, array $participants ): string
{
	$w		= (object) $words['panel-edit-results'];
	$list	= [];
	foreach( $participants as $participant )
		$list[]	= HtmlTag::create( 'li', renderParticipant( $words, $participant ) );
	if( [] === $list )
		$list[]	= HtmlTag::create( 'div', $w->labelRecipientsEmpty, ['class' => 'alert alert-info'] );
	return HtmlTag::create( 'ul', $list, ['class' => 'unstyled'] );
}

function renderPanelParticipantsModal( Environment $env, array $words, Entity_Work_Meeting $meeting, array $roles = [], array $groups = [] ): string
{
	$list	= [];
	foreach( $roles as $role ){
		if( 0 === $role->nrUsers )
			continue;
		$input	= HtmlTag::create( 'input', NULL, [
			'type'	=> 'checkbox',
			'name'	=> 'roleIds[]',
			'value'	=> $role->roleId,
		] );
		$nr			= HtmlTag::create( 'small', '('.$role->nrUsers.')', ['class' => 'muted'] );
		$label		= HtmlTag::create( 'label', $input.'&nbsp;'.$role->title.'&nbsp;'.$nr, ['class' => 'checkbox'] );
		$list[$role->title]		= HtmlTag::create( 'li', $label );
	};
	uksort( $list, 'strnatcasecmp' );
	$listRoles	= HtmlTag::create( 'ul', $list, ['class' => 'unstyled'] );

	$list	= [];
	foreach( $groups as $group ){
		if( 0 === $group->nrUsers )
			continue;
		$input	= HtmlTag::create( 'input', NULL, [
			'type'	=> 'checkbox',
			'name'	=> 'groupIds[]',
			'value'	=> $group->groupId,
		] );
		$nr			= HtmlTag::create( 'small', '('.$group->nrUsers.')', ['class' => 'muted'] );
		$label		= HtmlTag::create( 'label', $input.'&nbsp;'.$group->title.'&nbsp;'.$nr, ['class' => 'checkbox'] );
		$list[$group->title]		= HtmlTag::create( 'li', $label );
	};
	uksort( $list, 'strnatcasecmp' );
	$listGroups	= HtmlTag::create( 'ul', $list, ['class' => 'unstyled'] );

	$optionByGroups	= '';
	if( [] !== $groups )
		$optionByGroups	= '
			<div class="span4">
				<label class="radio">
					<input type="radio" name="source" value="groups" class="has-optionals"/>
					Gruppen
				</label>
			</div>';

	$form	= '
		<div class="row-fluid">
			<div class="span4">
				<label class="radio">
					<input type="radio" name="source" value="roles" class="has-optionals" checked/>
					Rollen
				</label>
			</div>
			'.$optionByGroups.'
			<div class="span4">
				<label class="radio">
					<input type="radio" name="source" value="user" class="has-optionals"/>
					Benutzer
				</label>
			</div>
		</div>
		<div class="row-fluid optional source source-roles">
			<div class="">
				<h4>Benutzer der Rollen</h4>
				'.$listRoles.'
			</div>
		</div>
		<div class="row-fluid optional source source-groups">
			<div class="">
				<h4>Benutzer der Gruppen</h4>
				'.$listGroups.'
			</div>
		</div>
		<div class="row-fluid optional source source-user">
			<div class="">
				<h4>Benutzer</h4>
			</div>
		</div>
		';

	$iconBack	= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-arrow-left"] ).'&nbsp;';
	$iconSave	= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-check"] ).'&nbsp;';

	return View_Helper_Bootstrap_Modal::create( $env )
		->setId( 'meeting-participants-add' )
		->setHeading( 'Teilnehmer hinzufügen' )
		->setFormAction( 'work/meeting/addParticipants/'.$meeting->meetingId )
		->setButtonLabelCancel( $iconBack.'abbrechen' )
		->setButtonLabelSubmit( $iconSave.'hinzufügen')
		->setBody( $form )
		->render();
}

function renderPanelParticipants( Environment $env, array $words, Entity_Work_Meeting $meeting, array $roles = [], array $groups = [] ): string
{
	$iconAdd	= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-plus"] ).'&nbsp;';

	$modal		= '';
	$buttonAdd	= '';
	if( Model_Work_Meeting::STATUS_NEW === $meeting->status ){
		$modal		= renderPanelParticipantsModal( $env, $words, $meeting, $roles );
		$buttonAdd	= View_Helper_Bootstrap_Modal_Trigger::create( $env )
			->setModalId( 'meeting-participants-add' )
			->setLabel( $iconAdd.'hinzufügen' )
			->setClass( 'btn btn-primary' )
			->render();
	}
	return '
		<div class="content-panel">
			<h4>Teilnehmer</h4>
			<div class="content-panel-inner">
				<div class="boxed not-boxed-large user-avatar-list">
					'.renderParticipantsList( $words, $meeting->participants ).'
				</div>
				<div class="buttonbar">
					'.$buttonAdd.'
				</div>
			</div>
		</div>
	'.$modal;
}


$style	= '
<style>
div.user-avatar-list div.username {
	line-height: 1.2em;
	font-size: 1.1em;
	}
div.user-avatar-list img.avatar {
	float: left;
	width: 32px;
	height: 32px;
	margin-right: 8px;
	border: 1px solid gray;
	box-shadow: 1px 1px 2px rgba(0,0,0,0.2);
	}
div.boxed {
	min-height: 150px;
	max-height: 300px;
	overflow-y: auto;
	border: 1px solid rgba(127, 127, 127, 0.5);
	border-radius: 4px;
	padding: 0.5em 1em;
	margin-bottom: 1.5em;	
	}
div.boxed-large {
	min-height: 470px;
	max-height: 470px;
	height: 470px;
	padding: 0.5em;
	}
div.modified {
	margin-top: 0.5em;
	padding-top: 0.5em;
	padding-bottom: 0.25em;
	border-top: 1px solid #DDD;
	}
div.form-view-label {
	font-size: 0.9em;
	color: rgba( 92, 92, 92, 0.5);
	}
div.form-view-value {
	font-size: 1.2em;
	padding-bottom: 0.4em;
	}
</style>
';

$panelEdit	= $editMode ? renderPanelEdit( $meeting, $words ) : renderPanelView( $meeting, $words );
$panelParticipants	= renderPanelParticipants( $env, $words, $meeting, $roles, $groups );

extract( $view->populateTexts( ['above', 'bottom', 'top'], 'html/work/meeting/edit/', ['words' => $words] ) );

return $textTop.'
<script>$(document).ready(function(){});</script>
<div class="newsletter-content">
	'.$textAbove.'
	<div class="row-fluid">
		<div class="span9">
			'.$panelEdit.'
		</div>
		<div class="span3">
			'.$panelParticipants.'
		</div>
	</div>
</div>
'.$textBottom.$style;

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
	</div>
</div>';

