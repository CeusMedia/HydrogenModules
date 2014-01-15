<?php
$w	= (object) $words['view'];

$needs	= $order;
unset( $needs[$moduleId] );

$isInstallable	= $mainModuleId || !count( $needs );
$graph			= "";

$attributes		= array( 'type' => 'button', 'class' => 'button cancel auto-back', 'readonly' => 'readonly', 'disabled' => 'disabled' );
$buttonBack		= UI_HTML_Tag::create( 'button', '<span>'.$w->buttonBack.'</span>', $attributes );
$buttonList		= UI_HTML_Elements::LinkButton( './admin/module', $w->buttonList, 'button cancel' );
$buttonView		= UI_HTML_Elements::LinkButton( './admin/module/viewer/index/'.$moduleId, $w->buttonView, 'button view' );
$buttonIndex	= UI_HTML_Elements::LinkButton( './admin/module/installer', $w->buttonIndex, 'button cancel' );
$buttonCancel	= UI_HTML_Elements::LinkButton( './admin/module/viewer/index/'.$module->id, $w->buttonView, 'button cancel' );
$buttonInstall	= UI_HTML_Elements::Button( 'doInstall', $w->buttonInstall, 'button add' );

if( $mainModuleId ){
	$buttonList		= '';
	$buttonCancel	= UI_HTML_Elements::LinkButton( './admin/module/installer/'.$mainModuleId, $w->buttonCancel, 'button cancel' );
	$needs	= array();
}

$url	= './admin/module/viewer/index/';

$relationsNeeded	= '-';
if( $module->neededModules )
	$relationsNeeded	= $this->renderRelatedModulesList( $modules, $module->neededModules, $url, 'relations relations-needed' );

$relationsSupported	= '-';
if( $module->supportedModules )
	$relationsSupported	= $this->renderRelatedModulesList( $modules, $module->supportedModules, $url, 'relations relations-supported' );


$listCritical	= array();
$neededModules	= '-';
$panelProgress	= '';

$graphNeeds		= "";
if( count( $needs ) ){
	$neededModules	= array();
	foreach( array_keys( $needs ) as $id ){
		$label	= $id;
		$status	= 0;
		if( array_key_exists( $id, $moduleMap ) ){
			$label	= $moduleMap[$id]->title;
			$status	= $moduleMap[$id]->type;
		}
		else
			$listCritical[]	= $id;
		$class	= 'icon module module-status-'.$status;
		$link	= UI_HTML_Elements::Link( './admin/module/viewer/index/'.$id, $label );
		$label	= UI_HTML_Tag::create( 'span', $link, array( 'class' => $class ) );
		$neededModules[]	= UI_HTML_Elements::ListItem( $label, 1 );
	}
	$neededModules	= UI_HTML_Elements::unorderedList( $neededModules, 1, array( 'class' => 'relations relations-needed' ) );
	$buttonInstall	= UI_HTML_Elements::Button( 'doInstall', $w->buttonInstall, 'button add', NULL, TRUE );
	
	$a		= 'Es müssen erst folgende Module installiert werden:'.$neededModules.'<div class="column-clear"></div>';
	if( $listCritical ){
		$a		.= '<br/><b>Die Installation kann nicht fortgesetzt werden, da mindestens ein benötigtes Modul nicht vorhanden ist.</b><br/>';
		$a		.= 'Möglicherweise befindet sich dieses Modul in einer Modulquelle, die Sie noch nicht hinzugefügt haben.<br/>';
	}
	else{
		$a		.= '<label><input type="checkbox" name="force" value="1" onchange="AdminModuleInstaller.toggleSubmitButton()"/>&nbsp;Alle benötigten Module der Reihe nach installieren.</label>';
	}
	

	$graph		= '<img src="./admin/module/showRelationGraph/'.$moduleId.'/needs" style="max-width: 100%"/>';
	$graphNeeds	= '<fieldset><legend>Abhängigkeiten</legend>'.$graph.'</fieldset>';
}

$graphSupports	= '';
if( $module->supportedModules ){
	$graph	= '<img src="./admin/module/showRelationGraph/'.$moduleId.'/supports" style="max-width: 100%"/>';
	$graphSupports	= '<fieldset><legend>Unterstützung</legend>'.$graph.'</fieldset>';
}

if( $mainModuleId ){
	$list	= array();
	foreach( $mainModule->neededModules as $id => $status ){
		if( $id == $moduleId || $id == $mainModule->id || !$status )
			continue;
		$label	= UI_HTML_Tag::create( 'span', $moduleMap[$id]->title, array( 'class' => 'icon module' ) );
		$list[]	= UI_HTML_Elements::ListItem( $label, 1 );
	}
	$current	= UI_HTML_Tag::create( 'span', $module->title, array( 'class' => 'icon module disabled' ) );
	$list[]		= UI_HTML_Elements::ListItem( $current, 1, array( 'class' => 'current' ) );
	foreach( $mainModule->neededModules as $id => $status ){
		if( $id == $moduleId || $id == $mainModule->id || $status )
			continue;
		$label	= UI_HTML_Tag::create( 'span', $moduleMap[$id]->title, array( 'class' => 'icon module disabled' ) );
		$list[]	= UI_HTML_Elements::ListItem( $label, 1 );
	}
	if( $module->id != $mainModule->id ){
		$main		= UI_HTML_Tag::create( 'span', $mainModule->title, array( 'class' => 'icon module disabled' ) );
		$list[]		= UI_HTML_Elements::ListItem( $main, 1 );
	}
	
	$list	= UI_HTML_Elements::unorderedList( $list, 1, array( 'class' => 'relations relations-needed' ) );
	$panelProgress	= '
<fieldset>
	<legend>Fortschritt</legend>
	'.$list.'
</fieldset>
';
}

$panelInfo	= '
<fieldset>
	<legend class="info">Informationen</legend>
	<dl>
		<dt>Title</dt>
		<dd>'.$module->title.'</dd>
		<dt>Quelle</dt>
		<dd>'.$module->source.'</dd>
		<dt>Version</dt>
		<dd>'.( $module->versionAvailable ? $module->versionAvailable : '?' ).'</dd>
		<dt>benötigte Module&nbsp;<a href="./admin/module/showRelationGraph/'.$moduleId.'/needs" class="layer-image" title="Abhängigkeiten von anderen Modulen"><img src="http://img.int1a.net/famfamfam/silk/magnifier.png"/></a></dt>
		<dd>'.$relationsNeeded.'</dd>
		<dt>unterstützt Module&nbsp;<a href="./admin/module/showRelationGraph/'.$moduleId.'/supports" class="layer-image" title="Unterstützung anderer Module"><img src="http://img.int1a.net/famfamfam/silk/magnifier.png"/></a></dt>
		<dd>'.$relationsSupported.'</dd>
	</dl>
	<div class="clearfix"></div>
</fieldset>
';
$helper	= new View_Helper_Module( $this->env );
$tableConfig	= '';
if( $isInstallable ){
	if( count( $module->config ) ){
		$rows	= array();
		foreach( $module->config as $item ){
			$input	= View_Helper_Module::renderModuleConfigInput( $item, $words['boolean-values'] );
			$label	= View_Helper_Module::renderModuleConfigLabel( $module, $item );
			$id		= str_replace( '.', '_', $item->key );
			$cells	= array(
				UI_HTML_Tag::create( 'td', $label, array() ),
				UI_HTML_Tag::create( 'td', $words['config-types'][$item->type], array( 'class' => "cell-config-type" ) ),
				UI_HTML_Tag::create( 'td', $input, array( 'class' => 'cell-config-value' ) ),
			);
			$rows[$item->key]	= UI_HTML_Tag::create( 'tr', $cells, array( 'id' => "config_".$id ) );
		#	natcasesort( $rows );
		}
		$tableHeads		= UI_HTML_Elements::TableHeads( array( 'Schlüssel', 'Typ', 'Wert' ) );
		$tableColumns	= UI_HTML_Elements::ColumnGroup( array( '25%', '10%', '65%' ) );
		$tableConfig	= '<table>'.$tableColumns.$tableHeads.join( $rows ).'</table>';
		$tableConfig	= UI_HTML_Tag::create( 'h4', 'Konfiguration' ).$tableConfig.'<br/>';
	}
	
	$a	= '
		<h4>Installationstyp</h4>
		<div>
			<input type="radio" name="type" id="input_type_link" value="link" checked="checked"/>
			<label for="input_type_link"><acronym title="'.$w->textLink.'">'.$w->labelLink.'</acronym></label><br/>
			<input type="radio" name="type" id="input_type_copy" value="copy"/>
			<label for="input_type_copy"><acronym title="'.$w->textCopy.'">'.$w->labelCopy.'</acronym></label><br/>
		</div><br/>
		';

}

$positions	= array(
#	'Liste'		=> './admin/module',
#	'Übersicht'	=> './admin/module/installer',
	'Ansicht'	=> './admin/module/viewer/index/'.$module->id
);

$urlForm	= './admin/module/installer/install/'.$module->id;
$headingVia	= '';
if( $mainModuleId ){
	$urlForm	.= '/'.$mainModuleId;
	$headingVia	= '&nbsp;<em><small>(via '.$mainModuleId.')</small></em>';
	$positions[$mainModuleId]	= './admin/module/installer/'.$mainModuleId;
}

function renderPositions( $positions ){
	$list	= array();
	foreach( $positions as $label => $url )
		$list[]	= '&laquo;&nbsp;'.UI_HTML_Tag::create( 'a', $label, array( 'href' => $url ) );
	$positions	= join( '&nbsp;&nbsp;|&nbsp;&nbsp;', $list );
	$positions	= UI_HTML_Tag::create( 'div', $positions, array( 'class' => 'nav-position', 'style' => 'margin-bottom: 0.8em') );
	return $positions;
}

return '
<h3 class="position">
	<span>'.$words['view']['heading'].'</span>
	<cite>'.$module->title.'</cite>'.$headingVia.'
</h3>
'.renderPositions( $positions ).'
<div class="column-left-70">
	<form action="'.$urlForm.'" method="post">
		<fieldset>
			<legend class="module-add">Modul installieren</legend>
			'.$tableConfig.'
			'.$a.'

			<div class="buttonbar">
				'.$buttonBack.'
<!--				'.$buttonView.'
				'.$buttonList.'
				'.$buttonIndex.'
				'.$buttonCancel.'-->
				'.$buttonInstall.'
			</div>
		</fieldset>
	</form>
</div>
<div class="column-right-30">
	'.$panelProgress.'
	'.$panelInfo.'
</div>
<div class="column-clear"></div>
<div>'.$graphNeeds.'</div>
<div>'.$graphSupports.'</div>
';

?>
