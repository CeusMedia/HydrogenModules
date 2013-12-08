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
$buttonUpdate	= UI_HTML_Elements::Button( 'doInstall', $w->buttonUpdate, 'button update' );

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

$tableConfig	= '';
if( $isInstallable ){
	if( count( $moduleSource->config ) ){
		$rows	= array();
		foreach( $moduleSource->config as $key => $value ){
			$class	= "";
			if( $value->mandatory ){
				if( $value->mandatory == "yes" )
					$class = " mandatory";
				else if( preg_match( "/^.+:.*$/", $value->mandatory ) ){
					list( $relatedKey, $relatedValue )	= explode( ':', $value->mandatory );
					$relatedValue	= explode( ',', $relatedValue );
					if( isset( $moduleSource->config[$relatedKey] ) ){
						if( in_array( $moduleSource->config[$relatedKey]->value, $relatedValue ) )
							$class = " mandatory";
					}
				}
			}
			$name	= 'config['.$key.']';
			switch( $value->type ){
				case 'boolean':
					$strValue	= $value->value === TRUE ? 'yes' : 'no';
					$options	= UI_HTML_Elements::Options( $words['boolean-values'], $strValue );
					$input		= UI_HTML_Tag::create( 'select', $options, array( 'class' => 's'.$class.' active-'.$strValue, 'name' => $name, 'id' => 'input_'.$name ) );
					break;
				case 'int':
				case 'integer':
					$input		= UI_HTML_Elements::Input( 'config['.$key.']', $value->value, 's'.$class );
					break;
				default:
					if( count( $value->values ) ){
						$options	= array_combine( $value->values, $value->values );
						$options	= UI_HTML_Elements::Options( $options, $value->value );
						$input		= UI_HTML_Elements::Select( 'config['.$key.']', $options, 'm'.$class );
					}
					else
						$input	= UI_HTML_Elements::Input( 'config['.$key.']', $value->value, 'max'.$class );
					break;
			}
			$label	= UI_HTML_Tag::create( 'label', $key, array( 'class' => $class, 'for' => 'input_'.$name ) );
			$id		= str_replace( '.', '_', $key );
			$cells	= array(
				UI_HTML_Tag::create( 'td', $label, array() ),
				UI_HTML_Tag::create( 'td', $words['config-types'][$value->type], array( 'class' => "cell-config-type" ) ),
				UI_HTML_Tag::create( 'td', $input, array( 'class' => 'cell-config-value' ) ),
			);
			$rows[$key]	= UI_HTML_Tag::create( 'tr', $cells, array( 'id' => "config_".$id ) );
		#	natcasesort( $rows );
		}
		$tableHeads		= UI_HTML_Elements::TableHeads( array( 'Schlüssel', 'Typ', 'Wert' ) );
		$tableColumns	= UI_HTML_Elements::ColumnGroup( array( '25%', '10%', '65%' ) );
		$tableConfig	= '<table>'.$tableColumns.$tableHeads.join( $rows ).'</table>';
		$tableConfig	= UI_HTML_Tag::create( 'h4', 'Konfiguration' ).$tableConfig.'<br/>';
	}
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

$urlForm	= './admin/module/installer/install/'.$moduleLocal->id;

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
<div class="column-clear"></div>';
?>
