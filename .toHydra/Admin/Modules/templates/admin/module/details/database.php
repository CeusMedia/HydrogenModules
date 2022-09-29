<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$count	= 0;
$list	= '-';
if( $module->sql ){
	$list	= [];
	foreach( $module->sql as $type => $sql ){
		if( !strlen( trim( $sql->sql ) ) )
			continue;
		$count++;
		$version	= $sql->event === 'update' ? '<br/>Version: '.$sql->version : '';
		$label		= ucFirst( $sql->event ).$version.'<br/>DBMS: '.$sql->type;
		$list[]		= HtmlTag::create( 'dt', $label );
		$list[]		= HtmlTag::create( 'dd', HtmlTag::create( 'xmp', trim( $sql->sql ) ) );
	}
	$list	= HtmlTag::create( 'dl', join( $list ), ['class' => 'database'] );
}
return $list.'<div class="clearfix"></div>';
?>
