<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var Environment $env */
/** @var array<Entity_Manual_Category> $categories */
/** @var Entity_Manual_Category $category */
/** @var int|string $categoryId */
/** @var Dictionary $moduleConfig */
/** @var array<string,array<string,string>> $words */
/** @var array<string> $folders */
/** @var array<string> $rights */

$helperCategory	= new View_Helper_Info_Manual_CategorySelector( $env );
$helperCategory->setCategories( $categories );
$helperCategory->setActiveCategoryId( $categoryId );

$helperNav	= new View_Helper_Info_Manual_CategoryPageList( $env );
$helperNav->setCategoryId( $categoryId );

$helperNav	= new View_Helper_Info_Manual_PageTree( $env );
$helperNav->setCategoryId( $categoryId );

$buttonAdd		= "";
$buttonReload	= "";
if( $moduleConfig->get( 'editor' ) ){
	$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'icon-plus icon-white'] );
	$iconReload		= HtmlTag::create( 'i', '', ['class' => 'icon-refresh'] );
	if( array_key_exists( 'add', $rights ) )
		$buttonAdd		= HtmlTag::create( 'a', $iconAdd.' '.$words['list']['buttonAdd'], ['href' => './info/manual/add', 'class' => "btn btn-small btn-primary"] );
	if( array_key_exists( 'edit', $rights ) )
		$buttonReload	= HtmlTag::create( 'a', $iconReload.' '.$words['list']['buttonReload'], ['href' => './info/manual/reload', 'class' => "btn btn-small"] );
}

return '
<div class="bs2-row-fluid bs4-row">
	<div class="bs2-span3 bs4-col-lg-3">
		<h3>'.$words['list']['heading'].'</h3>
		'.$helperCategory->render().'
		'.$helperNav->render().'
		'.$buttonAdd.'
		'.$buttonReload.'
	</div>
	<div class="bs2-span9 bs4-col-lg-9">
		'.print_m( $category, NULL, NULL, TRUE ).'
	</div>
</div>';
