<?php

$helperCategory	= new View_Helper_Info_Manual_CategorySelector( $env );
$helperCategory->setCategories( $categories );
$helperCategory->setActiveCategoryId( $categoryId );

$helperNav	= new View_Helper_Info_Manual_CategoryPageList( $env );
$helperNav->setCategoryId( $categoryId );
$helperNav->setActivePageId( $page->manualPageId );

$helperNav	= new View_Helper_Info_Manual_PageTree( $env );
$helperNav->setCategoryId( $categoryId );
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
		<h3>'.$words['list']['heading'].'</h3>
		'.$helperCategory->render().'
		'.$helperNav->render().'
		'.$buttonAdd.'
		'.$buttonReload.'
	</div>
	<div class="bs2-span9 bs4-col-lg-9" style="position: relative">
		<div id="content-index">
			<div class="heading">Inhalt</div>
		</div>
		'.$contentContainer.'
		<div class="buttonbar">
			'.$buttonEdit.'
		</div>
		<br/>
	</div>
</div>
<script>
$(document).ready(function(){
	InfoManual.renderer = "'.$renderer.'";
	InfoManual.init("#content-container", "#content-index");
});
</script>
<style>
#content-index {
	display: none;
	float: right;
	width: 18em;
	min-height: 50px;
	max-height: 70%;
	margin: 10px 0px 10px 10px;
	padding: 0.3em 0.5em;
	background-color: rgb(255, 255, 255);
	border-width: 1px 1px 1px 1px;
	border-style: solid;
	border-color: gray;
	box-shadow: 0px 0px 2px 2px rgba(0,0,0,0.10);
	overflow-x: hidden;
	overflow-y: auto;
	}
#content-index .heading {
	font-size: 1.2em;
	padding: 0.5em 0.5em 0.25em 0.5em;
	border-bottom: 1px solid #DDD;
	margin-bottom: 0.5em;
	background-color: white;
	}
#content-index ul {
	margin: 0;
	padding: 0;
	list-style: none;
	}
#content-index li {
	margin: 0;
	padding: 0;
	}
#content-index li a {
	display: block;
	text-overflow: ellipsis;
	white-space: nowrap;
	overflow: hidden;
	line-height: 1.5em;
	}

/*  --  RENDER STYLE: LIST  --  */
#content-index ul.index-list li {
	line-height: 1.3em;
	}
#content-index ul.index-list li.level-1 {
	margin-left: 0em;
	}
#content-index ul.index-list li.level-1 a {
	font-size: 1.4em;
	color: #228;
	}
#content-index ul.index-list li.level-2 {
	margin-left: 0.75em;
	}
#content-index ul.index-list li.level-2 a {
	font-size: 1.2em;
	color: #339;
	}
#content-index ul.index-list li.level-3 {
	margin-left: 1.5em;
	}
#content-index ul.index-list li.level-3 a {
	font-size: 1.1em;
	color: #44A;
	}
#content-index ul.index-list li.level-4 {
	margin-left: 2.25em;
	}
#content-index ul.index-list li.level-4 a {
	font-size: 1em;
	color: #55B;
	}
#content-index ul.index-list li.level-5 {
	margin-left: 3em;
	}
#content-index ul.index-list li.level-5 a {
	font-size: 0.9em;
	color: #66C;
	}
</style>
';
?>
