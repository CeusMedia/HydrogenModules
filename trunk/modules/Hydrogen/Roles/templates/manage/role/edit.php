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

$formEdit	= '
	<form name="editRole" action="./manage/role/edit/'.$roleId.'" method="post">
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
				'.UI_HTML_Elements::LinkButton( './manage/role', $words['edit']['buttonCancel'], 'button cancel' ).'
				'.UI_HTML_Elements::Button( 'saveRole', $words['edit']['buttonSave'], 'button save' ).'
				&nbsp;&nbsp;|&nbsp;&nbsp;
				'.UI_HTML_Elements::LinkButton( './manage/role/remove/'.$roleId, $words['edit']['buttonRemove'], 'button remove', 'Wirklich?' ).'
			</div>
		</fieldset>
	</form>
';

$formRightEdit	= '';
$formRightAdd	= '';
switch( $role->access ){

	case Model_Role::ACCESS_FULL:
		$formRightEdit	= '
	<fieldset>
		<legend>Rechte</legend>
		<em>Vollzugriff auf alle Controller und Actions.</em>
	</fieldset>
		';
		$formRightAdd	= '';
		break;

	case Model_Role::ACCESS_ACL:
		$listControllers	= array();
		foreach( $rights as $right )
			$listControllers[$right->controller][]	= $right->action;

		$list	= array();
		foreach( $listControllers as $controller => $actions ){
			$listActions	= array();
			$controller		= str_replace( '/', '-', $controller );
			foreach( $actions as $action ){
				$label			= UI_HTML_Tag::create( 'span', $action, array( 'class' => 'label' ) );
				$urlRemove		= './manage/role/removeRight/'.$roleId.'/'.$controller.'/'.$action;
				$linkRemove		= UI_HTML_Elements::Link( $urlRemove, 'entfernen' );
				$spanRemove		= UI_HTML_Tag::create( 'span', $linkRemove, array( 'class' => 'remove' ) );
				$listActions[]	= UI_HTML_Elements::ListItem( $label.$spanRemove, 1, array( 'class' => 'right-action' ) );
			}
			$actions	= '';
			if( $listActions )
				$actions	= UI_HTML_Elements::unorderedList( $listActions, 1, array( 'class' => 'right-actions' ) );
			$label		= UI_HTML_Tag::create( 'span', $controller, array( 'class' => 'label' ) );
			$item		= UI_HTML_Elements::ListItem( $label.$actions, 0, array( 'class' => 'right-controller' ) );
			$list[]	= $item;
		}
		$formRightEdit	= '
	<form name="editRoleRights">
		<fieldset>
			<legend>Rechte</legend>
			'.UI_HTML_Elements::unorderedList( $list, 0, array( 'class' => 'right-controllers' ) ).'
		</fieldset>
	</form>
	';
	$formRightAdd	= '
	<form name="addRoleRight" action="./manage/role/addRight/'.$roleId.'" method="post">
		<fieldset>
			<legend>'.$words['addRight']['legend'].'</legend>
				<ul class="input">
					<li>
						<label for="controller">Controller</label<br/>
						'.UI_HTML_Elements::Input( 'controller', NULL, 'm' ).'
					</li>
				</ul>
				<ul class="input">
					<li>
						<label for="action">Action</label<br/>
						'.UI_HTML_Elements::Input( 'action', NULL, 'm' ).'
					</li>
				</ul>
			<div class="buttonbar">
				'.UI_HTML_Elements::Button( 'saveRight', $words['addRight']['buttonSave'], 'button save' ).'
			</div>
		</fieldset>
	</form>
		';
		break;
	case Model_Role::ACCESS_NONE:
		$formRightEdit	= '
	<fieldset>
		<legend>Rechte</legend>
		<em>Zugriff komplett verweigert.</em>
	</fieldset>
		';
		break;
}

return '
<div class="column-control">
	'.$formRightEdit.'
	<br/>
	'.$formRightAdd.'
</div>
<div class="column-main">
	'.$formEdit.'
</div>
<div style="clear: both"></div>
';
?>
