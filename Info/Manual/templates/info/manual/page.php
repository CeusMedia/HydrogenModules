<?php

$helperCategory	= new View_Helper_Info_Manual_CategorySelector( $env );
$helperCategory->setCategories( $categories );
$helperCategory->setActiveCategoryId( $categoryId );

/*$helperNav	= new View_Helper_Info_Manual_CategoryPageList( $env );
$helperNav->setCategoryId( $categoryId );
$helperNav->setActivePageId( $page->manualPageId );*/

$helperNav	= new View_Helper_Info_Manual_PageTree( $env );
$helperNav->setCategoryId( $page->manualCategoryId );
$helperNav->setActivePageId( $page->manualPageId );

$buttonAdd		= "";
$buttonEdit		= "";
$buttonReload	= "";
if( $moduleConfig->get( 'editor' ) ){
	$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
	$iconEdit		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-pencil' ) );
	$iconReload		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-refresh' ) );
	if( in_array( 'add', $rights ) )
		$buttonAdd		= UI_HTML_Tag::create( 'a', $iconAdd.' '.$words['list']['buttonAdd'], array( 'href' => './info/manual/add', 'class' => "btn btn-small btn-info" ) );
	if( in_array( 'edit', $rights ) )
		$buttonEdit		= UI_HTML_Tag::create( 'a', $iconEdit.' '.$words['view']['buttonEdit'], array( 'href' => './info/manual/edit/'.$page->manualPageId.'-'.$view->urlencode( $page->title ), 'class' => "btn btn-small" ) );
	if( in_array( 'reload', $rights ) )
		$buttonReload	= UI_HTML_Tag::create( 'a', $iconReload.' '.$words['list']['buttonReload'], array( 'href' => './info/manual/reload', 'class' => "btn btn-small" ) );
}

$attributes	= array(
	'id'	=> 'content-container'
);
switch( $renderer ){
	case 'server-ajax':
	case 'client':
		$attributes['style']	= 'display: none';
		$attributes['class']	= 'markdown';
		break;
	case 'server-inline':
	default:
		break;
}
//$contentContainer	= UI_HTML_Tag::create( 'div', htmlentities( $content, ENT_COMPAT, 'UTF-8' ), $attributes );
$contentContainer	= UI_HTML_Tag::create( 'div', $content, $attributes );

return '
<div class="bs2-row-fluid bs4-row">
	<div class="bs2-span3 bs4-col-lg-3">
<!--		<div class="content-panel">-->
			<h3>'.$words['list']['heading'].'</h3>
<!--			<div class="content-panel-inner">-->
				'.$helperCategory->render().'
				'.$helperNav->render().'
				<hr/>
				<div class="buttonbar">
					'.$buttonAdd.'
					'.$buttonReload.'
				</div>
<!--			</div>
		</div>-->
	</div>
	<div class="bs2-span9 bs4-col-lg-9" style="position: relative">
		<div class="content-panel">
			<h3><span class="muted">Dokument:</span> '.$page->title.'</h3>
			<div class="content-panel-inner">
				<div id="content-index">
					<div class="heading">Inhalt</div>
				</div>
				'.$contentContainer.'
				<div class="buttonbar">
					'.$buttonEdit.'
				</div>
			</div>
		</div>
		<br/>
	</div>
</div>';
