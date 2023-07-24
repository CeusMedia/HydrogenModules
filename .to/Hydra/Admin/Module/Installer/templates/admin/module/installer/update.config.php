<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$configKeys		= array_keys( $moduleSource->config ) + array_keys( $moduleLocal->config );
if( $hasUpdate && count( $configKeys ) ){
	$rows	= [];
	foreach( $configKeys as $key ){
		$item	= (object) [];
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
		$buttonCopy	= HtmlTag::create( 'button', '<img src="//cdn.ceusmedia.de/img/famfamfam/silk/arrow_down.png"/>', ['type' => 'button', 'class' => "button tiny copy"] );
		$buttonInit	= HtmlTag::create( 'button', '<img src="//cdn.ceusmedia.de/img/famfamfam/silk/arrow_refresh.png"/>', ['type' => 'button', 'class' => "button tiny reset"] );
		$inputOld	= View_Helper_Module::renderModuleConfigInput( $itemOld, $words['boolean-values'], TRUE );
		$inputNew	= View_Helper_Module::renderModuleConfigInput( $itemNew, $words['boolean-values'] );
		$input		= $inputNew;
		if( $itemOld && $itemNew )
			$input	= $inputOld.'<br/>'.$inputNew.$buttonCopy.$buttonInit;
		else if( $itemOld )
			$input	= $inputOld;

		$name	= 'config['.$item->key.']';
		$class	= ( $item->mandatory && $item->mandatory === "yes" ) ? " mandatory" : "";
		$label	= $itemNew->title ? HtmlTag::create( 'abbr', $key, ['title' => $itemNew->title] ) : $key;
		$label	= HtmlTag::create( 'label', $label, ['class' => $class, 'for' => 'input_'.$name] );
		$id		= str_replace( '.', '_', $key );
		$cells	= array(
			HtmlTag::create( 'td', $label, [] ),
			HtmlTag::create( 'td', $words['config-types'][$item->type], ['class' => "cell-config-type"] ),
			HtmlTag::create( 'td', $words['config-update-status'][$status], [] ),
			HtmlTag::create( 'td', $input, ['class' => 'cell-config-value'] ),
		);
		$rows[$key]	= HtmlTag::create( 'tr', $cells, ['id' => "config_".$id] );
	#	natcasesort( $rows );
	}
	$tableHeads		= HtmlElements::TableHeads( ['Schlüssel', 'Typ', 'Änderung', 'Wert'] );
	$tableColumns	= HtmlElements::ColumnGroup( ['25%', '10%', '15%', '50%'] );
	$tableConfig	= '<table>'.$tableColumns.$tableHeads.join( $rows ).'</table>';
	$tableConfig	= HtmlTag::create( 'h4', 'Konfiguration' ).$tableConfig.'<br/>';
	return '
<fieldset>
	<legend>Konfiguration</legend>
	'.$tableConfig.'
</fieldset>';
}
return '';
?>
