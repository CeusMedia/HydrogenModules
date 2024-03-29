<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$modelUser	= new Model_User( $env );
$userId		= $session->get( 'auth_user_id' );
$user		= $modelUser->get( $userId );

$buttonInvite	= HtmlElements::LinkButton( './manage/my/user/invite/invite', 'jemanden einladen', 'button icon add' );

$listInvites	= '<em><small>Noch keine Einladungen verschickt.</em></small><br/>';
if( $invites->all ){
	$listInvites	= [];
	foreach( $invites->all as $invite ){
		$email		= HtmlTag::create( 'span', $invite->email );
		$date		= date( 'd.m.Y H:i', $invite->createdAt );
		$status		= HtmlTag::create( 'span', $words['states'][$invite->status] );
		$buttons	= [];
		if( $invite->type == 1 ){
			if( $invite->status == 1 ){
				$expire	= date( 'd.m.Y', $invite->createdAt + $daysValid * 24 * 60 * 60 );
				$expire	= sprintf( 'verfällt am %s', $expire );
				$image	= HtmlTag::create( 'img', NULL, ['src' => 'https://cdn.ceusmedia.de/img/famfamfam/silk/error.png'] );
				$date	.= '&nbsp;'.HtmlTag::create( 'span', $image, ['title' => $expire] );
				$buttons[]	= HtmlElements::LinkButton( './manage/my/user/invite/cancel/'.$invite->userInviteId, '', 'button tiny remove', NULL, NULL, 'abbrechen' );
			}
		}
		$cells		= [];
		$cells[]	= HtmlTag::create( 'td', $date, ['class' => 'invite-date'] );
		$cells[]	= HtmlTag::create( 'td', $status, ['class' => 'invite-status'] );
		$cells[]	= HtmlTag::create( 'td', $email, ['class' => 'invite-email'] );
		$cells[]	= HtmlTag::create( 'td', join( $buttons ), ['class' => 'invite-actions'] );
		$listInvites[]	= HtmlTag::create( 'tr', join( $cells ), ['class' => 'invite-status status'.$invite->status] );
	}
	$listInvites	= join( $listInvites );
	$colgroup		= HtmlElements::ColumnGroup( '140px', '120px', '', '50px' );
	$tableHeads		= HtmlElements::TableHeads( ['Datum', 'Status', 'E-Mail-Adresse', ''] );
	$listInvites	= '
<style>
.invite-status.status-2{
	background-color: #FF9F9F;
	opacity: 0.75;
	}
.invite-status.status-1{
	background-color: #DFDFDF;
	opacity: 0.5;
	}
.invite-status.status0{
	background-color: #FFFFFF;
	}
.invite-status.status1{
	background-color: #FFFF7F;
	}
.invite-status.status2{
	background-color: #9FFF9F;
	}
td.invite-actions{
	text-align: right;
	}
</style>
<fieldset>
	<legend class="icon info">Einladungen</legend>
	<table>
		'.$colgroup.'
		<thead>
			'.$tableHeads.'
		</thead>
		<tbody>
			'.$listInvites.'
		</tbody>
	</table>
</fieldset>';
}

$text	= '
###Plan###
**3 Modi:**
olist>
- promote - Mail schicken
- invite - Invite-Code schicken
- bonus - Coupon-Code schicken
<olist
###Fehlt noch###
ulist>
- Relationen <cite>Auth</cite>, <cite>UI:Helper:Content</cite>
- Integration <cite>Invite-Code</cite> => <cite>Auth</cite>
- Umsetzung der Modulkonfiguration
- Project-Join @ <cite>Auth::register</cite>
- Controller ::cancel
- Helper für <cite>Manage:My:User</cite>
- Template für <cite>promote</cite> Modus
- Locale <cite>en/invite</cite>
- CSS >>(aus Template <cite>index</cite>)<< auslagern
<ulist
###Nice to have###
list>
- Modus <cite>bonus</cite>
- Einlade-Limit pro Monat >>(hardlimit)<<
- Limit pro Benutzer pro Monat  >>(softlimit)<<
<list
';

return '
<div class="column-left-70">
	'.$listInvites.'
	'.$buttonInvite.'
</div>
<div class="column-right-30">
	<fieldset>
		<legend class="icon info">Informationen</legend>
		'.View_Helper_ContentConverter::render( $env, $text ).'
	</fieldset>
</div>
<div class="column-clear"></div>
';


/*
$listInvitesOpen	= '<em><small>Keine.</em></small>';
if( $invites->open ){
	$listInvitesOpen	= [];
	foreach( $invites->open as $invite ){
		$url	= './manage/my/user/invite/invite/?code='.$invite->userInviteId;
		$link	= HtmlTag::create( 'a', $invite->email, ['href' => $url] );
		$listInvitesOpen[]	= HtmlTag::create( 'li', $link );
	}
	$listInvitesOpen	= HtmlTag::create( 'ul', join( $listInvitesOpen ) );
}

$listInvitesDone	= '<em><small>Keine.</em></small>';
if( $invites->done ){
	$listInvitesDone	= [];
	foreach( $invites->done as $invite ){
		$url	= './manage/my/user/invite/invite/?code='.$invite->userInviteId;
		$link	= HtmlTag::create( 'a', $invite->email, ['href' => $url] );
		$listInvitesDone[]	= HtmlTag::create( 'li', $link );
	}
	$listInvitesDone	= HtmlTag::create( 'ul', join( $listInvitesDone ) );
}

return '
<dl>
	<dt>User ID</dt>
	<dd>'.$userId.'</dd>
	<dt>User Name</dt>
	<dd>'.$user->username.'</dd>
	<dt>Invite Open</dt>
	<dd>'.$listInvitesOpen.'</dd>
	<dt>Invite Done</dt>
	<dd>'.$listInvitesDone.'</dd>
</dl>'.$buttonInvite;
 */

