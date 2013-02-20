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
		$url	= './admin/user/edit/'.$user->userId;
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


$facts	= array();
if( isset( $missions ) && count( $missions ) ){
	$url	= './work/mission/filter?projects[]='.$project->projectId;
	$label	= UI_HTML_Tag::create( 'a', count( $missions ), array( 'href' => $url ) );
	$facts[]	= UI_HTML_Tag::create( 'dt', 'Aufgaben' ).UI_HTML_Tag::create( 'dd', $label );
}

if( isset( $issues ) ){
	$url	= './work/issue/filter?projects[]='.$project->projectId;
	$button	= UI_HTML_Elements::LinkButton( $url, 'anzeigen', 'button filter' );
	$label	= count( $missions ).'&nbsp;'.$button;
	$facts[]	= UI_HTML_Tag::create( 'dt', 'Probleme' ).UI_HTML_Tag::create( 'dd', $label );
}

$panelInfo	= '';
if( $facts ){
	$panelInfo	= '
<fieldset>
	<legend class="icon info">Informationen</legend>
	<dl>
		'.join( $facts ).'
	</dl>
</fieldset>
';

}



$buttonAdd	= UI_HTML_Elements::Button( 'addUser', 'hinzufügen', 'button add' );
if( !$canEdit )
	$buttonAdd	= UI_HTML_Elements::Button( 'addUser', 'hinzufügen', 'button add', NULL, TRUE );
$panelUsers	= '
<form name="" action="./manage/project/addUser/'.$project->projectId.'" method="post">
	<fieldset id="project-users">
		<legend>Beteiligte Benutzer</legend>
		'.$list.'
		<br/>
		<label class="not-mandatory">Benutzer</label><br/>
		<select name="userId" id="input_userId" class="max not-mandatory">'.$optUser.'</select>
		<div class="buttonbar">
			'.$buttonAdd.'
		</div>
	</fieldset>
</form>
';

$optCompany	= "";
if( isset( $projectCompanies ) ){
	$optCompany	= array();
	foreach( $projectCompanies as $company )
		$optCompany[$company->companyId]	= $company->title;
	$optCompany	= UI_HTML_Elements::Options( $optCompany, $projectId );
}


$buttonSave	= UI_HTML_Elements::Button( 'save', $words['edit']['buttonSave'], 'button add' );
if( !$canEdit )
	$buttonSave	= UI_HTML_Elements::Button( 'save', $words['edit']['buttonSave'], 'button add', NULL, TRUE );
$panelEdit	= '
<form name="" action="./manage/project/edit/'.$project->projectId.'" method="post">
	<fieldset>
		<legend class="icon edit">'.$words['edit']['legend'].'</legend>
		<ul class="input">
			<li>
				<label for="input_title" class="mandatory">'.$words['add']['labelTitle'].'</label><br/>
				<input type="text" name="title" id="input_title" class="max mandatory" value="'.htmlentities( $project->title, ENT_COMPAT, 'UTF-8' ).'"/>
			</li>
			<li>
				<label for="input_description">'.$words['add']['labelDescription'].'</label><br/>
				<textarea name="description" id="input_description" class="max cmGrowText cmClearInput">'.htmlentities( $project->description, ENT_COMPAT, 'UTF-8' ).'</textarea>
			</li>
			<li class="column-left-20">
				<label for="input_status" class="mandatory">'.$words['add']['labelStatus'].'</label><br/>
				<select name="status" id="input_status" class="max">'.$optStatus.'</select>
			</li>
			<li class="column-left-80">
				<label for="input_url">'.$words['add']['labelUrl'].'</label><br/>
				<input type="text" name="url" id="input_url" class="max cmClearInput" value="'.htmlentities( $project->url, ENT_COMPAT, 'UTF-8' ).'"/>
			</li>
<!--			<li class="">
				<label for="input_companyId">Unternehmen</label>
				<select name="companyId" id="input_companyId" class="max">'.$optCompany.'</select>
			</li>
-->		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::LinkButton( './manage/project', $words['edit']['buttonCancel'], 'button cancel' ).'
			'.$buttonSave.'
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
