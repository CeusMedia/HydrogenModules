<?php
$clock	= new Alg_Time_Clock();
$roleId	= 4;
$rows	= array();
foreach( $actions as $controller => $class ){
	$list	= array();
	foreach( $class->methods as $action => $method ){
		$access	= $acl->hasRight( $roleId, $controller, $action );
		$check	= "";
		$for	= NULL;
		$class	= 'red';
		$id		= 'input-role-right-'.$roleId.'-'.$controller.'-'.$action;
		switch( $access ){
			case -1:
				$id	= NULL;
				break;
			case -0:
				$class	= 'red changable';
				break;
			case 1:
				$class	= 'green changable';
				break;
			case 2:
				$class	= 'green';
				$id	= NULL;
				break;
		}
		$label	= UI_HTML_Tag::create( 'label', $method->name, array() );
		$list[]	= UI_HTML_Tag::create( 'li', $check.$label, array( 'class' => $class, 'id' => $id ) );
	}
	$list	= UI_HTML_Tag::create( 'ul', join( $list ), array() );
	$rows[]	= '<tr><td>'.$controller.'</td><td>'.$list.'</td></tr>';
}
$tableRights	= '<table><tr><th>Controller</th><th>Aktionen</th><th>Status</th></tr>'.join( $rows ).'</table>';

$tableRights	= '
<style>
#role-edit-rights {
}
#role-edit-rights li {
	float: left;
	display: block;
	padding: 0px 4px;
	margin-right: 1px;
	font-size: 0.9em;
	color: #3F3F3F;
	}

#role-edit-rights li.changable {
	color: black;
	cursor: pointer;
	}
#role-edit-rights li.changable label {
	cursor: pointer;
	}

#role-edit-rights li.green {
	background-color: #BFFFBF;
	}
#role-edit-rights li.green.changable {
	background-color: #7FFF7F;
	}
#role-edit-rights li.red {
	background-color: #FFBFBF;
	}
#role-edit-rights li.red.changable {
	background-color: #FF7F7F;
	}
</style>
<script>
</script>
<fieldset id="role-edit-rights">
	<legend>Rechte 2</legend>
	'.$tableRights.'
</fieldset>
';

$script	= '
$(document).ready(function(){
	$("#role-edit-rights li.changable").bind("mousedown",function(){
		var id = $(this).attr("id");
		var parts = id.split(/-/);
		var action = parts.pop();
		var controller = parts.pop();
		$.ajax({
			url: "./manage/role/ajaxChangeRight/'.$roleId.'/"+controller+"/"+action,
			dataType: "json",
			context: $(this),
			success: function(data){
				if(data)
					$(this).removeClass("red").addClass("green");
				else
					$(this).removeClass("green").addClass("red");
			}
		});
	});
});
';
$this->env->getPage()->js->addScript( $script );
//remark( $clock->stop( 6, 0) );
//die;
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
	'.$tableRights.'

</div>
<div style="clear: both"></div>
';
?>
