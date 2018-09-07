<?php
$w				= (object) $words['edit'];
$wSitemap		= (object) $words['edit-sitemap'];

if( $page->type == 1 ){
	return '<div class="alert alert-info"><em>'.$wSitemap->no_sitemap.'</em></div>';
}

$optChangefreq	= UI_HTML_Elements::Options( $words['changefreqs'], $page->changefreq );
$optPriority	= UI_HTML_Elements::Options( $words['priorities'], $page->priority );

return '
<div class="content-panel content-panel-form">
	<div class="content-panel-inner">
		<form action="./manage/page/edit/'.$page->pageId.'/'.$version.'" method="post" class="cmFormChange-auto form-changes-auto">
			<!--<h3>'.$wSitemap->heading.'</h3>-->
			<p>
				<small class="muted">
					Diese Angaben sind für die <a href="https://de.wikipedia.org/wiki/Webcrawler" target="_blank">Crawler</a> der Suchmaschinen bestimmt
					und werden bei der automatischen Erzeugung von <a href="https://de.wikipedia.org/wiki/Sitemaps-Protokoll" target="_blank">Sitemaps</a> verwendet.<br/>
					Anhand dieser Einstellungen bestimmen die Crawler, wann sie diese Seite wieder besuchen werden.
				</small>
			</p>
			<br/>
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
				<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i> '.$wSitemap->buttonSave.'</button>
			</div>
		</form>
	</div>
</div>
';
?>
