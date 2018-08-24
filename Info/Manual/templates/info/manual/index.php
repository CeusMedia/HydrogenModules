<?php

$helperCategory	= new View_Helper_Info_Manual_CategorySelector( $env );
$helperCategory->setCategories( $categories );
$helperCategory->setActiveCategoryId( $categoryId );

$helperNav	= new View_Helper_Info_Manual_CategoryPageList( $env );
$helperNav->setCategoryId( $categoryId );

$helperNav	= new View_Helper_Info_Manual_PageTree( $env );
$helperNav->setCategoryId( $categoryId );


$buttonAdd	= "";
$buttonReload	= "";
if( $moduleConfig->get( 'editor' ) ){
	$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
	$iconReload		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-refresh' ) );
	if( array_key_exists( 'add', $rights ) )
		$buttonAdd		= UI_HTML_Tag::create( 'a', $iconAdd.' '.$words['list']['buttonAdd'], array( 'href' => './info/manual/add', 'class' => "btn btn-small btn-primary" ) );
	if( array_key_exists( 'edit', $rights ) )
		$buttonReload	= UI_HTML_Tag::create( 'a', $iconReload.' '.$words['list']['buttonReload'], array( 'href' => './info/manual/reload', 'class' => "btn btn-small" ) );
}

return '
<div class="row-fluid">
	<div class="span3 bs4-col-lg-3">
		<h3>'.$words['list']['heading'].'</h3>
		'.$helperCategory->render().'
		'.$helperNav->render().'
		'.$buttonAdd.'
		'.$buttonReload.'
	</div>
</div>';
?>
