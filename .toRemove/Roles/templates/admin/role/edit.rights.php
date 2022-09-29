<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$rows	= [];
foreach( $actions as $controller => $class ){
	$list	= [];
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
		$label	= HtmlTag::create( 'label', $method->name, [] );
		$list[]	= HtmlTag::create( 'li', $check.$label, array( 'class' => $class, 'id' => $id ) );
	}
	$list	= HtmlTag::create( 'ul', join( $list ), [] );
	$rows[]	= '<tr><td>'.$controller.'</td><td>'.$list.'</td></tr>';
}
$tableRights	= '<table><tr><th>Controller</th><th>Aktionen</th><th>Status</th></tr>'.join( $rows ).'</table>';

$script	= '
$(document).ready(function(){
	$("#role-edit-rights li.changable").on("mousedown",function(){
		if(event.button != 0)
			return;
		var id = $(this).attr("id");
		var parts = id.split(/-/);
		var action = parts.pop();
		var controller = parts.pop();
		$(this).addClass("yellow");
		$.ajax({
			url: "./admin/role/ajaxChangeRight/'.$roleId.'/"+controller+"/"+action,
			dataType: "json",
			context: $(this),
			success: function(data){
				$(this).removeClass("yellow");
				if(data)
					$(this).removeClass("red").addClass("green");
				else
					$(this).removeClass("green").addClass("red");
			}
		});
	});
});
';
$env->getPage()->js->addScript( $script );

return '
<fieldset id="role-edit-rights">
	<legend>Zugriffsrechte der Rolle</legend>
	'.$tableRights.'
</fieldset>
';
?>
