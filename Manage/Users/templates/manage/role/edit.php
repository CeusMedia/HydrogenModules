<?php
$optAccess	= array();
foreach( $words['type-access'] as $key => $label ){
	$selected		= $key == $role->access;
	$class			= 'role-access access'.$key;
	$optAccess[]	= UI_HTML_Elements::Option( (string) $key, $label, $selected, FALSE, $class );
}
$optAccess	= join( $optAccess );

$optRegister	= array();
foreach( $words['type-register'] as $key => $label ){
	$selected		= $key == $role->register;
	$class			= 'role-register register'.$key;
	$optRegister[]	= UI_HTML_Elements::Option( (string) $key, $label, $selected, FALSE, $class );
}
$optRegister	= join( $optRegister );

$panelEdit	= '
<div class="content-panel">
	<h3>'.$words['edit']['legend'].'</h3>
	<div class="content-panel-inner">
		<form name="editRole" action="./manage/role/edit/'.$roleId.'" method="post">
			<div class="row-fluid">
				<div class="span6">
					<label for="title">'.$words['edit']['labelTitle'].'</label>
					'.UI_HTML_Elements::Input( 'title', $role->title, 'span12' ).'
				</div>
				<div class="span3">
					<label for="access">'.$words['edit']['labelAccess'].'</label>
					'.UI_HTML_Elements::Select( 'access', $optAccess , 'span12' ).'
				</div>
				<div class="span3">
					<label for="register">'.$words['edit']['labelRegister'].'</label>
					'.UI_HTML_Elements::Select( 'register', $optRegister , 'span12' ).'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="description">'.$words['edit']['labelDescription'].'</label>
			<!--		'.UI_HTML_Elements::Textarea( 'description', $role->description, 'xl-l' ).'-->
					'.UI_HTML_Tag::create( 'textarea', $role->description, array( 'class' => 'span12', 'name' => 'description', 'rows' => 4 ) ).'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12 buttonbar">
					'.UI_HTML_Elements::LinkButton( './manage/role', '<i class="icon-arrow-left"></i> '.$words['edit']['buttonCancel'], 'btn' ).'
					'.UI_HTML_Elements::Button( 'saveRole', '<i class="icon-ok icon-white"></i> '.$words['edit']['buttonSave'], 'btn btn-success' ).'
					&nbsp;&nbsp;|&nbsp;&nbsp;
					'.UI_HTML_Elements::LinkButton( './manage/role/remove/'.$roleId, '<i class="icon-remove icon-white"></i> '.$words['edit']['buttonRemove'], 'btn btn-danger', 'Wirklich?' ).'
					&nbsp;&nbsp;|&nbsp;&nbsp;
					'.UI_HTML_Elements::LinkButton( './manage/user/add?roleId='.$roleId, '<i class="icon-plus icon-white"></i> '.$words['edit']['buttonAddUser'], 'btn btn-info btn-small' ).'
					'.UI_HTML_Elements::LinkButton( './manage/user/filter?roleId='.$roleId, '<i class="icon-search"></i> '.$words['edit']['buttonFilter'], 'btn btn-small' ).'
				</div>
			</div>
		</form>
	</div>
</div>
';

$panelRights	= $view->loadTemplateFile( 'manage/role/edit.rights.php' );
//$panelInfo		= $view->loadTemplateFile( 'manage/role/edit.info.php' );

$w				= (object) $words['info'];
$helperTime	= new View_Helper_TimePhraser( $env );
$createdAt		= 'vor '.$helperTime->convert( $role->createdAt, TRUE );
$modifiedAt		= $role->modifiedAt ? 'vor '.$helperTime->convert( $role->modifiedAt, TRUE ) : '-';
$panelInfo		= '
<div class="content-panel">
	<h3>'.$w->legend.'</h3>
	<div class="content-panel-inner">
		<dl class="dl-horizontal">
			<dt>'.$w->labelUserCount.'</dt>
			<dd>'.$userCount.'</dd>
			<dt>'.$w->labelCreatedAt.'</dt>
			<dd>'.$createdAt.'</dd>
			<dt>'.$w->labelModifiedAt.'</dt>
			<dd>'.$modifiedAt.'</dd>
		</dl>
	</div>
</div>
';


extract( $view->populateTexts( array( 'index.top', 'index.bottom' ), 'html/manage/role/' ) );

return $textIndexTop.'
<!--<h2><span class="muted">Rolle</span> '.$role->title.'</h2>-->
<div class="row-fluid">
	<div class="span9">
		'.$panelEdit.'
	</div>
	<div class="span3">
		'.$panelInfo.'
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		'.$panelRights.'
	</div>
</div>'.$textIndexBottom;
?>
