<?php

$optStatus	= array();
foreach( array_reverse( $words['states'], TRUE ) as $key => $value ){
	$attributes		= array(
		'value'		=> $key,
		'class'		=> 'project status'.$key,
		'selected'	=> ( $key == $project->status ? 'selected' : NULL )
	);
	$optStatus[]	= UI_HTML_Tag::create( 'option', $value, $attributes );
}
$optStatus		= join( '', $optStatus );

$canEditUsers	= $env->getAcl()->has( 'manage/user', 'edit' );

$list	= array();
foreach( $projectUsers as $user ){
	$class	= 'role role'.$user->roleId;
	$label	= UI_HTML_Tag::create( 'span', $user->username, array( 'class' => $class ) );
	if( $canEditUsers ){
		$url	= './manage/user/edit/'.$user->userId;
		$label	= UI_HTML_Tag::create( 'a', $user->username, array( 'href' => $url, 'class' => $class ) );
	}
	$url	= './manage/project/removeUser/'.$project->projectId.'/'.$user->userId;
	$remove	= UI_HTML_Elements::LinkButton( $url, NULL, array( 'class' => 'button tiny remove' ) );
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


if( isset( $missions ) ){
	$url	= './work/mission/filter?projects[]='.$project->projectId;
	$button	= UI_HTML_Elements::LinkButton( $url, 'anzeigen', 'button filter' );
	$label	= count( $missions ).'&nbsp;'.$button;
	$data[]	= UI_HTML_Tag::create( 'dt', 'Aufgaben' ).UI_HTML_Tag::create( 'dd', $label );
}

if( isset( $issues ) ){
	$url	= './work/issue/filter?projects[]='.$project->projectId;
	$button	= UI_HTML_Elements::LinkButton( $url, 'anzeigen', 'button filter' );
	$label	= count( $missions ).'&nbsp;'.$button;
	$data[]	= UI_HTML_Tag::create( 'dt', 'Probleme' ).UI_HTML_Tag::create( 'dd', $label );
}

$panelInfo	= '';
if( $data ){
	$panelInfo	= '
<fieldset>
	<legend class="icon info">Informationen</legend>
	<dl>
		'.join( $data ).'
	</dl>
</fieldset>
';

}


$panelUsers	= '
<form name="" action="./manage/project/addUser/'.$project->projectId.'" method="post">
	<fieldset id="project-users">
		<legend>Beteiligte Benutzer</legend>
		'.$list.'
		<br/>
		<label class="not-mandatory">Benutzer</label><br/>
		<select name="userId" id="input_userId" class="max not-mandatory">'.$optUser.'</select>
		<div class="buttonbar">
			'.UI_HTML_Elements::Button( 'addUser', 'hinzufügen', 'button add' ).'
		</div>
	</fieldset>
</form>
';

$panelEdit	= '
<form name="" action="./manage/project/edit/'.$project->projectId.'" method="post">
	<fieldset>
		<legend>'.$words['add']['legend'].'</legend>
		<ul class="input">
			<li>
				<label for="input_title" class="mandatory">'.$words['add']['labelTitle'].'</label><br/>
				<input type="text" name="title" id="input_title" class="max mandatory" value="'.htmlentities( $project->title, ENT_COMPAT, 'UTF-8' ).'"/>
			</li>
			<li>
				<label for="input_description">'.$words['add']['labelDescription'].'</label><br/>
				<textarea name="description" id="input_description" class="max">'.htmlentities( $project->description, ENT_COMPAT, 'UTF-8' ).'</textarea>
			</li>
			<li class="column-left-20">
				<label for="input_status" class="mandatory">'.$words['add']['labelStatus'].'</label><br/>
				<select name="status" id="input_status" class="max">'.$optStatus.'</select>
			</li>
			<li class="column-left-80">
				<label for="input_url">'.$words['add']['labelUrl'].'</label><br/>
				<input type="text" name="url" id="input_url" class="max" value="'.htmlentities( $project->url, ENT_COMPAT, 'UTF-8' ).'"/>
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::LinkButton( './manage/project', $words['add']['buttonCancel'], 'button cancel' ).'
			'.UI_HTML_Elements::Button( 'save', $words['add']['buttonSave'], 'button add' ).'
		</div>
	</fieldset>
</form>
';

return '
<div class="column-left-75">
	'.$panelEdit.'
</div>
<div class="column-left-25">
	'.$panelUsers.'
	'.$panelInfo.'
</div>
<div class="column-clear"></div>
';
?>
