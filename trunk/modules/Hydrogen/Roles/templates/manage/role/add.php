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

return '
<form name="addRole" action="./manage/role/add" method="post">
	<fieldset>
		<legend>'.$words['add']['legend'].'</legend>
		<ul class="input">
			<li>
				<label for="title">'.$words['add']['labelTitle'].'</label><br/>
				'.UI_HTML_Elements::Input( 'title', $role->get( 'title' )/*, 'xl'*/ ).'
			</li>
			<li>
				<label for="description">'.$words['add']['labelDescription'].'</label><br/>
		<!--		'.UI_HTML_Elements::Textarea( 'description', $role->get( 'description' ), 'xl-l' ).'-->
				'.UI_HTML_Tag::create( 'textarea', $role->get( 'description' ), array( 'name' => 'description', 'rows' => 4 ) ).'
			</li>
			<li>
				<label for="access">'.$words['add']['labelAccess'].'</label><br/>
				'.UI_HTML_Elements::Select( 'access', $optAccess, 'm' ).'
			</li>
			<li>
				<label for="register">'.$words['add']['labelRegister'].'</label><br/>
				'.UI_HTML_Elements::Select( 'register', $optRegister, 'm' ).'
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::LinkButton( './manage/role', $words['edit']['buttonCancel'], 'button cancel' ).'
			'.UI_HTML_Elements::Button( 'saveRole', $words['edit']['buttonSave'], 'button save' ).'
		</div>
	</fieldset>
</form>
';
?>
