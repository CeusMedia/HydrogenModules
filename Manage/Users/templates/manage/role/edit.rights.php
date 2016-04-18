<?php

$rows	= array();
foreach( $actions as $controller => $class ){
	$list	= array();
	foreach( $class->methods as $action => $method ){
		$access	= $acl->hasRight( $roleId, $controller, $action );
		$check	= "";
		$for	= NULL;
		$title	= $words['type-right'][$access];
		$id		= 'input-role-right-'.$roleId.'-'.$controller.'-'.$action;
		switch( $access ){
			case -2:								//  public outside
				$class	= "gray";
				break;
			case 0:									//  not allowed
				$class	= "red changable";
				break;
			case 1:									//  allowed by role right
				$class	= "green changable";
				break;
			case 2:									//  full access
			case 3:									//  public
			case 5:									//  public inside
				$class	= "green";
				break;
			case -1:								//  access denied
			default:
				$class	= "red";
				break;
		}
		$id		= preg_match( "/changable/", $class ) ? $id : NULL;
		$label	= UI_HTML_Tag::create( 'span', $method->name, array() );
		$list[]	= UI_HTML_Tag::create( 'li', $check.$label, array(
			'class'	=> $class,
			'id'	=> $id,
			'title'	=> $title,
		) );
	}
	if( $list ){
		$list	= UI_HTML_Tag::create( 'ul', join( $list ), array() );
		$rows[]	= '<tr><td>'.$controller.'</td><td>'.$list.'</td></tr>';
	}
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
<div class="content-panel content-panel-form">
	<h3>'.$words['editRights']['heading'].'</h3>
	<div class="content-panel-inner">
		<div id="role-edit-rights">
			'.$tableRights.'
		</div>
	</div>
</div>
';
?>
