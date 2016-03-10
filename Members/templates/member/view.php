<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-trash icon-white' ) );
$iconAccept		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok icon-white' ) );
$iconReject		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );
$iconRequest	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );

$isCurrentUser	= $user->userId == $currentUserId;
$isRelated		= $relation && $relation->status == 2;

$w	= (object) $words['view'];

$helperAvatar	= new View_Helper_UserAvatar( $env );
$helperAvatar->setUser( $user );
$helperAvatar->setSize( 256 );

$image	= UI_HTML_Tag::create( 'img', NULL, array(
	'src'	=> $helperAvatar->getImageUrl(),
//	'class'	=> 'img-polaroid',
) );

$modelRole	= new Model_Role( $env );
$role		= $modelRole->get( $user->roleId );

$data	= print_m( $user, NULL, NULL, TRUE );

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.'&nbsp;'.$w->buttonCancel, array(
	'href'		=> $from ? $from : './member/search',
	'class'		=> 'btn btn-small',
) );

$buttonRequest	= '';
$buttonRevoke	= '';
$buttonAccept	= '';
$buttonReject	= '';

if( $user->userId !== $currentUserId ){
	if( $relation ){
		if( $relation->status == 1 && $relation->direction == "in" ){
			$buttonAccept	= UI_HTML_Tag::create( 'a', $iconAccept.'&nbsp;'.$w->buttonAccept, array(
				'href'		=> './member/accept/'.$relation->userRelationId.'?from='.$from,
				'class'		=> 'btn btn btn-success',
			) );
			$buttonReject	= UI_HTML_Tag::create( 'a', $iconReject.'&nbsp;'.$w->buttonReject, array(
				'href'		=> './member/reject/'.$relation->userRelationId.'?from='.$from,
				'class'		=> 'btn btn btn-danger',
			) );
		}
		if( $relation->status == 2 ){
			$buttonRevoke	= UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp;'.$w->buttonRevoke, array(
				'href'		=> './member/release/'.$relation->userRelationId.'?from='.$from,
				'class'		=> 'btn btn-small btn-inverse',
				'onclick'	=> "if(!confirm('Wirklich?')) return false;",
			) );
		}
	}
	else{
		$buttonRequest	= UI_HTML_Tag::create( 'a', $iconRequest.'&nbsp;'.$w->buttonRequest, array(
			'href'		=> './member/request/'.$user->userId.'?from='.$from,
			'class'		=> 'btn btn-small btn-primary',
		) );
	}
}

function renderFacts( $facts, $class = 'dl-horizontal' ){
	$list	= array();
	foreach( $facts as $term => $values )
		$list[]	= '<dt>'.$term.'</dt><dd>'.join( '</dd><dd>', $values ).'</dd>';
	return '<dl class="'.$class.'">'.join( $list ).'</dl>';
}

$facts	= array();
$facts['Bentzername']	= array( '<big><strong>'.$user->username.'</strong></big>' );
if( $isRelated || $isCurrentUser ){
	$facts['Vor- und Nachname']	= array( $user->firstname.' '.$user->surname );
	$facts['Rolle']	= array( $role->title );
	$facts['E-Mail-Adresse']	= array( '<a href="mailto:'.$user->email.'">'.$user->email.'</a>' );
	if( $user->phone )
		$facts['Telefon']	= array( $user->phone );
	if( $user->fax )
		$facts['Telefax']	= array( $user->fax );
}

$facts['registriert am']	= array( date( 'd.m.Y', $user->createdAt ).'&nbsp;<small class="muted">'.date( 'H:i:s', $user->createdAt ).'</small>' );
if( $user->loggedAt )
	$facts['zuletzt eingeloggt am']	= array( date( 'd.m.Y', $user->loggedAt ).'&nbsp;<small class="muted">'.date( 'H:i:s', $user->loggedAt ).'</small>' );
if( $user->activeAt )
	$facts['zuletzt aktiv am']	= array( date( 'd.m.Y', $user->activeAt ).'&nbsp;<small class="muted">'.date( 'H:i:s', $user->activeAt ).'</small>' );
$facts['Status']		= array( $words['user-states'][$user->status] );
if( !$relation )
	$relation	= (object) array( 'status' => 0 );
$facts['Verbindung']	= array( $words['relation-states'][$relation->status] );

$helperMember	= new View_Helper_Member( $env );
$helperMember->setMode( 'thumbnail' );
$helperMember->setUser( $user );
//$image	= $helperMember->render();

$panelInfo	= '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span4">
				<div class="thumbnail">'.$image.'</div><br/>
			</div>
			<div class="span8">
				'.renderFacts( $facts ).'
			</div>
		</div>
		<div class="buttonbar">
			'.$buttonCancel.'
			'.$buttonRequest.'
			'.$buttonAccept.'
			'.$buttonReject.'
			'.$buttonRevoke.'
		</div>
	</div>
</div>';

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/member/' ) );

$tabs	= View_Member::renderTabs( $env, '' );

return $tabs.$textTop.'
<div class="row-fluid">
	<div class="span8">
		'.$panelInfo.'
	</div>
</div>'.$textBottom;
