<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$optAccess	= [];
foreach( $words['type-access'] as $key => $label ){
	$selected		= $key == $role->access;
	$class			= 'role-access access'.$key;
	$optAccess[]	= HtmlElements::Option( (string) $key, $label, $selected, FALSE, $class );
}
$optAccess	= join( $optAccess );

$optRegister	= [];
foreach( $words['type-register'] as $key => $label ){
	$selected		= $key == $role->register;
	$class			= 'role-register register'.$key;
	$optRegister[]	= HtmlElements::Option( (string) $key, $label, $selected, FALSE, $class );
}
$optRegister	= join( $optRegister );

$panelEdit	= '
	<form name="editRole" action="./admin/role/edit/'.$roleId.'" method="post">
		<fieldset>
			<legend>'.$words['edit']['legend'].'</legend>
			<ul class="input">
				<li>
					<label for="title">'.$words['edit']['labelTitle'].'</label><br/>
					'.HtmlElements::Input( 'title', $role->title/*, 'xl'*/ ).'
				</li>
				<li>
				<label for="description">'.$words['edit']['labelDescription'].'</label><br/>
		<!--		'.HtmlElements::Textarea( 'description', $role->description, 'xl-l' ).'-->
				'.HtmlTag::create( 'textarea', $role->description, array( 'name' => 'description', 'rows' => 4 ) ).'
				</li>
				<li>
					<label for="access">'.$words['edit']['labelAccess'].'</label><br/>
					'.HtmlElements::Select( 'access', $optAccess , 'm' ).'
				</li>
				<li>
					<label for="register">'.$words['edit']['labelRegister'].'</label><br/>
					'.HtmlElements::Select( 'register', $optRegister , 'm' ).'
				</li>
			</ul>
			<div class="buttonbar">
				'.HtmlElements::LinkButton( './admin/role', $words['edit']['buttonCancel'], 'button cancel' ).'
				'.HtmlElements::Button( 'saveRole', $words['edit']['buttonSave'], 'button save' ).'
				&nbsp;&nbsp;|&nbsp;&nbsp;
				'.HtmlElements::LinkButton( './admin/role/remove/'.$roleId, $words['edit']['buttonRemove'], 'button remove', 'Wirklich?' ).'
			</div>
		</fieldset>
	</form>
';

$rights	= $view->loadTemplateFile( 'admin/role/edit.rights.php' );

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