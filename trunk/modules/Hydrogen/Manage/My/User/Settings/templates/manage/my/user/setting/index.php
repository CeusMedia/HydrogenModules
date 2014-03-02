<?php
$w			= (object) $words['index'];

$tabs		= View_Manage_My_User::renderTabs( $env, 'setting' );

$formUri 	= './manage/my/user/setting/update'.( $from ? '?from='.$from : '' );
$iconSave	= '<i class="icon-ok icon-white"></i>';
$buttonSave	= UI_HTML_Elements::Button( 'save', $iconSave.' '.$w->buttonSave, 'btn btn-success btn-small' );

$formUri 	= './manage/my/user/setting/update'.( $from ? '?from='.$from : '' );
$iconSave	= '<i class="icon-ok icon-white"></i>';

$panelSettings	= '<div class="muted">'.$w->noSettings.'</div>';
if( isset( $modules ) && count( $modules ) ){
	$panels		= array();
	foreach( $modules as $module ){
		$moduleWords	= $view->getModuleWords( $module );
		$key			= isset( $moduleWords['title'] ) ? $moduleWords['title'] : $module->id;
		$panel			= $view->renderModuleSettings( $module, $settings, $moduleWords, $from );
		$panel ? $panels[$key]	= $panel : NULL;
	}
	ksort( $panels );
	$buttonSave		= UI_HTML_Elements::Button( 'save', $iconSave.' '.$w->buttonSave, 'btn btn-success btn-small' );
	if( $panels ){
		$panelSettings	= '
<form name="form-manage-my-user-settings" action="'.$formUri.'" method="post">
	<div class="row-fluid">
		'.join( '<br/>', $panels ).'
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
