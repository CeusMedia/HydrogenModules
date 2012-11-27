<?php
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
		<dt>Title</dt>
		<dd>'.$module->title.'</dd>
		<dt>Quelle</dt>
		<dd>'.$module->source.'</dd>
		<dt>Ausgangsversion</dt>
		<dd>'.( $module->versionInstalled? $module->versionInstalled : '?' ).'</dd>
		<dt>Zielversion </dt>
		<dd>'.( $module->versionAvailable ? $module->versionAvailable : '?' ).'</dd>
	</dl>
	<div class="clearfix"></div>
</fieldset>
';

$tableConfig	= '';
if( $isInstallable ){
	if( count( $module->config ) ){
		$rows	= array();
		foreach( $module->config as $key => $value ){
			
			$class	= "";
			if( $value->mandatory ){
				if( $value->mandatory == "yes" )
					$class = " mandatory";
				else if( preg_match( "/^.+:.*$/", $value->mandatory ) ){
					list( $relatedKey, $relatedValue )	= explode( ':', $value->mandatory );
					$relatedValue	= explode( ',', $relatedValue );
					if( isset( $module->config[$relatedKey] ) ){
						if( in_array( $module->config[$relatedKey]->value, $relatedValue ) )
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

$urlForm	= './admin/module/installer/install/'.$module->id;

return '
<h3 class="position">
	<span>'.$words['view']['heading'].'</span>
	<cite>'.$module->title.'</cite>
</h3>
<div class="column-left-70">
	<form action="'.$urlForm.'" method="post">
		<fieldset>
			<legend class="module-add">Modul installieren</legend>
			'.$tableConfig.'
			'.$a.'

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