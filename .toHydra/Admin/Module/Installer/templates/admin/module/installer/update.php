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
<div class="column-clear"></div>';
?>
