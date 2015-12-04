<?php

$w			= (object) $words['edit-panel-users'];

$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-trash icon-white' ) );

$canEditUsers	= $env->getAcl()->has( 'manage/user', 'edit' );

$list	= array();
foreach( $projectUsers as $user ){
	$class	= 'role role'.$user->roleId;
	$label	= UI_HTML_Tag::create( 'span', $user->username, array( 'class' => $class ) );
	if( $canEditUsers ){
		$url	= './admin/user/edit/'.$user->userId;
		$label	= UI_HTML_Tag::create( 'a', $user->username, array( 'href' => $url, 'class' => $class ) );
	}
	$url	= './manage/project/removeUser/'.$project->projectId.'/'.$user->userId;
	$remove	= UI_HTML_Tag::create( 'a', $iconRemove, array( 'href' => $url, 'class' => 'btn btn-mini btn-inverse pull-right' ) );
	if( count( $projectUsers ) === 1 )
		$remove	= '';
	$list[$user->username]	= UI_HTML_Tag::create( 'li', $remove.$label );
}
ksort( $list );
$list	= UI_HTML_Tag::create( 'ul', $list );

$optUser	= array( '' => '');
foreach( $users as $user )
	if( !array_key_exists( $user->userId, $projectUsers ) )
		if( $user->status > 0 )
			$optUser[$user->userId]	= $user->username;
$optUser	= UI_HTML_Elements::Options( $optUser );

$buttonAdd	= UI_HTML_Elements::Button( 'addUser', $iconAdd.' hinzufügen', 'btn btn-small btn-primary' );
if( !$canEdit )
	$buttonAdd	= UI_HTML_Tag::Button( 'addUser', $iconAdd.' hinzufügen', 'btn btn-small btn-primary disabled', NULL, TRUE );

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
