<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var Entity_User $user */
/** @var int|string $currentUserId */
/** @var ?string $from */
/** @var Dictionary $config */
/** @var array<string,array<string,string>> $words */


$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'icon-arrow-left'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'icon-trash icon-white'] );
$iconAccept		= HtmlTag::create( 'i', '', ['class' => 'icon-ok icon-white'] );
$iconReject		= HtmlTag::create( 'i', '', ['class' => 'icon-remove icon-white'] );
$iconRequest	= HtmlTag::create( 'i', '', ['class' => 'icon-plus icon-white'] );

$isCurrentUser	= $user->userId == $currentUserId;
$isRelated		= $relation && $relation->status == 2;

$w	= (object) $words['view'];

$helperAvatar	= NULL;
if( $env->getModules()->has( 'Manage_My_User_Avatar' ) ){									//  use user avatar helper module
	$helperAvatar			= new View_Helper_UserAvatar( $env );							//  create helper
	$moduleConfig	= $config->getAll( 'module.manage_my_user_avatar.', TRUE );				//  get module config
	$helperAvatar->useGravatar( (bool) $moduleConfig->get( 'use.gravatar' ) );			//  use gravatar as fallback
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
	$image	= HtmlTag::create( 'img', NULL, [
		'src'	=> $helperAvatar->getImageUrl(),
	//	'class'	=> 'img-polaroid',
	] );
}

$helperAvatar	= new View_Helper_UserAvatar( $env );
$helperAvatar->setUser( $user );
$helperAvatar->setSize( 256 );


$modelRole	= new Model_Role( $env );
$role		= $modelRole->get( $user->roleId );

$data	= print_m( $user, NULL, NULL, TRUE );

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.'&nbsp;'.$w->buttonCancel, [
	'href'		=> $from ?: './member/search',
	'class'		=> 'btn btn-small',
] );

$buttonRequest	= '';
$buttonRevoke	= '';
$buttonAccept	= '';
$buttonReject	= '';


$helperTime		= new View_Helper_TimePhraser( $env );

if( $user->userId !== $currentUserId ){
	if( $relation ){
		if( $relation->status == 1 && $relation->direction == "in" ){
			$buttonAccept	= HtmlTag::create( 'a', $iconAccept.'&nbsp;'.$w->buttonAccept, [
				'href'		=> './member/accept/'.$relation->userRelationId.'?from='.$from,
				'class'		=> 'btn btn btn-success',
			] );
			$buttonReject	= HtmlTag::create( 'a', $iconReject.'&nbsp;'.$w->buttonReject, [
				'href'		=> './member/reject/'.$relation->userRelationId.'?from='.$from,
				'class'		=> 'btn btn btn-danger',
			] );
		}
		if( $relation->status == 2 ){
			$buttonRevoke	= HtmlTag::create( 'a', $iconRemove.'&nbsp;'.$w->buttonRevoke, [
				'href'		=> './member/release/'.$relation->userRelationId.'?from='.$from,
				'class'		=> 'btn btn-small btn-inverse',
				'onclick'	=> "if(!confirm('Wirklich?')) return false;",
			] );
		}
	}
	else{
		$buttonRequest	= HtmlTag::create( 'a', $iconRequest.'&nbsp;'.$w->buttonRequest, [
			'href'		=> './member/request/'.$user->userId.'?from='.$from,
			'class'		=> 'btn btn-small btn-primary',
		] );
	}
}

function renderFacts( $facts, $class = 'dl-horizontal' ){
	$list	= [];
	foreach( $facts as $term => $values )
		$list[]	= '<dt>'.$term.'</dt><dd>'.join( '</dd><dd>', $values ).'</dd>';
	return '<dl class="'.$class.'">'.join( $list ).'</dl>';
}

$facts	= [];
$facts[$w->labelUsername]	= ['<big><strong>'.$user->username.'</strong></big>'];
if( $isRelated || $isCurrentUser ){
	$facts[$w->labelName]	= [$user->firstname.' '.$user->surname];
	$facts[$w->labelRole]	= [$role->title];
	$facts[$w->labelEmail]	= ['<a href="mailto:'.$user->email.'">'.$user->email.'</a>'];
	if( $user->phone )
		$facts[$w->labelPhone]	= [$user->phone];
	if( $user->fax )
		$facts[$w->labelFax]	= [$user->fax];
}

$facts[$w->labelRegisteredAt]	= [$helperTime->convert( $user->createdAt, TRUE, $w->labelRegisteredAt_prefix, $w->labelRegisteredAt_suffix )];
if( $user->loggedAt )
	$facts[$w->labelLoggedAt]	= [$helperTime->convert( $user->loggedAt, TRUE, $w->labelLoggedAt_prefix, $w->labelLoggedAt_suffix )];
if( $user->activeAt )
	$facts[$w->labelActiveAt]	= [$helperTime->convert( $user->activeAt, TRUE, $w->labelActiveAt_prefix, $w->labelActiveAt_suffix )];
$facts[$w->labelStatus]			= [$words['user-states'][$user->status]];
if( !$relation )
	$relation	= (object) ['status' => 0];
$facts[$w->labelRelation]	= [$words['relation-states'][$relation->status]];

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

extract( $view->populateTexts( ['top', 'bottom'], 'html/member/' ) );

$tabs	= View_Member::renderTabs( $env, '' );

return $tabs.$textTop.'
<div class="row-fluid">
	<div class="span8">
		'.$panelInfo.'
	</div>
</div>'.$textBottom;
