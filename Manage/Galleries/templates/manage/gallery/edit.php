<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$galleries	= $this->renderList( $gallery->galleryId );

$optStatus	= $words['states'];
$optStatus	= HtmlElements::Options( $optStatus, $gallery->status );

$tabs	= array(
	$words['tabs'][1],
	$words['tabs'][2],
	$words['tabs'][3],
);

$currentTab		= $this->env->getSession()->get( 'module.manage_galleries.tab' );
$listTabs		= [];
foreach( $tabs as $nr => $tab ){
	$attributes	= array( 'href' => '#tab'.++$nr, 'data-toggle' => 'tab' );
	$link		= HtmlTag::create( 'a', $tab, $attributes );
	$attributes	= array( 'id' => 'gallery-editor-tab-'.$nr, 'class' => $nr == $currentTab ? "active" : NULL );
	$listTabs[]	= HtmlTag::create( 'li', $link, $attributes );
}
$listTabs	= HtmlTag::create( 'ul', $listTabs, array( 'class' => "nav nav-tabs" ) );

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/manage/gallery' ) );

return $textTop.'
<div class="row-fluid">
	<div id="not-layout-gallery-list" class="span3">
		<div class="content-panel">
			<h3>'.$words['index']['heading'].'</h3>
			<div class="content-panel-inner">
				'.$galleries.'
				<div class="buttonbar">
					<a href="./manage/gallery/add" class="btn btn-small not-btn-info btn-success"><i class="icon-plus icon-white"></i> '.$words['index']['buttonAdd'].'</a>
				</div>
			</div>
		</div>
	</div>
	<div class="span9">
		<div class="tabbable" id="tabs-gallery-editor"> <!-- Only required for left/right tabs -->
			'.$listTabs.'
			<div class="tab-content">
				<div class="tab-pane" id="tab1">
					'.$view->loadTemplateFile( 'manage/gallery/edit.gallery.php' ).'
				</div>
				<div class="tab-pane" id="tab2">
					'.$view->loadTemplateFile( 'manage/gallery/edit.images.php' ).'
				</div>
				<div class="tab-pane" id="tab3">
					'.$view->loadTemplateFile( 'manage/gallery/edit.upload.php' ).'
				</div>
			</div>
		</div>
	</div>
</div>
'.$textBottom;
?>
