<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env */
/** @var array<Entity_Manual_Category> $categories */
/** @var int|string $categoryId */
/** @var Dictionary $moduleConfig */
/** @var array<string,array<string,string>> $words */
/** @var array<string> $rights */
/** @var array<string> $folders */

/*
$helperCategory	= new View_Helper_Info_Manual_CategorySelector( $env );
$helperCategory->setCategories( $categories );
$helperCategory->setActiveCategoryId( $categoryId );

$helperNav	= new View_Helper_Info_Manual_CategoryPageList( $env );
$helperNav->setCategoryId( $categoryId );
*/

$helperCategory	= new View_Helper_Info_Manual_CategorySelector( $env );
$helperCategory->setCategories( $categories );
$helperCategory->setActiveCategoryId( $categoryId );

$helperNav		= new View_Helper_Info_Manual_CategoryPageList( $env );
$helperNav->setCategoryId( $categoryId );
//$helperNav->setActivePageId( $pageId );

$helperNav		= new View_Helper_Info_Manual_PageTree( $env );
$helperNav->setCategoryId( $categoryId );
//$helperNav->setActivePageId( $pageId );


/*
$list	= '<div><em class="muted">'.$words['list']['empty'].'</em></div><br/>';
if( $files ){
	$list	= [];
	foreach( $order as $entry ){
		$entry	= preg_replace( "/\.md$/", "", $entry );
		$link	= HtmlTag::create( 'a', $entry, ['href' => './info/manual/view/'.$view->urlencode( $entry] ) );
		$list[]	= HtmlTag::create( 'li', $link );
	}
	$list	= HtmlTag::create( 'ul', $list, ['class' => 'nav nav-pills nav-stacked'] );
}
*/
$optParentId	= ['' => '- ohne -'];
foreach( $folders as $folder )
	$optParentId[$folder->manualPageId] = $folder->title;
$optParentId	= HtmlElements::Options( $optParentId );

$buttonAdd		= '';
$buttonReload	= '';
if( $moduleConfig->get( 'editor' ) ){
	$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'icon-plus icon-white'] );
	$iconReload		= HtmlTag::create( 'i', '', ['class' => 'icon-refresh'] );
	if( in_array( 'add', $rights ) )
		$buttonAdd		= HtmlTag::create( 'a', $iconAdd.' '.$words['list']['buttonAdd'], ['href' => './info/manual/add', 'class' => "btn btn-small btn-info"] );
	if( in_array( 'reload', $rights ) )
		$buttonReload	= HtmlTag::create( 'a', $iconReload.' '.$words['list']['buttonReload'], ['href' => './info/manual/reload', 'class' => "btn btn-small"] );
}

$optFormat	= $words['formats'];
$optFormat	= HtmlElements::Options( $optFormat, Model_Manual_Page::FORMAT_MARKDOWN );

return '
<div class="row-fluid">
	<div class="span3">
		<h3>'.$words['list']['heading'].'</h3>
		'.$helperCategory->render().'
		'.$helperNav->render().'
		'./*$list.*/'
		'.$buttonAdd.'
		'.$buttonReload.'
	</div>
	<div class="span9">
		<div class="content-panel">
			<h3>'.$words['add']['heading'].'</h3>
			<div class="content-panel-inner">
				<form action="./info/manual/add" method="post">
					<div class="row-fluid">
						<div class="span4">
							<label for="input_title">'.$words['add']['labelTitle'].'</label>
							<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $title, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
						</div>
						<div class="span2">
							<label for="input_format">'.$words['add']['labelFormat'].'</label>
							<select name="format" id="input_format" class="span12">'.$optFormat.'</select>
						</div>
                        <div class="span4">
                            <label for="input_parentId">'.$words['edit']['labelParent'].'</label>
                            <select name="parentId" id="input_parentId" class="span12">'.$optParentId.'</select>
                        </div>
<!--						<div class="span2">
							<label for="input_version">'.$words['add']['labelVersion'].'</label>
							<input type="number" min="1" name="version" id="input_version" class="span12" value="'.htmlentities( max( 1, $version ), ENT_QUOTES, 'UTF-8' ).'" required="required"/>
						</div>-->
<!--						<div class="span2">
							<label for="input_rank">'.$words['add']['labelRank'].'</label>
							<input type="number" min="1" name="rank" id="input_rank" class="span12" value="'.htmlentities( max( 1, $rank ), ENT_QUOTES, 'UTF-8' ).'" required="required"/>
						</div>-->
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_content">'.$words['add']['labelContent'].'</label>
							<textarea class="span12 CodeMirror-auto" name="content" id="input_content" rows="'.$moduleConfig->get( 'editor.rows' ).'">'.$content.'</textarea>
						</div>
					</div>
					<div class="buttonbar">
						<button type="submit" name="save" class="btn btn-small btn-success"><i class="icon-ok icon-white"></i> '.$words['add']['buttonSave'].'</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>';
