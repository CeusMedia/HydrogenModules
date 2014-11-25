<?php

$w	= (object) $words['update'];

$attributes		= array( 'type' => 'button', 'class' => 'button cancel auto-back', 'readonly' => 'readonly', 'disabled' => 'disabled' );
$buttonBack		= UI_HTML_Tag::create( 'button', '<span>'.$w->buttonBack.'</span>', $attributes );
$buttonUpdate	= UI_HTML_Elements::Button( 'doUpdate', $w->buttonUpdate, 'button update' );

$panelChanges	= $view->loadTemplateFile( 'admin/module/installer/update.changes.php' );
$panelDatabase	= $view->loadTemplateFile( 'admin/module/installer/update.database.php' );
$panelLocal		= $view->loadTemplateFile( 'admin/module/installer/update.local.php' );
$panelInfo		= $view->loadTemplateFile( 'admin/module/installer/update.info.php' );
$tableConfig	= $view->loadTemplateFile( 'admin/module/installer/update.config.php' );
$panelFiles		= $view->loadTemplateFile( 'admin/module/installer/update.files.php' );
$panelDev		= $view->loadTemplateFile( 'admin/module/installer/update.dev.php' );

$panelType	= '
	<h4>Installationstyp</h4>
	<div>
		<input type="radio" name="type" id="input_type_link" value="link" checked="checked"/>
		<label for="input_type_link"><acronym title="'.$w->textLink.'">'.$w->labelLink.'</acronym></label><br/>
		<input type="radio" name="type" id="input_type_copy" value="copy"/>
		<label for="input_type_copy"><acronym title="'.$w->textCopy.'">'.$w->labelCopy.'</acronym></label><br/>
	</div><br/>
	';

$urlForm	= './admin/module/installer/update/'.$moduleLocal->id;

return '
<h3 class="position">
	<span>'.$words['view']['heading'].'</span>
	<cite>'.$moduleLocal->title.'</cite>
</h3>
<br/>
<div class="column-left-70">
	<form action="'.$urlForm.'" method="post">
		'.$panelChanges.'
		'.$panelFiles.'
		'.$tableConfig.'
		'.$panelDatabase.'
		<fieldset>
			<legend class="module-add">Modul aktualisieren</legend>
			'.$panelType.'
			<div class="buttonbar">
				'.$buttonBack.'
				'.$buttonUpdate.'
			</div>
		</fieldset>
	</form>
</div>
<div class="column-right-30">
	'.$panelInfo.'
	'.$panelLocal.'
</div>
<br/>
<div class="column-clear" id="panel-dev-modules">
	'.$panelDev.'
</div>
<script>
$(document).ready(function(){
	AdminModuleUpdater.init();
});
</script>
<style>
dl.general > dt{
	clear: left;
	float: left;
	width: 110px;
	}
dl.general > dd{
	float: left;
	}

table.module-update-files tr.status-new {background-color: #DFFFDF}
table.module-update-files tr.status-installed {background-color: #FFFFDF; opacity: 0.75}
table.module-update-files tr.status-changed {background-color: #FFDFDF}
table.module-update-files tr.status-linked {background-color: #EFEFEF; opacity: 0.75}
table.module-update-files tr.status-foreign {background-color: #DFDFFF}
table.module-update-files tr.status-refered {background-color: #DFDFFF; opacity: 0.75}

#panel-dev-modules {
	opacity: 0.5;
	}
#panel-dev-modules:hover {
	opacity: 1;
	}

</style>
<div class="column-clear"></div>';
?>
