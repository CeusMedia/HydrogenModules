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
	$remove	= UI_HTML_Tag::create( 'a', '<i class="icon-remove icon-white"></i>', array( 'href' => $url, 'class' => 'btn btn-mini btn-danger pull-right' ) );
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
</fieldset>';

}



$iconAdd	= '<i class="icon-plus icon-white"></i>';
$buttonAdd	= UI_HTML_Elements::Button( 'addUser', $iconAdd.' hinzufügen', 'btn not-btn-small btn-success' );
if( !$canEdit )
	$buttonAdd	= UI_HTML_Tag::Button( 'addUser', $iconAdd.' hinzufügen', 'btn not-btn-small btn-success disabled', NULL, TRUE );
$panelUsers	= '
<h3>Beteiligte Benutzer</h3>
<form id="project-users" name="" action="./manage/project/addUser/'.$project->projectId.'" method="post">
	<div class="row-fluid">
		<div class="span12">
			'.$list.'
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12">
			<label class="not-mandatory">Benutzer</label>
			<select name="userId" id="input_userId" class="span12 max not-mandatory">'.$optUser.'</select>
		</div>
		<div class="buttonbar">
			'.$buttonAdd.'
		</div>
	</div>
</form>';

$optCompany	= "";
if( isset( $projectCompanies ) ){
	$optCompany	= array();
	foreach( $projectCompanies as $company )
		$optCompany[$company->companyId]	= $company->title;
	$optCompany	= UI_HTML_Elements::Options( $optCompany, $projectId );
}

$w	= (object) $words['edit'];

$buttonSave	= UI_HTML_Elements::Button( 'save', $words['edit']['buttonSave'], 'button add' );
if( !$canEdit )
	$buttonSave	= UI_HTML_Elements::Button( 'save', $words['edit']['buttonSave'], 'button add', NULL, TRUE );
$panelEdit	= '
<form name="" action="./manage/project/edit/'.$project->projectId.'" method="post">
	<div class="row-fluid">
		<div class="span12">
			<label for="input_title" class="mandatory">'.$w->labelTitle.'</label>
			<input type="text" name="title" id="input_title" class="span12 max mandatory" value="'.htmlentities( $project->title, ENT_COMPAT, 'UTF-8' ).'"/>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12">
			<label for="input_description">'.$w->labelDescription.'</label>
			<textarea name="description" id="input_description" rows="6" class="span12 max CodeMirror-auto">'.htmlentities( $project->description, ENT_COMPAT, 'UTF-8' ).'</textarea>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span4">
			<label for="input_status" class="mandatory">'.$w->labelStatus.'</label>
			<select name="status" id="input_status" class="span12 max">'.$optStatus.'</select>
		</div>
		<div class="span8">
			<label for="input_url">'.$w->labelUrl.'</label>
			<input type="text" name="url" id="input_url" class="span12 max" value="'.htmlentities( $project->url, ENT_COMPAT, 'UTF-8' ).'"/>
		</div>
	</div>
	<div class="buttonbar">
		<a href="./manage/project" class="btn btn-small"><i class="icon-arrow-left"></i> '.$w->buttonCancel.'</a>
		<button type="submit" name="save" class="btn btn-small btn-success"><i class="icon-ok icon-white"></i> '.$w->buttonSave.'</button>
	</div>
<!--			<li class="">
				<label for="input_companyId">Unternehmen</label>
				<select name="companyId" id="input_companyId" class="max">'.$optCompany.'</select>
			</li>
-->		</ul>
	</fieldset>
</form>
';

return '
<div class="row-fluid">
	<div class="span8">
		'.$panelEdit.'
	</div>
	<div class="span4">
		'.$panelUsers.'
		'.$panelInfo.'
	</div>
</div>
';
?>
