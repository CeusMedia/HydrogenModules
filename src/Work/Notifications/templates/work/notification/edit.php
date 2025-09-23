<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/** @var Entity_Notification_Message $message */
/** @var array<string,array<string,int|float|string>> $words */
/** @var array<object{username: string, email: string, seenAt: int}> $recipientsNew */
/** @var array<object{username: string, email: string, seenAt: int}> $recipientsSeen */

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['edit'];

$iconCancel		= Icon::create( 'arrow-left' );
$iconSave		= Icon::create( 'check' );
$iconAbort		= Icon::create( 'remove' );
$iconActivate	= Icon::create( 'play' );
$iconRemove		= Icon::create( 'trash' );

$isActive	= Model_Notification_Message::STATUS_ACTIVE === $message->status;
$isNew		= Model_Notification_Message::STATUS_NEW === $message->status;
$isSeen		= Model_Notification_Message::STATUS_SEEN === $message->status;
$isAborted	= Model_Notification_Message::STATUS_ABORTED === $message->status;
$isEditable	= $isNew || $isAborted;
$isEditable	= $isNew;

//print_m( $view->getData() );die;

function renderDisabledEditPanel( array $words, Entity_Notification_Message $message, array $buttons ): string
{
	$w			= (object) $words['edit'];
	$optStatus	= HtmlElements::Options( $words['statuses'], (string) $message->status );
	return HtmlTag::create( 'div', [
		HtmlTag::create( 'h3', $w->heading ),
		HtmlTag::create( 'div', [
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', $w->labelTitle ),
					HtmlTag::create( 'input', NULL, [
						'type'		=> 'text',
						'class'		=> 'span12',
						'value'		=> htmlentities( $message->title, ENT_QUOTES, 'UTF-8' ),
						'disabled'	=> 'disabled',
					] )
				], ['class' => 'span9'] ),
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', $w->labelStatus ),
					HtmlTag::create( 'select', $optStatus, [
						'class'		=> 'span12',
						'readonly'	=> 'readonly',
						'disabled'	=> 'disabled',
					] )
				], ['class' => 'span3'] )
			], ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', $w->labelDateStart ),
					HtmlTag::create( 'input', NULL, [
						'type'		=> 'date',
						'class'		=> 'span12',
						'value'		=> htmlentities( $message->dateStart, ENT_QUOTES, 'UTF-8' ),
						'disabled'	=> 'disabled',
					] )
				], ['class' => 'span3'] ),
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', $w->labelDateEnd ),
					HtmlTag::create( 'input', NULL, [
						'type'	=> 'date',
						'class'	=> 'span12',
						'value'	=> htmlentities( $message->dateEnd ?? '', ENT_QUOTES, 'UTF-8' ),
						'disabled'	=> 'disabled',
					] )
				], ['class' => 'span3'] ),
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', $w->labelLink ),
					HtmlTag::create( 'input', NULL, [
						'type'		=> 'text',
						'class'		=> 'span12',
						'value'		=> htmlentities( $message->link ?? '', ENT_QUOTES, 'UTF-8' ),
						'disabled'	=> 'disabled',
					] )
				], ['class' => 'span6'] ),
			], ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', $w->labelContent ),
					HtmlTag::create( 'div', $message->content, ['class' => 'boxed'] )
				], ['class' => 'span12'] )
			], ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', join( ' ', $buttons ), ['class' => 'buttonbar'] )
		], ['class' => 'content-panel-inner'] )
	], ['class' => 'content-panel'] );
}

function renderEditPanel( array $words, Entity_Notification_Message $message, array $buttons, array $recipientsNew = [] ): string
{
	$w			= (object) $words['edit'];
	$optStatus	= HtmlElements::Options( $words['statuses'], (string) $message->status );
	return HtmlTag::create( 'div', [
		HtmlTag::create( 'h3', $w->heading ),
		HtmlTag::create( 'div', [
			HtmlTag::create( 'form', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'div', [
						HtmlTag::create( 'div', [
							HtmlTag::create( 'div', [
								HtmlTag::create( 'label', $w->labelTitle, ['class' => 'mandatory'] ),
								HtmlTag::create( 'input', NULL, [
									'type'		=> 'text',
									'name'		=> 'title',
									'id'		=> 'input_title',
									'class'		=> 'span12',
									'required'	=> 'required',
									'value'		=> htmlentities( $message->title, ENT_QUOTES, 'UTF-8' ),
								] )
							], ['class' => 'span9'] ),
							HtmlTag::create( 'div', [
								HtmlTag::create( 'label', $w->labelStatus ),
								HtmlTag::create( 'select', $optStatus, [
									'name'		=> 'status',
									'id'		=> 'input_status',
									'class'		=> 'span12',
									'readonly'	=> 'readonly',
									'disabled'	=> 'disabled',
								] )
							], ['class' => 'span3'] )
						], ['class' => 'row-fluid'] ),
						HtmlTag::create( 'div', [
							HtmlTag::create( 'div', [
								HtmlTag::create( 'label', $w->labelDateStart, ['class' => 'mandatory'] ),
								HtmlTag::create( 'input', NULL, [
									'type'		=> 'date',
									'name'		=> 'dateStart',
									'id'		=> 'input_dateStart',
									'class'		=> 'span12',
									'required'	=> 'required',
									'value'		=> htmlentities( $message->dateStart, ENT_QUOTES, 'UTF-8' ),
								] )
							], ['class' => 'span3'] ),
							HtmlTag::create( 'div', [
								HtmlTag::create( 'label', $w->labelDateEnd ),
								HtmlTag::create( 'input', NULL, [
									'type'	=> 'date',
									'name'	=> 'dateEnd',
									'id'	=> 'input_dateEnd',
									'class'	=> 'span12',
									'value'	=> htmlentities( $message->dateEnd ?? '', ENT_QUOTES, 'UTF-8' ),
								] )
							], ['class' => 'span3'] ),
							HtmlTag::create( 'div', [
								HtmlTag::create( 'label', $w->labelLink ),
								HtmlTag::create( 'input', NULL, [
									'type'		=> 'text',
									'name'		=> 'link',
									'id'		=> 'input_link',
									'class'		=> 'span12',
									'value'		=> htmlentities( $message->link ?? '', ENT_QUOTES, 'UTF-8' ),
								] )
							], ['class' => 'span6'] ),
						], ['class' => 'row-fluid'] ),
						HtmlTag::create( 'div', [
							HtmlTag::create( 'div', [
								HtmlTag::create( 'label', $w->labelContent ),
								HtmlTag::create( 'textarea', htmlentities( $message->content, ENT_QUOTES, 'UTF-8' ), [
									'type'	=> 'text',
									'name'	=> 'content',
									'id'	=> 'input_content',
									'class'	=> 'span12 TinyMCE',
									'rows'	=> 12,
								] )
							], ['class' => 'span12'] )
						], ['class' => 'row-fluid'] ),
					], ['class' => 'span9'] ),
					HtmlTag::create( 'div', [
						HtmlTag::create( 'label', 'Empfänger' ),
						HtmlTag::create( 'div', [
							renderRecipientList( $words, $recipientsNew ),
						], ['class' => 'boxed boxed-large user-avatar-list'] ),
					], ['class' => 'span3'] ),
				], ['class' => 'row-fluid'] ),
				HtmlTag::create( 'div', join( ' ', $buttons ), ['class' => 'buttonbar'] )
			], [
				'action'	=> 'work/notification/edit/'.$message->notificationMessageId,
				'method'	=> 'post'
			] )
		], ['class' => 'content-panel-inner'] )
	], ['class' => 'content-panel content-panel-form'] );

}

/**
 *	@param		object{username: string, email: string, seenAt: int}	$recipient
 *	@return		string
 */
function renderRecipient( object $recipient ): string
{
	$gravatar	= 'https://www.gravatar.com/avatar/'.md5( strtolower( trim( $recipient->email ) ) ).'?s=32&d=mm&r=g';
	$gravatar	= HtmlTag::create( 'img', NULL, ['src' => $gravatar, 'class' => 'avatar'] );
	$nrPosts	= '';#HtmlTag::create( 'small', ' ()', ['class' => 'muted'] );
	$datetime	= HtmlTag::create( 'small', date( "d.m.Y H:i", $recipient->seenAt ), ['class' => 'muted'] );
	$username	= HtmlTag::create( 'div', $recipient->username.$nrPosts, ['class' => 'username'] );
	return $gravatar.$username.$datetime;
}

/**
 *	@param		array<string,array<string,int|float|string>>					$words
 *	@param		array<object{username: string, email: string, seenAt: int}>		$recipients
 *	@return		string
 */
function renderRecipientList( array $words, array $recipients ): string
{
	$w		= (object) $words['panel-edit-results'];
	$list	= [];
	foreach( $recipients as $recipient )
		$list[$recipient->username]	= HtmlTag::create( 'li', renderRecipient( $recipient ) );
	uksort( $list, 'strnatcasecmp' );
	if( [] === $list )
		$list[]	= HtmlTag::create( 'div', $w->labelRecipientsEmpty, ['class' => 'alert alert-info'] );
	return HtmlTag::create( 'ul', $list, ['class' => 'unstyled'] );
}

/**
 *	@param		array<string,array<string,int|float|string>>				$words
 *	@param		array<object{username: string, email: string, seenAt: int}>	$recipientsNew
 *	@param		array<object{username: string, email: string, seenAt: int}>	$recipientsSeen
 *	@return		string
 */
function renderResultsPanel( array $words, array $recipientsNew, array $recipientsSeen ): string
{
	$w		= (object) $words['panel-edit-results'];
	$ratio	= round( count( $recipientsSeen ) / count( $recipientsNew + $recipientsSeen ) ) * 100;
	return HtmlTag::create( 'div', [
		HtmlTag::create( 'h3', $w->heading ),
		HtmlTag::create( 'div', [
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'h4', $w->headingFacts ),
					HtmlTag::create( 'div', sprintf( $w->templateRecipientsNew, count( $recipientsNew ) ) ),
					HtmlTag::create( 'div', sprintf( $w->templateRecipientsSeen, count( $recipientsSeen ) ) ),
					HtmlTag::create( 'div', sprintf( $w->templateRecipientsRatio, $ratio ) ),
				], ['class' => 'span4'] ),
				HtmlTag::create( 'div', [
					HtmlTag::create( 'h4', $w->labelRecipientsNew ),
					HtmlTag::create( 'div', [
						renderRecipientList( $words, $recipientsNew ),
					], ['class' => 'boxed user-avatar-list'] ),
				], ['class' => 'span4'] ),
				HtmlTag::create( 'div', [
					HtmlTag::create( 'h4', $w->labelRecipientsSeen ),
					HtmlTag::create( 'div', [
						renderRecipientList( $words, $recipientsSeen ),
					], ['class' => 'boxed user-avatar-list'] ),
				], ['class' => 'span4'] ),
			], ['class' => 'row-fluid'] )
		], ['class' => 'content-panel-inner'] )
	], ['class' => 'content-panel content-panel-form'] );
}

$panelResults	= '';
if( $isSeen || $isActive || $isAborted )
	$panelResults	= renderResultsPanel( $words, $recipientsNew, $recipientsSeen );

$buttonSave		= '';
$buttonAbort	= '';
$buttonActivate	= '';
$buttonRemove	= '';

if( $isNew ){
	$buttonActivate	= HtmlTag::create( 'a', $iconActivate.'&nbsp;'.$w->buttonActivate, [
		'href'	=> './work/notification/setStatus/'.$message->notificationMessageId.'/'.Model_Notification_Message::STATUS_ACTIVE,
		'class'	=> 'btn btn-warning not-btn-small',
	] );
}
if( $isActive ){
	$buttonAbort	= HtmlTag::create( 'a', $iconAbort.'&nbsp;'.$w->buttonAbort, [
		'href'	=> './work/notification/setStatus/'.$message->notificationMessageId.'/'.Model_Notification_Message::STATUS_ABORTED,
		'class'	=> 'btn btn-danger btn-small',
	] );
}
if( $isSeen || $isAborted || $isNew ){
	$inputContent	= HtmlTag::create( 'div', $message->content, ['class' => 'boxed'] );
	$buttonRemove	= HtmlTag::create( 'a', $iconRemove.'&nbsp;'.$w->buttonRemove, [
		'href'	=> './work/notification/remove/'.$message->notificationMessageId,
		'class'	=> 'btn btn-inverse btn-small',
	] );
}
if( $isNew ){
	$buttonSave	= HtmlTag::create( 'button', $iconSave.'&nbsp;'.$w->buttonSave, [
		'type'	=> 'submit',
		'name'	=> 'save',
		'class'	=> 'btn btn-success',
	] );
}

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.'&nbsp;'.$w->buttonCancel, [
	'href'	=> './work/notification',
	'class'	=> 'btn btn-small',
] );
$buttons	= [];
foreach( [$buttonCancel, $buttonSave, $buttonActivate, $buttonAbort, $buttonRemove] as $button )
	if( '' !== $button )
		$buttons[]	= $button;

if( $isNew )
	$panelEdit	= renderEditPanel( $words, $message, $buttons, $recipientsNew );
else
	$panelEdit	= renderDisabledEditPanel( $words, $message, $buttons );

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
</style>
';

return $panelEdit.$panelResults.$style;
