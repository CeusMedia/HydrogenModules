<?php
$optAccess	= array();
foreach( $words['type-access'] as $key => $label ){
	$selected	= $key == $role->get( 'access' );
	$class		= 'role-access access'.$role->get( 'access' );
	$optAccess[]	= UI_HTML_Elements::Option( (string) $key, $label, $selected, FALSE, $class );
}
$optAccess	= join( $optAccess );

$optRegister	= array();
foreach( $words['type-register'] as $key => $label ){
	$selected	= $key == $role->get( 'register' );
	$class		= 'role-register register'.$role->get( 'register' );
	$optRegister[]	= UI_HTML_Elements::Option( (string) $key, $label, $selected, FALSE, $class );
}
$optRegister	= join( $optRegister );

extract( $view->populateTexts( array( 'index.top', 'index.bottom' ), 'html/manage/role/' ) );

return $textIndexTop.'
<h3>'.$words['add']['legend'].'</h3>
<form name="addRole" action="./manage/role/add" method="post">
	<div class="row-fluid">
		<div class="span6">
			<label for="title">'.$words['add']['labelTitle'].'</label>
			'.UI_HTML_Elements::Input( 'title', $role->get( 'title' ), 'span12' ).'
		</div>
		<div class="span3">
			<label for="access">'.$words['add']['labelAccess'].'</label>
			'.UI_HTML_Elements::Select( 'access', $optAccess, 'span12' ).'
		</div>
		<div class="span3">
			<label for="register">'.$words['add']['labelRegister'].'</label>
			'.UI_HTML_Elements::Select( 'register', $optRegister, 'span12' ).'
		</div>
	</div>
	<div class="row-fluid">
		<div class="span">
			<label for="description">'.$words['add']['labelDescription'].'</label>
			'.UI_HTML_Tag::create( 'textarea', $role->get( 'description' ), array( 'class' => 'span12', 'name' => 'description', 'rows' => 4 ) ).'
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12 buttonbar">
			'.UI_HTML_Elements::LinkButton( './manage/role', '<i class="icon-arrow-left"></i> '.$words['edit']['buttonCancel'], 'btn' ).'
			'.UI_HTML_Elements::Button( 'saveRole','<i class="icon-ok icon-white"></i> '. $words['edit']['buttonSave'], 'btn btn-success' ).'
		</div>
	</div>
</form>'.$textIndexBottom;
?>