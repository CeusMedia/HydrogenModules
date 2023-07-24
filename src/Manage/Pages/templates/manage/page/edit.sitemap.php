<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

$w				= (object) $words['edit'];
$wSitemap		= (object) $words['edit-sitemap'];

if( $page->type == 1 ){
	return '<div class="alert alert-info"><em>'.$wSitemap->no_sitemap.'</em></div>';
}

$optChangefreq	= HtmlElements::Options( $words['changefreqs'], $page->changefreq );
$optPriority	= HtmlElements::Options( $words['priorities'], $page->priority );

extract( $view->populateTexts( ['top'], 'html/manage/page/sitemap' ) );

return '
<div class="content-panel content-panel-form">
	<div class="content-panel-inner">
		<form action="./manage/page/edit/'.$page->pageId.'/'.$version.'" method="post" class="cmFormChange-auto form-changes-auto">
			<!--<h3>'.$wSitemap->heading.'</h3>-->
			'.$textTop.'
			<div class="row-fluid">
				<div class="span3">
					<label for="input_page_changefreq">'.$wSitemap->labelChangefreq.'</label>
					<select name="page_changefreq" id="input_page_changefreq" class="span12">'.$optChangefreq.'</select>
				</div>
				<div class="span3">
					<label for="input_page_priority">'.$wSitemap->labelPriority.'</label>
					<select name="page_priority" id="input_page_priority" class="span12">'.$optPriority.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-fw fa-check"></i> '.$wSitemap->buttonSave.'</button>
			</div>
		</form>
	</div>
</div>
';
