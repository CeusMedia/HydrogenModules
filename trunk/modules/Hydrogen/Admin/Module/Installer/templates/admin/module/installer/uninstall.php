<?php

$helperDetails  = new View_Helper_Module_Details( $env );
$panelDetails   = $helperDetails->render( $module, $modules, $view );

/*  --  TODO: Options like ForeignFiles and Database  -- */
$keeForeignFiles	= $module->installType === Logic_Module::INSTALL_TYPE_LINK;
$dev				= !TRUE;
if( isset( $dev ) && $dev )
	die( print_m( $module ) );
/*  --  END OF DEV  --  */

$w			= (object) $words['uninstall'];

/*  --  BUTTONS  --  */
$attributes		= array( 'type' => 'button', 'class' => 'button cancel auto-back', 'readonly' => 'readonly', 'disabled' => 'disabled' );
$buttonBack		= UI_HTML_Tag::create( 'button', '<span>'.$w->buttonBack.'</span>', $attributes );
$buttonSubmit	= UI_HTML_Elements::Button( 'doUninstall', $w->buttonUninstall, 'button add' );

$labelDetails   = UI_HTML_Tag::create( 'span', 'Details' );
$buttonDetails  = UI_HTML_Tag::create( 'button', $labelDetails, array( 'class' => 'button info more', 'onclick' => "$('#panel-details').toggle()" ) );


/*  --  PANEL: INFO  --  */
$url	= './admin/module/viewer/index/';
$relationsNeeded    = '-';
$relationsSupported = '-';
if( $module->neededModules )
	$relationsNeeded    = $this->renderRelatedModulesList( $modules, $module->neededModules, $url, 'relations relations-needed' );
if( $module->supportedModules )
	$relationsSupported = $this->renderRelatedModulesList( $modules, $module->supportedModules, $url, 'relations relations-supported' );
$panelInfo  = '
<fieldset>
	<legend class="info">Informationen</legend>
	<dl>
		<dt>Title</dt>
		<dd>'.$module->title.'</dd>
		<dt>Quelle</dt>
		<dd>'.$module->source.'</dd>
		<dt>Version</dt>
		<dd>'.( $module->versionAvailable ? $module->versionAvailable : '?' ).'</dd>
		<dt>benötigte Module&nbsp;<a href="./admin/module/showRelationGraph/'.$moduleId.'/needs" class="layer-image" title="Abhängigkeiten von an$
		<dd>'.$relationsNeeded.'</dd>
		<dt>unterstützt Module&nbsp;<a href="./admin/module/showRelationGraph/'.$moduleId.'/supports" class="layer-image" title="Unterstützung an$
		<dd>'.$relationsSupported.'</dd>
	</dl>
	<div class="clearfix"></div>
</fieldset>';

/*  --  POSITION BAR --  */
function renderPositions( $positions ){
	$list	= array();
	foreach( $positions as $label => $url )
		$list[]	= '&laquo;&nbsp;'.UI_HTML_Tag::create( 'a', $label, array( 'href' => $url ) );
	$positions	= join( '&nbsp;&nbsp;|&nbsp;&nbsp;', $list );
	$positions	= UI_HTML_Tag::create( 'div', $positions, array( 'class' => 'nav-position', 'style' => 'margin-bottom: 0.8em') );
	return $positions;
}
$positions  = array(
	'Ansicht'	=> './admin/module/viewer/index/'.$module->id,
);

/*  --  OPTIONS  --  */
$options	= array();
if( $module->sql )
	$options[]	= '
				<label class="checkbox">
					<input type="checkbox" name="database" id="input_database" checked="checked">
					Datenbank-Kommandos ausführen
				</label>';
if( 1 || $keepForeignFiles ){
	$options[]	= '
				<label class="checkbox">
					<input type="checkbox" name="keepForeignLinkedFiles" id="input_keepForeignLinkedFiles" data-checked="checked" disabled="disabled">
					<strike>fremd gelinkte Dateien nicht entfernen</strike>
				</label>&nbsp;<small><em>(noch nicht implementiert)</em></small>';
}


if( $options ){
	$list	= array();
	foreach( $options as $option ){
		$list[]	= '<div>'.$option.'</div>';
	}
	$options	= '<h4>Optionen</h4>'.join( $list );
}
else
	$options	= '';

return '
<h3 class="position">
    <span>'.$words['uninstall']['heading'].'</span>
    <cite>'.$module->title.'</cite>
</h3>
'.renderPositions( $positions ).'
<div class="column-left-70">
    <form action="./admin/module/installer/uninstall/'.$moduleId.'" method="post">
        <fieldset>
            <legend class="module-add">Modul deinstallieren</legend>
			<big><b>Soll dieses Modul wirklich deinstalliert werden?</b></big><br/>
			<br/>
			Dabei werden ggfs. folgende Komponenten entgültig entfernt:<br/>
			<ul class="list">
				<li><abbr title="Klassen, Templates, Sprachdateien, HTML-Inhalte etc.">Dateien</abbr></li>
				<li><abbr title="Tabellen, Daten, Trigger, Stored Procedures & Functions">Datenbank-Inhalte</abbr></li>
				<li><abbr title="lokale Einstellungen, Datum der Installation">Konfigurationswerte</abbr></li>
			</ul>
			<div>
				'.$options.'
			</div>
			<div class="buttonbar">
				'.$buttonBack.'
				'.$buttonSubmit.'
				|
				'.$buttonDetails.'
			</div>
		</fieldset>
	</form>
</div>
<div class="column-right-30">
	'.$panelInfo.'
</div>
<div class="column-clear"></div>
<div id="panel-details" style="display: none">
	'.$panelDetails.'
</div>
';

