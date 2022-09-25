<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w			= (object) $words['edit-panel-users'];

$iconAdd		= HtmlTag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
$iconRemove		= HtmlTag::create( 'i', '', array( 'class' => 'icon-trash icon-white' ) );

$canEditUsers	= $env->getAcl()->has( 'manage/user', 'edit' );

$useMembers		= $env->getModules()->has( 'Members' );
if( $useMembers ){
	$helperMember	= new View_Helper_Member( $env );
	$helperMember->setMode( 'inline' );
}

$list	= [];
foreach( $projectUsers as $user ){
	$class	= 'role role'.$user->roleId;
	$from	= '?from=./manage/project/edit/'.$project->projectId;
	if( $useMembers ){
		$url	= './member/view/'.$user->userId;
		if( $canEditUsers )
			$url	= './manage/user/edit/'.$user->userId;
		$helperMember->setUser( $user );
//		if( $user->userId !== $currentUserId )
			$helperMember->setLinkUrl( $url.$from );
		$label	= $helperMember->render();
	}
	else{
		$label	= HtmlTag::create( 'span', $user->username, array( 'class' => $class ) );
		if( $canEditUsers ){
			$url	= './admin/user/edit/'.$user->userId.$from;
			$label	= HtmlTag::create( 'a', $user->username, array( 'href' => $url, 'class' => $class ) );
		}
	}
	$url	= './manage/project/removeUser/'.$project->projectId.'/'.$user->userId;
	$remove	= HtmlTag::create( 'button', $iconRemove, array( 'type' => 'button', 'class' => 'btn btn-mini btn-inverse pull-right disabled' ) );
	if( ( $project->creatorId && $user->userId !== $project->creatorId ) || ( !$project->creatorId && $user->userId !== $currentUserId ) )
		$remove	= HtmlTag::create( 'a', $iconRemove, array( 'href' => $url, 'class' => 'btn btn-mini btn-inverse pull-right' ) );
	if( count( $projectUsers ) === 1 )
		$remove	= '';
	$list[$user->username]	= HtmlTag::create( 'li', $remove.$label, array( 'class' => 'autocut' ) );
}
ksort( $list );
$list	= HtmlTag::create( 'ul', $list );

$optUser	= array( '' => '');
foreach( $users as $user )
	if( !array_key_exists( $user->userId, $projectUsers ) )
		if( $user->status > 0 )
			$optUser[$user->userId]	= $user->username;
$optUser	= UI_HTML_Elements::Options( $optUser );

$buttonAdd	= UI_HTML_Elements::Button( 'addUser', $iconAdd.' hinzufügen', 'btn btn-small btn-primary' );
if( !$canEdit )
	$buttonAdd	= HtmlTag::Button( 'addUser', $iconAdd.' hinzufügen', 'btn btn-small btn-primary disabled', NULL, TRUE );

return '
<div class="content-panel content-panel-form">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form id="project-users" name="" action="./manage/project/addUser/'.$project->projectId.'" method="post">
			<div class="row-fluid">
				<div class="span12">
					'.$list.'
				</div>
			</div>
			<br/>
			<div class="row-fluid">
				<div class="span12">
					<label class="not-mandatory">Benutzer</label>
					<select name="userId" id="input_userId" class="span12 max not-mandatory">'.$optUser.'</select>
				</div>
			</div>
			<div class="buttonbar">
				'.$buttonAdd.'
			</div>
		</form>
	</div>
</div>';
?>
