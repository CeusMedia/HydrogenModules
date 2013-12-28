<?php

$list	= array();
foreach( $files as $file ){
	$actions	= array();
	$checkbox	= UI_HTML_Tag::create( 'input', NULL, array( 'type' => 'checkbox', 'value' => $file->file ) );
	if( $file->status === 2 )
		$checkbox;
	if( $file->status === 4 ){
		$url		= './admin/module/installer/diff/'.base64_encode( $file->pathLocal ).'/'.base64_encode( $file->pathSource );
		$actions[]	= UI_HTML_Tag::create( 'a', 'diff', array( 'href' => $url, 'class' => 'layer-html' ) );
	}
	$cells	= array(
		UI_HTML_Tag::create( 'td', $checkbox, array( 'class' => 'cell-check' ) ),
		UI_HTML_Tag::create( 'td', $file->typeKey, array( 'class' => 'cell-type' ) ),
		UI_HTML_Tag::create( 'td', $file->name, array( 'class' => 'cell-name' ) ),
		UI_HTML_Tag::create( 'td', join( " ", $actions ), array( 'class' => 'cell-actions' ) ),
	);
	$states	= array(
		0	=> 'new',
		1	=> 'installed',
		2	=> 'linked',
		3	=> 'foreign',
		4	=> 'changed',
	);
	$status	= $states[$file->status];
	$list[]	= UI_HTML_Tag::create( 'tr', $cells, array(
		'class'	=> 'status-'.$status,
		'data-file-source'	=> $file->pathSource,
		'data-file-local'	=> $file->pathLocal
	) );
}
$colgroup	= UI_HTML_Elements::ColumnGroup( "3%", "7%", "70%", "20%" );
$thead		= UI_HTML_Tag::create( 'thead' );
$tbody		= UI_HTML_Tag::create( 'tbody', $list );
$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table' ) );

unset( $moduleLocal->icon );
unset( $moduleLocal->sql );

unset( $moduleSource->icon );
unset( $moduleSource->sql );

$panelFiles	= '
	<style>
dl>dt{
	clear: left;
	float: left;
	width: 120px;
	}
dl>dd{
	float: left;
	}
tr.status-new {background-color: #DFFFDF}
tr.status-installed {background-color: #FFFFDF}
tr.status-linked {background-color: #EFEFEF; opacity: 0.75}
tr.status-foreign {background-color: #DFDFFF; opacity: 0.75}
tr.status-changed {background-color: #FFDFDF}
	</style>
	<h4>Dateien</h4>
	'.$table.'
	<div>
		<div style="float:left;width:50%;">
			<h3>LOKAL</h3>
			<b>Module</b>
			<div style="height: 300px; overflow: auto;">
				'.print_m( $moduleLocal, NULL, NULL, TRUE ).'
			</div>
		</div>
		<div style="float:left;width:50%;">
			<h3>SOURCE</h3>
			<b>Module</b>
			<div style="height: 300px; overflow: auto;">
				'.print_m( $moduleSource, NULL, NULL, TRUE ).'
			</div>
		</div>
	</div>';


$w	= (object) $words['update'];

$isInstallable	= $hasUpdate;

/*$modA	= $modulesAvailable[$moduleId];
$modB	= $modulesInstalled[$moduleId];
print_m( $modA->config );
print_m( $modA->files );
print_m( $modB->config );
print_m( $modB->files );
die;
*/

$attributes		= array( 'type' => 'button', 'class' => 'button cancel auto-back', 'readonly' => 'readonly', 'disabled' => 'disabled' );
$buttonBack		= UI_HTML_Tag::create( 'button', '<span>'.$w->buttonBack.'</span>', $attributes );
$buttonUpdate	= UI_HTML_Elements::Button( 'doUpdate', $w->buttonUpdate, 'button update' );

$panelInfo	= '
<fieldset>
	<legend class="info">Informationen</legend>
	<dl>
		<dt>Modul</dt>
		<dd>'.$moduleLocal->title.'</dd>
		<dt>Quelle</dt>
		<dd>'.$moduleLocal->source.'</dd>
		<dt>Ausgangsversion</dt>
		<dd>'.( $moduleLocal->versionInstalled? $moduleLocal->versionInstalled : '?' ).'</dd>
		<dt>Zielversion </dt>
		<dd>'.( $moduleLocal->versionAvailable ? $moduleLocal->versionAvailable : '?' ).'</dd>
	</dl>
	<div class="clearfix"></div>
</fieldset>
';

$configKeys	= array_keys( $moduleSource->config ) + array_keys( $moduleLocal->config );

$tableConfig	= '';
if( $isInstallable && count( $configKeys ) ){
	$rows	= array();
	foreach( $configKeys as $key ){
		$item	= (object) array();
		$itemNew	= isset( $moduleSource->config[$key] ) ? $moduleSource->config[$key] : NULL;
		$itemOld	= isset( $moduleLocal->config[$key] ) ? $moduleLocal->config[$key] : NULL;
		$item		= $itemNew ? $itemNew : $itemOld;
		$status		= 5;
		if( !$itemOld )
			$status		= 4;
		else{
			if( $itemNew ){
				$status		= 0;
				if( $itemOld->type !== $itemNew->type )
					$status		= 3;
				else if( $itemOld->values !== $itemNew->values )
					$status		= 2;
				else if( $itemOld->value !== $itemNew->value )
					$status		= 1;
			}
		}
		$buttonCopy	= UI_HTML_Tag::create( 'button', '<img src="//cdn.int1a.net/img/famfamfam/silk/arrow_down.png"/>', array( 'type' => 'button', 'class' => "button tiny copy" ) );
		$buttonInit	= UI_HTML_Tag::create( 'button', '<img src="//cdn.int1a.net/img/famfamfam/silk/arrow_refresh.png"/>', array( 'type' => 'button', 'class' => "button tiny reset" ) );
		$inputOld	= View_Helper_Module::renderModuleConfigInput( $itemOld, $words['boolean-values'], TRUE );
		$inputNew	= View_Helper_Module::renderModuleConfigInput( $itemNew, $words['boolean-values'] );
		$input		= $inputNew;
		if( $itemOld && $itemNew )
			$input	= $inputOld.'<br/>'.$inputNew.$buttonCopy.$buttonInit;
		else if( $itemOld )
			$input	= $inputOld;

		$name	= 'config['.$item->key.']';
		$class	= ( $item->mandatory && $item->mandatory === "yes" ) ? " mandatory" : "";
		$label	= UI_HTML_Tag::create( 'label', $key, array( 'class' => $class, 'for' => 'input_'.$name ) );
		$id		= str_replace( '.', '_', $key );
		$cells	= array(
			UI_HTML_Tag::create( 'td', $label, array() ),
			UI_HTML_Tag::create( 'td', $words['config-types'][$item->type], array( 'class' => "cell-config-type" ) ),
			UI_HTML_Tag::create( 'td', $words['config-update-status'][$status], array() ),
			UI_HTML_Tag::create( 'td', $input, array( 'class' => 'cell-config-value' ) ),
		);
		$rows[$key]	= UI_HTML_Tag::create( 'tr', $cells, array( 'id' => "config_".$id ) );
	#	natcasesort( $rows );
	}
	$tableHeads		= UI_HTML_Elements::TableHeads( array( 'Schlüssel', 'Typ', 'Änderung', 'Wert' ) );
	$tableColumns	= UI_HTML_Elements::ColumnGroup( array( '25%', '10%', '15%', '50%' ) );
	$tableConfig	= '<table>'.$tableColumns.$tableHeads.join( $rows ).'</table>';
	$tableConfig	= UI_HTML_Tag::create( 'h4', 'Konfiguration' ).$tableConfig.'<br/>';
}
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
<div class="column-left-70">
	<form action="'.$urlForm.'" method="post">
		<fieldset>
			<legend class="module-add">Modul aktualisieren</legend>
			'.$tableConfig.'
			'.$panelType.'
			'.$panelFiles.'
			<div class="buttonbar">
				'.$buttonBack.'
				'.$buttonUpdate.'
			</div>
		</fieldset>
	</form>
</div>
<div class="column-right-30">
	'.$panelInfo.'
</div>
<script>
$(document).ready(function(){
	Updater.init();
});
</script>
<div class="column-clear"></div>';
?>
