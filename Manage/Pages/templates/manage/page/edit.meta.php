<?php
$w				= (object) $words['edit'];

if( $page->type == 1 ){
	return '<div class="alert alert-info"><em>'.$w->no_meta.'</em></div>';
}

$optChangefreq	= UI_HTML_Elements::Options( $words['changefreqs'], $page->changefreq );
$optPriority	= UI_HTML_Elements::Options( $words['priorities'], $page->priority );

return '
<div class="content-panel content-panel-form">
	<div class="content-panel-inner">
		<form action="./manage/page/edit/'.$page->pageId.'/'.$version.'" method="post" class="cmFormChange-auto form-changes-auto">
			<h3>Beschreibung und Schlagwörter</h3>
			<div class="row-fluid">
				<div class="span6">
					<h4>Werte für diese Seite</h4>
					<p><small class="muted">Wenn keine Werte gespeichert wurden, werden die Standartwerte benutzt.</small></p>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_description">Beschreibung</label>
							<textarea class="span12" rows="4" name="description" id="input_description">'.htmlentities( $page->description, ENT_QUOTES, 'UTF-8' ).'</textarea>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_keywords">Schlagwörter <small class="muted">(kommagetrennt)</small></label>
							<textarea class="span12" rows="6" name="keywords" id="input_keywords">'.htmlentities( $page->keywords, ENT_QUOTES, 'UTF-8' ).'</textarea>
						</div>
					</div>
			<!--		<div class="row-fluid">
						<div class="span6">
							<label for="input_author">Autor</label>
							<input class="span12" type="text" name="author" id="input_author" value="'.htmlentities( ""/*$page->author*/, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
						<div class="span6">
							<label for="input_publisher">Herausgeber</label>
							<input class="span12" type="text" name="publisher" id="input_publisher" value="'.htmlentities( ""/*$page->publisher*/, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>-->
				</div>
				<div class="span6">
					<h4>Standardwerte</h4>
					<div><small class="muted">Diese Werte wurden im Meta-Modul der Website definiert.</small></div>
					<dl class="dl-horizontal" id="meta-defaults">
						<dt class="meta-default" data-key="description">Beschreibung</dt>
						<dd style="min-height: 8em;">'.$meta['default.description'].'</dd>
						<dt class="meta-default" data-key="keywords">Schlagwörter</dt>
						<dd style="min-height: 11em;">'.$meta['default.keywords'].'</dd>
			<!--			<dt class="meta-default" data-key="author">Autor</dt>
						<dd>'.$meta['default.author'].'</dd>
						<dt class="meta-default" data-key="publisher">Herausgeber</dt>
						<dd>'.$meta['default.publisher'].'</dd>-->
					</dl>
				</div>
			</div>
			<h3>Angaben für Suchmaschinen</h3>
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
					<label for="input_changefreq">'.$w->labelChangefreq.'</label>
					<select name="changefreq" id="input_changefreq" class="span12">'.$optChangefreq.'</select>
				</div>
				<div class="span3">
					<label for="input_priority">'.$w->labelPriority.'</label>
					<select name="priority" id="input_priority" class="span12">'.$optPriority.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i> '.$w->buttonSave.'</button>
			</div>
		</form>
	</div>
</div>
';
?>
