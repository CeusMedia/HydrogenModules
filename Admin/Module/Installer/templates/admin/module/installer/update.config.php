<?php
$configKeys		= array_keys( $moduleSource->config ) + array_keys( $moduleLocal->config );
if( $hasUpdate && count( $configKeys ) ){
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
	return '
<fieldset>
	<legend>Konfiguration</legend>
	'.$tableConfig.'
</fieldset>';
}
return '';
?>