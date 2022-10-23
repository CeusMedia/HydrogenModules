<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

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
	$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'icon-plus icon-white'] );
	$iconEdit		= HtmlTag::create( 'i', '', ['class' => 'icon-pencil'] );
	$iconReload		= HtmlTag::create( 'i', '', ['class' => 'icon-refresh'] );
	if( in_array( 'add', $rights ) )
		$buttonAdd		= HtmlTag::create( 'a', $iconAdd.' '.$words['list']['buttonAdd'], ['href' => './info/manual/add', 'class' => "btn btn-small btn-info"] );
	if( in_array( 'edit', $rights ) )
		$buttonEdit		= HtmlTag::create( 'a', $iconEdit.' '.$words['view']['buttonEdit'], ['href' => './info/manual/edit/'.$page->manualPageId.'-'.$view->urlencode( $page->title ), 'class' => "btn btn-small"] );
	if( in_array( 'reload', $rights ) )
		$buttonReload	= HtmlTag::create( 'a', $iconReload.' '.$words['list']['buttonReload'], ['href' => './info/manual/reload', 'class' => "btn btn-small"] );
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
//$contentContainer	= HtmlTag::create( 'div', htmlentities( $content, ENT_COMPAT, 'UTF-8' ), $attributes );
$contentContainer	= HtmlTag::create( 'div', $content, $attributes );

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
