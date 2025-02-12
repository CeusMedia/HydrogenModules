<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;

/** @var View_Manage_My_User_Setting $view */
/** @var Web $env */
/** @var array<string,array<string,string>> $words */
/** @var ?string $from */
/** @var Entity_User_Settings[] $settings */
/** @var array[] $modules */

$w			= (object) $words['index'];
$tabs		= View_Manage_My_User::renderTabs( $env, 'setting' );

$panelSettings	= '<div class="muted">'.$w->noSettings.'</div>';
if( isset( $modules ) && count( $modules ) ){
	$formUri 	= './manage/my/user/setting/update'.( $from ? '?from='.$from : '' );
	$iconSave	= HtmlTag::create( 'i', '', ['class' => "icon-ok icon-white"] );
	$buttonSave	= HtmlTag::create( 'button', $iconSave.' '.$w->buttonSave, [
		'type'		=> 'submit',
		'name'		=> 'save',
		'class'		=> 'btn btn-primary',
		'disabled'	=> isset( $modules ) && [] !== $modules ? NULL : 'disabled',
	] );

	$panels		= [];
	foreach( $modules as $module ){
		$moduleWords	= $view->getModuleWords( $module );
		$key			= $moduleWords['title'] ?? $module->id;
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

$script	= 'jQuery("#manageMyUserModuleSettings :input").on("keyup mouseup change",function(){
	var row = jQuery(this).closest("div.row-fluid");
	var changed = row.data("value") != jQuery(this).val();
	changed ? row.addClass("modified") : row.removeClass("modified");
});';

$env->getPage()->js->addScriptOnReady($script, 9);

return $tabs.'
<div class="content-panel" id="manageMyUserModuleSettings">
	<h3>'.$w->legend.'</h3>
	<div class="content-panel-inner">
		'.$panelSettings.'
	</div>
</div>';
