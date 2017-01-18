<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-trash icon-white' ) );
$iconAccept		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok icon-white' ) );
$iconReject		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );
$iconRequest	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );

$isCurrentUser	= $user->userId == $currentUserId;
$isRelated		= $relation && $relation->status == 2;

$w	= (object) $words['view'];

$helperAvatar	= NULL;
if( $env->getModules()->has( 'Manage_My_User_Avatar' ) ){									//  use user avatar helper module
	$helperAvatar			= new View_Helper_UserAvatar( $env );							//  create helper
	$moduleConfig	= $config->getAll( 'module.manage_my_user_avatar.', TRUE );				//  get module config
	$helperAvatar->useGravatar( $moduleConfig->get( 'use.gravatar' ) );						//  use gravatar as fallback
	$helperAvatar->setUser( $user );														//  set user data
	$helperAvatar->setSize( 256 );															//  set image size
//	$avatar	= $helperAvatar->render();														//  render avatar
}
else if( $this->env->getModules()->has( 'UI_Helper_Gravatar' ) ){							//  use gravatar helper module
	$helperAvatar		= new View_Helper_Gravatar( $env );									//  create helper
	$helperAvatar->setUser( $user );														//  set user data
	$helperAvatar->setSize( 256 );															//  set image size
//	$avatar	= $helperAvatar->render();														//  render avatar
}

$image	= '';
if( $helperAvatar ){
	$image	= UI_HTML_Tag::create( 'img', NULL, array(
		'src'	=> $helperAvatar->getImageUrl(),
	//	'class'	=> 'img-polaroid',
	) );
}

$helperAvatar	= new View_Helper_UserAvatar( $env );
$helperAvatar->setUser( $user );
$helperAvatar->setSize( 256 );


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


$helperTime		= new View_Helper_TimePhraser( $env );

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
$facts[$w->labelUsername]	= array( '<big><strong>'.$user->username.'</strong></big>' );
if( $isRelated || $isCurrentUser ){
	$facts[$w->labelName]	= array( $user->firstname.' '.$user->surname );
	$facts[$w->labelRole]	= array( $role->title );
	$facts[$w->labelEmail]	= array( '<a href="mailto:'.$user->email.'">'.$user->email.'</a>' );
	if( $user->phone )
		$facts[$w->labelPhone]	= array( $user->phone );
	if( $user->fax )
		$facts[$w->labelFax]	= array( $user->fax );
}

$facts[$w->labelRegisteredAt]	= array( $helperTime->convert( $user->createdAt, TRUE, $w->labelRegisteredAt_prefix, $w->labelRegisteredAt_suffix ) );
if( $user->loggedAt )
	$facts[$w->labelLoggedAt]	= array( $helperTime->convert( $user->loggedAt, TRUE, $w->labelLoggedAt_prefix, $w->labelLoggedAt_suffix ) );
if( $user->activeAt )
	$facts[$w->labelActiveAt]	= array( $helperTime->convert( $user->activeAt, TRUE, $w->labelActiveAt_prefix, $w->labelActiveAt_suffix ) );
$facts[$w->labelStatus]			= array( $words['user-states'][$user->status] );
if( !$relation )
	$relation	= (object) array( 'status' => 0 );
$facts[$w->labelRelation]	= array( $words['relation-states'][$relation->status] );

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
