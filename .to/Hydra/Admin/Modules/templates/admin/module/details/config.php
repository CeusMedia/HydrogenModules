<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['tab-config'];

$count	= 0;
$table	= '';
if( $module->config ){
	$rows	= [];
	foreach( $module->config as $item ){
		$count++;
		if( $item->type == 'boolean' )
			$item->value	= $words['boolean-values'][( $item->value ? 'yes' : 'no' )];
		if( preg_match( "/password/", $item->key ) )
			$item->value	= '<em class="muted">hidden</em>';
//		else if( $item->protected === "yes" )
//			$item->value	= '<em class="muted">protected</em>';
		$label	= View_Helper_Module::renderModuleConfigLabel( $module, $item );
		$key		= HtmlTag::create( 'td', $label );
		$value		= HtmlTag::create( 'td', $item->value, ['class' => 'config-type-'.$item->type] );
		$rows[strtolower( $item->key )]	= '<tr>'.$key.$value.'</tr>';
	}
	ksort( $rows );
	$tbody		= HtmlTag::create( 'tbody', join( $rows ) );
	$heads		= HtmlElements::TableHeads( [$w->headKey, $w->headValue] );
	$thead		= HtmlTag::create( 'thead', $heads );
	$colgroup	= HtmlElements::ColumnGroup( '25%', '75%' );
	$table		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'striped'] );
}
return $table;
?>
