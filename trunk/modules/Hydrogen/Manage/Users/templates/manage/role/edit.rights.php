<?php
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
$tableRights	= '<table class="table table-condensed table-striped"><tr><th>Controller</th><th>Aktionen</th></tr>'.join( $rows ).'</table>';

$script	= '
$(document).ready(function(){
	$("#role-edit-rights li.changable").bind("mousedown",function(event){
		if(event.button != 0)
			return;
		var id = $(this).attr("id");
		var parts = id.split(/-/);
		var action = parts.pop();
		var controller = parts.pop();
		$(this).addClass("yellow");
		$.ajax({
			url: "./manage/role/ajaxChangeRight/'.$roleId.'/"+controller+"/"+action,
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
<h3>Zugriffsrechte</h3>
<div id="role-edit-rights">
	'.$tableRights.'
</div>
';
?>
