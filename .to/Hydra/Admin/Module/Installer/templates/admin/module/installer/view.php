<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['view'];

$graph		= "";

$needs		= $order;
unset( $needs[$moduleId] );

$isInstallable	= $mainModuleId || !count( $needs );

$attributes		= ['type' => 'button', 'class' => 'button cancel auto-back', 'readonly' => 'readonly', 'disabled' => 'disabled'];
$buttonBack		= HtmlTag::create( 'button', '<span>'.$w->buttonBack.'</span>', $attributes );
$buttonList		= HtmlElements::LinkButton( './admin/module', $w->buttonList, 'button cancel' );
$buttonView		= HtmlElements::LinkButton( './admin/module/viewer/index/'.$moduleId, $w->buttonView, 'button view' );
$buttonIndex	= HtmlElements::LinkButton( './admin/module/installer', $w->buttonIndex, 'button cancel' );
$buttonCancel	= HtmlElements::LinkButton( './admin/module/viewer/index/'.$module->id, $w->buttonView, 'button cancel' );
$buttonInstall	= HtmlElements::Button( 'doInstall', $w->buttonInstall, 'button add' );

if( $mainModuleId ){
	$buttonList		= '';
	$buttonCancel	= HtmlElements::LinkButton( './admin/module/installer/'.$mainModuleId, $w->buttonCancel, 'button cancel' );
	$needs	= [];
}

/*  --  RELATIONS  --  */
$url	= './admin/module/viewer/index/';

$relationsNeeded	= '-';
if( $module->neededModules )
	$relationsNeeded	= $this->renderRelatedModulesList( $modules, $module->neededModules, $url, 'relations relations-needed' );

$relationsSupported	= '-';
if( $module->supportedModules )
	$relationsSupported	= $this->renderRelatedModulesList( $modules, $module->supportedModules, $url, 'relations relations-supported' );


$listCritical	= [];
$neededModules	= '-';
$panelProgress	= '';

$graphNeeds		= "";
if( count( $needs ) ){
	$neededModules	= [];
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
		$link	= HtmlElements::Link( './admin/module/viewer/index/'.$id, $label );
		$label	= HtmlTag::create( 'span', $link, ['class' => $class] );
		$neededModules[]	= HtmlElements::ListItem( $label, 1 );
	}
	$neededModules	= HtmlElements::unorderedList( $neededModules, 1, ['class' => 'relations relations-needed'] );
	$buttonInstall	= HtmlElements::Button( 'doInstall', $w->buttonInstall, 'button add', NULL, TRUE );

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

/*  --  PANEL: PROGRESS  --  */
if( $mainModuleId ){
	$list	= [];
	foreach( $mainModule->neededModules as $id => $status ){
		if( $id == $moduleId || $id == $mainModule->id || !$status )
			continue;
		$label	= HtmlTag::create( 'span', $moduleMap[$id]->title, ['class' => 'icon module'] );
		$list[]	= HtmlElements::ListItem( $label, 1 );
	}
	$current	= HtmlTag::create( 'span', $module->title, ['class' => 'icon module disabled'] );
	$list[]		= HtmlElements::ListItem( $current, 1, ['class' => 'current'] );
	foreach( $mainModule->neededModules as $id => $status ){
		if( $id == $moduleId || $id == $mainModule->id || $status )
			continue;
		$label	= HtmlTag::create( 'span', $moduleMap[$id]->title, ['class' => 'icon module disabled'] );
		$list[]	= HtmlElements::ListItem( $label, 1 );
	}
	if( $module->id != $mainModule->id ){
		$main		= HtmlTag::create( 'span', $mainModule->title, ['class' => 'icon module disabled'] );
		$list[]		= HtmlElements::ListItem( $main, 1 );
	}

	$list	= HtmlElements::unorderedList( $list, 1, ['class' => 'relations relations-needed'] );
	$panelProgress	= '
<fieldset>
	<legend>Fortschritt</legend>
	'.$list.'
</fieldset>
';
}

/*  --  PANEL: INFO  --  */
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
		<dt>benötigte Module&nbsp;<a href="./admin/module/showRelationGraph/'.$moduleId.'/needs" class="layer-image" title="Abhängigkeiten von anderen Modulen"><img src="https://cdn.ceusmedia.de/img/famfamfam/silk/magnifier.png"/></a></dt>
		<dd>'.$relationsNeeded.'</dd>
		<dt>unterstützt Module&nbsp;<a href="./admin/module/showRelationGraph/'.$moduleId.'/supports" class="layer-image" title="Unterstützung anderer Module"><img src="https://cdn.ceusmedia.de/img/famfamfam/silk/magnifier.png"/></a></dt>
		<dd>'.$relationsSupported.'</dd>
	</dl>
	<div class="clearfix"></div>
</fieldset>
';
$helper	= new View_Helper_Module( $this->env );

/*  --  INSTALLATION  --  */
$tableConfig	= '';
if( $isInstallable ){
	if( count( $module->config ) ){
		$rows	= [];
		foreach( $module->config as $item ){
			$input	= View_Helper_Module::renderModuleConfigInput( $item, $words['boolean-values'] );
			$label	= View_Helper_Module::renderModuleConfigLabel( $module, $item );
			$id		= str_replace( '.', '_', $item->key );
			$cells	= array(
				HtmlTag::create( 'td', $label, [] ),
				HtmlTag::create( 'td', $words['config-types'][$item->type], ['class' => "cell-config-type"] ),
				HtmlTag::create( 'td', $input, ['class' => 'cell-config-value'] ),
			);
			$rows[$item->key]	= HtmlTag::create( 'tr', $cells, ['id' => "config_".$id] );
		#	natcasesort( $rows );
		}
		$tableHeads		= HtmlElements::TableHeads( ['Schlüssel', 'Typ', 'Wert'] );
		$tableColumns	= HtmlElements::ColumnGroup( ['25%', '10%', '65%'] );
		$tableConfig	= '<table>'.$tableColumns.$tableHeads.join( $rows ).'</table>';
		$tableConfig	= HtmlTag::create( 'h4', 'Konfiguration' ).$tableConfig.'<br/>';
	}

	$a	= '
		<h4>Installationstyp</h4>
		<div>
			<input type="radio" name="type" id="input_type_link" value="link" checked="checked"/>
			<label for="input_type_link"><acronym title="'.$w->textLink.'">'.$w->labelLink.'</acronym></label><br/>
			<input type="radio" name="type" id="input_type_copy" value="copy"/>
			<label for="input_type_copy"><acronym title="'.$w->textCopy.'">'.$w->labelCopy.'</acronym></label><br/>
		</div>';
	if( $module->sql ){
		$a	.= '
			<div>
			<label class="checkbox">
				<input type="checkbox" name="database" id="input_database" checked="checked">
				<acronym title="'.$w->textDatabase.'">'.$w->labelDatabase.'</acronym>
			</label>
		</div><br/>';
	}
}

/*  --  DATABASE SCRIPTS  --  */
$panelDatabase	= '';
if( $isInstallable && $module->sql ){
	$helper		= new View_Helper_Module_SqlScripts( $this->env );
	$table		= $helper->render( $sqlScripts );
	$panelDatabase	= '<fieldset><legend class="database">'.$w->database.'</legend>'.$table.'</fieldset>';
}

$panelFiles	= '';
if( $files ){
	$helper	= new View_Helper_Module_Files( $this->env );
	$table		= $helper->render( $files, $words, [
		'useCheckboxes'	=> !FALSE,
		'useActions'	=> !FALSE,
	] );
	$panelFiles	= '<fieldset><legend class="database">'.$w->files.'</legend>'.$table.'</fieldset>';
}

/*  --  POSITION BAR  --  */
function renderPositions( $positions ){
	$list	= [];
	foreach( $positions as $label => $url )
		$list[]	= '&laquo;&nbsp;'.HtmlTag::create( 'a', $label, ['href' => $url] );
	$positions	= join( '&nbsp;&nbsp;|&nbsp;&nbsp;', $list );
	$positions	= HtmlTag::create( 'div', $positions, array( 'class' => 'nav-position', 'style' => 'margin-bottom: 0.8em') );
	return $positions;
}

$positions	= [
#	'Liste'		=> './admin/module',
#	'Übersicht'	=> './admin/module/installer',
	'Ansicht'	=> './admin/module/viewer/index/'.$module->id
];

/*  --  HEADING  --  */
$urlForm	= './admin/module/installer/install/'.$module->id;
$headingVia	= '';
if( $mainModuleId ){
	$urlForm	.= '/'.$mainModuleId;
	$headingVia	= '&nbsp;<em><small>(via '.$mainModuleId.')</small></em>';
	$positions[$mainModuleId]	= './admin/module/installer/'.$mainModuleId;
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
		'.$panelFiles.'
		'.$panelDatabase.'
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
