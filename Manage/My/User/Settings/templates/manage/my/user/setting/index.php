<?php
$w			= (object) $words['index'];
$tabs		= View_Manage_My_User::renderTabs( $env, 'setting' );

$panelSettings	= '<div class="muted">'.$w->noSettings.'</div>';
if( isset( $modules ) && count( $modules ) ){
	$formUri 	= './manage/my/user/setting/update'.( $from ? '?from='.$from : '' );
	$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => "icon-ok icon-white" ) );
	$buttonSave	= UI_HTML_Tag::create( 'button', $iconSave.' '.$w->buttonSave, array(
		'type'		=> 'submit',
		'name'		=> 'save',
		'class'		=> 'btn btn-primary',
		'disabled'	=> isset( $modules ) && count( $modules ) ? NULL : 'disabled',
	) );

	$panels		= array();
	foreach( $modules as $module ){
		$moduleWords	= $view->getModuleWords( $module );
		$key			= isset( $moduleWords['title'] ) ? $moduleWords['title'] : $module->id;
		$panel			= $view->renderModuleSettings( $module, $settings, $moduleWords, $from );
		$panel ? $panels[$key]	= $panel : NULL;
	}
	ksort( $panels );
	if( $panels ){
		$panelSettings	= '
<form name="form-manage-my-user-settings" action="'.$formUri.'" method="post" class="form-changes-auto">
	<div class="row-fluid">
		<div class="span12">
			'.join( '<br/>', $panels ).'
		</div>
		<br/>
	</div>
	<div class="buttonbar">'.$buttonSave.'</div>
</form>';
	}
}

return $tabs.'
<div class="content-panel" id="manageMyUserModuleSettings">
	<h3>'.$w->legend.'</h3>
	<div class="content-panel-inner">
		'.$panelSettings.'
	</div>
</div>
<script>
$(document).ready(function(){
	$("#manageMyUserModuleSettings :input").bind("keyup mouseup change",function(){
		var row = $(this).closest("div.row-fluid");
		var changed = row.data("value") != $(this).val();
		changed ? row.addClass("modified") : row.removeClass("modified");
	});
});
</script>';
?>
