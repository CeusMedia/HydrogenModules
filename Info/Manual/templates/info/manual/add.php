<?php

/*
$helperCategory	= new View_Helper_Info_Manual_CategorySelector( $env );
$helperCategory->setCategories( $categories );
$helperCategory->setActiveCategoryId( $categoryId );

$helperNav	= new View_Helper_Info_Manual_CategoryPageList( $env );
$helperNav->setCategoryId( $categoryId );
*/

$helperCategory = new View_Helper_Info_Manual_CategorySelector( $env );
$helperCategory->setCategories( $categories );
$helperCategory->setActiveCategoryId( $categoryId );

$helperNav  = new View_Helper_Info_Manual_CategoryPageList( $env );
$helperNav->setCategoryId( $categoryId );
//$helperNav->setActivePageId( $pageId );

$helperNav  = new View_Helper_Info_Manual_PageTree( $env );
$helperNav->setCategoryId( $categoryId );
//$helperNav->setActivePageId( $pageId );


/*
$list	= '<div><em class="muted">'.$words['list']['empty'].'</em></div><br/>';
if( $files ){
	$list	= array();
	foreach( $order as $entry ){
		$entry	= preg_replace( "/\.md$/", "", $entry );
		$link	= UI_HTML_Tag::create( 'a', $entry, array( 'href' => './info/manual/view/'.$view->urlencode( $entry ) ) );
		$list[]	= UI_HTML_Tag::create( 'li', $link );
	}
	$list	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'nav nav-pills nav-stacked' ) );
}
*/
$optParentId    = array( '' => '- ohne -' );
foreach( $folders as $folder )
    $optParentId[$folder->manualPageId] = $folder->title;
$optParentId    = UI_HTML_Elements::Options( $optParentId );

$buttonAdd		= "";
$buttonReload	= "";
if( $moduleConfig->get( 'editor' ) ){
	$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
	$iconReload		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-refresh' ) );
	if( in_array( 'add', $rights ) )
		$buttonAdd		= UI_HTML_Tag::create( 'a', $iconAdd.' '.$words['list']['buttonAdd'], array( 'href' => './info/manual/add', 'class' => "btn btn-small btn-info" ) );
	if( in_array( 'reload', $rights ) )
		$buttonReload	= UI_HTML_Tag::create( 'a', $iconReload.' '.$words['list']['buttonReload'], array( 'href' => './info/manual/reload', 'class' => "btn btn-small" ) );
}

$optFormat	= $words['formats'];
$optFormat	= UI_HTML_Elements::Options( $optFormat, Model_Manual_Page::FORMAT_MARKDOWN );

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
?>
