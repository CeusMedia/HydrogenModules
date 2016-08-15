<?php

$w			= (object) $words['editRights'];
$rows		= array();

$showAll	= !TRUE;
foreach( $controllerActions as $controller ){
	$list		= array();
	foreach( $controller->methods as $action => $method ){
		$access	= $acl->hasRight( $roleId, $controller->name, $action );
		$check	= "";
		$for	= NULL;
		$title	= $words['type-right'][$access];
		$id		= 'input-role-right-'.$roleId.'-'.$controller->name.'-'.$action;
		$changable	= FALSE;
		switch( $access ){
			case -2:								//  public outside
				$class	= "gray";
				break;
			case 0:									//  not allowed
				$class	= "red changable";
				$changable	= true;
				break;
			case 1:									//  allowed by role right
				$class	= "green changable";
				$changable	= true;
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
		$label	= UI_HTML_Tag::create( 'span', $method->name, array() );
		$list[]	= UI_HTML_Tag::create( 'li', $check.$label, array(
			'class'	=> $class,
			'id'	=> $changable ? $id : NULL,
			'title'	=> $title,
			'style'	=> $changable || $showAll ? NULL : 'display: none',
		) );
	}
	if( $list ){
		$module		= 'local';
		if( $controller->module ){
			$module	= $controller->module->title;
			if( $controller->module->description ){
				$description	= array_slice( explode( "\n", $controller->module->description ), 0, 1 );
				$module			= UI_HTML_Tag::create( 'acronym', $module, array( 'title' => $description ) );
			}
		}
		$list		= UI_HTML_Tag::create( 'ul', join( $list ), array() );
		$path		= strtolower( str_replace( '_', '/', $controller->name ) ).'/';

		$labelPath			= UI_HTML_Tag::create( 'span', $path, array( 'class' => 'label-path' ) );
		$labelModule		= UI_HTML_Tag::create( 'small', '<br/>Modul: '.$module, array( 'class' => 'muted label-module', 'style' => $showAll ? NULL : 'display: none' ) );
		$labelController	= UI_HTML_Tag::create( 'small', '<br/>Controller: '.$controller->name, array( 'class' => 'muted label-controller', 'style' => $showAll ? NULL : 'display: none' ) );

		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $labelPath.$labelController.$labelModule, array( 'class' => 'column-controller autocut' ) ),
			UI_HTML_Tag::create( 'td', $list, array( 'class' => 'column-actions' ) ),
		), array( 'style' => $changable || $showAll ? NULL : 'display: none' ) );
	}
}
$tableRights	= '<table class="table table-condensed table-striped" style="table-layout: fixed">
	<colgroup>
		<col width="30%"/>
		<col width=""/>
	</colgroup>
	<thead>
		<tr>
			<th>'.$w->headController.'</th>
			<th>'.$w->headActions.'</th>
		</tr>
	</thead>
	<tbody>
		'.join( $rows ).'
	</tbody>
</table>
';

$script	= '
var ModuleManageUsers = {
	onChangeVisibleRightsToggle: function(event){
		var toggle = $(this);
		var showAll = toggle.is(":checked");
		var rows = $("#role-edit-rights tbody tr");
		rows.each(function(){
			var row = $(this);
			if(showAll){
				row.fadeIn({duration: 0, queue: false});
				row.find("li").show();
				row.find(".label-module,.label-controller").show();
			}
			else{
				row.find(".label-module,.label-controller").hide();
				var hasChangables = $(this).find("li.changable").size();
				if(!hasChangables)
					row.fadeOut({duration: 0, queue: false});
				else{
					row.fadeIn({duration: 0, queue: false});
					row.find("li").not(".changable").hide();
				}
			}
		});
	},
	onChangeRightToggle: function(event){
		if(event.button != 0)
			return;
		var toggle = $(this);
		var id = toggle.attr("id");
		var parts = id.split(/-/);
		var action = parts.pop();
		var controller = parts.pop();
		toggle.addClass("yellow");
		$.ajax({
			url: "./manage/role/ajaxChangeRight/'.$roleId.'/"+controller+"/"+action,
			dataType: "json",
			context: toggle,
			success: function(data){
				if(data)
					$(this).removeClass("red").addClass("green");
				else
					$(this).removeClass("green").addClass("red");
				$(this).removeClass("yellow");
			}
		});
	}
};
';
$env->getPage()->js->addScript( $script );

$script	= '
$(document).ready(function(){
	$("#role-edit-rights li.changable").bind("mousedown", ModuleManageUsers.onChangeRightToggle );
	$("#input-toggle-rights-all").bind("change", ModuleManageUsers.onChangeVisibleRightsToggle);
});
';
$env->getPage()->js->addScript( $script );

return '
<div class="content-panel content-panel-form">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<label class="checkbox">
			'.UI_HTML_Tag::create( 'input', NULL, array(
				'type'		=> "checkbox",
				'id'		=> "input-toggle-rights-all",
				'checked'	=> $showAll ? 'checked' : NULL,
			) ).'
			'.$w->labelShowAll.'
		</label>
		<hr/>
		<div id="role-edit-rights">
			'.$tableRights.'
		</div>
	</div>
</div>
<script>
$(document).ready(function(){
});
</script>
';
?>
