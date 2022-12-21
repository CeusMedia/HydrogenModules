<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$optAccess	= [];
foreach( $words['type-access'] as $key => $label ){
	$selected	= $key == $role->get( 'access' );
	$class		= 'role-access access'.$role->get( 'access' );
	$optAccess[]	= HtmlElements::Option( (string) $key, $label, $selected, FALSE, $class );
}
$optAccess	= join( $optAccess );

$optRegister	= [];
foreach( $words['type-register'] as $key => $label ){
	$selected	= $key == $role->get( 'register' );
	$class		= 'role-register register'.$role->get( 'register' );
	$optRegister[]	= HtmlElements::Option( (string) $key, $label, $selected, FALSE, $class );
}
$optRegister	= join( $optRegister );

return '
<form name="addRole" action="./admin/role/add" method="post">
	<fieldset>
		<legend>'.$words['add']['legend'].'</legend>
		<ul class="input">
			<li>
				<label for="title">'.$words['add']['labelTitle'].'</label><br/>
				'.HtmlElements::Input( 'title', $role->get( 'title' )/*, 'xl'*/ ).'
			</li>
			<li>
				<label for="description">'.$words['add']['labelDescription'].'</label><br/>
		<!--		'.HtmlElements::Textarea( 'description', $role->get( 'description' ), 'xl-l' ).'-->
				'.HtmlTag::create( 'textarea', $role->get( 'description' ), array( 'name' => 'description', 'rows' => 4 ) ).'
			</li>
			<li>
				<label for="access">'.$words['add']['labelAccess'].'</label><br/>
				'.HtmlElements::Select( 'access', $optAccess, 'm' ).'
			</li>
			<li>
				<label for="register">'.$words['add']['labelRegister'].'</label><br/>
				'.HtmlElements::Select( 'register', $optRegister, 'm' ).'
			</li>
		</ul>
		<div class="buttonbar">
			'.HtmlElements::LinkButton( './admin/role', $words['edit']['buttonCancel'], 'button cancel' ).'
			'.HtmlElements::Button( 'saveRole', $words['edit']['buttonSave'], 'button save' ).'
		</div>
	</fieldset>
</form>
';
?>