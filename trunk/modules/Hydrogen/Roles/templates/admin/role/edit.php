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
	<form name="editRole" action="./admin/role/edit/'.$roleId.'" method="post">
		<fieldset>
			<legend>'.$words['edit']['legend'].'</legend>
			<ul class="input">
				<li>
					<label for="title">'.$words['edit']['labelTitle'].'</label><br/>
					'.UI_HTML_Elements::Input( 'title', $role->title/*, 'xl'*/ ).'
				</li>
				<li>
				<label for="description">'.$words['edit']['labelDescription'].'</label><br/>
		<!--		'.UI_HTML_Elements::Textarea( 'description', $role->description, 'xl-l' ).'-->
				'.UI_HTML_Tag::create( 'textarea', $role->description, array( 'name' => 'description', 'rows' => 4 ) ).'
				</li>
				<li>
					<label for="access">'.$words['edit']['labelAccess'].'</label><br/>
					'.UI_HTML_Elements::Select( 'access', $optAccess , 'm' ).'
				</li>
				<li>
					<label for="register">'.$words['edit']['labelRegister'].'</label><br/>
					'.UI_HTML_Elements::Select( 'register', $optRegister , 'm' ).'
				</li>
			</ul>
			<div class="buttonbar">
				'.UI_HTML_Elements::LinkButton( './admin/role', $words['edit']['buttonCancel'], 'button cancel' ).'
				'.UI_HTML_Elements::Button( 'saveRole', $words['edit']['buttonSave'], 'button save' ).'
				&nbsp;&nbsp;|&nbsp;&nbsp;
				'.UI_HTML_Elements::LinkButton( './admin/role/remove/'.$roleId, $words['edit']['buttonRemove'], 'button remove', 'Wirklich?' ).'
			</div>
		</fieldset>
	</form>
';

$rights	= $this->loadTemplateFile( 'admin/role/edit.rights.php' );

return '
<div class="column-control">
</div>
<div class="column-main">
	'.$panelEdit.'
	'.$rights.'
</div>
<div style="clear: both"></div>
';
?>