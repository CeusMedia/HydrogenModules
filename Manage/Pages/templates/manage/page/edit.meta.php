<?php
$w				= (object) $words['edit'];
$wMeta			= (object) $words['edit-meta'];

$iconCopy		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconSuggest	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-magic' ) );
$iconExclude	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-ban' ) );

$metaPageTypes	= array(
	Model_Page::TYPE_CONTENT,
	Model_Page::TYPE_MODULE,
);
if( !in_array( (int) $page->type, $metaPageTypes, TRUE ) )
	return '<div class="alert alert-info"><em>'.$wMeta->no_meta.'</em></div>';

$optChangefreq	= UI_HTML_Elements::Options( $words['changefreqs'], $page->changefreq );
$optPriority	= UI_HTML_Elements::Options( $words['priorities'], $page->priority );

$buttonSuggest		= '';
$buttonBlacklist	= '';
if( $page->type == 0 ){
	$buttonSuggest	= UI_HTML_Tag::create( 'button', $iconSuggest.'&nbsp;'.$wMeta->buttonSuggest, array(
		'type'			=> "button",
		'class'			=> "btn btn-mini",
		'id'			=> 'btn-meta-suggest',
		'data-page-id'	=> $page->pageId,
		'data-target'	=> '#input_page_keywords',
		'data-question'	=> 'Wörter ausschließen, getrennt mit Leerzeichen',
	) );
	$buttonBlacklist	= UI_HTML_Tag::create( 'button', $iconExclude.'&nbsp;'.$wMeta->buttonBlacklist, array(
		'type'			=> "button",
		'class'			=> "btn btn-mini",
		'id'			=> 'btn-meta-blacklist',
		'data-page-id'	=> $page->pageId,
		'data-target'	=> '#input_page_keywords',
		'data-question'	=> 'Wörter ausschließen, getrennt mit Leerzeichen',
	) );
}

return '
<div class="content-panel content-panel-form">
	<div class="content-panel-inner">
		<form action="./manage/page/edit/'.$page->pageId.'/'.$version.'" method="post" class="cmFormChange-auto form-changes-auto">
			<!--<h3>'.$wMeta->heading.'</h3>-->
			<div class="row-fluid">
				<div class="span6">
					<h4>Werte für diese Seite</h4>
					<p><small class="muted">Wenn keine Werte gespeichert wurden, werden die Standartwerte benutzt.</small></p>
				</div>
				<div class="span6">
					<h4>Standardwerte</h4>
					<div><small class="muted">Diese Werte wurden im Meta-Modul der Website definiert.</small></div>
				</div>
			</div>
			<div id="meta-defaults" data-todo="refactor this element and its jQuery bindings">
				<div class="row-fluid">
					<div class="span6">
						<label for="input_page_description">'.$wMeta->labelDescription.'</label>
						<textarea class="span12" rows="4" name="page_description" id="input_page_description">'.htmlentities( $page->description, ENT_QUOTES, 'UTF-8' ).'</textarea>
					</div>
					<div class="span6">
						<label for="input_page_description">'.$wMeta->labelDefaultDescription.'</label>
						<textarea class="span12" rows="2" name="page_default_description" id="input_page_default_description">'.htmlentities( $meta['description'], ENT_QUOTES, 'UTF-8' ).'</textarea>
						<div class="btn-group">
							<button type="button" class="btn btn-mini" id="btn-copy-description">'.$iconCopy.' '.$wMeta->buttonCopy.'</button>
							<button type="button" class="btn btn-mini" disabled="disabled">save</button>
						</div>
					</div>
				</div>
				<div class="row-fluid">
					<div class="span6">
						<label for="input_page_keywords">'.$wMeta->labelKeywords.'</label>
						<textarea class="span12" rows="6" name="page_keywords" id="input_page_keywords">'.htmlentities( $page->keywords, ENT_QUOTES, 'UTF-8' ).'</textarea>
						<div class="btn-group">
							'.$buttonSuggest.'
							'.$buttonBlacklist.'
						</div>
					</div>
					<div class="span6">
						<label for="page_default_keywords">'.$wMeta->labelDefaultKeywords.'</label>
						<textarea class="span12" rows="4" name="page_default_keywords" id="input_page_default_keywords">'.htmlentities( $meta['keywords'], ENT_QUOTES, 'UTF-8' ).'</textarea>
						<div class="btn-group">
							<button type="button" class="btn btn-mini" id="btn-copy-keywords">'.$iconCopy.' '.$wMeta->buttonCopy.'</button>
							<button type="button" class="btn btn-mini" disabled="disabled">save</button>
						</div>
					</div>
				</div>
				<br/>
				<div class="row-fluid">
					<div class="span6">
						<label for="input_page_keywords_blacklist">'.$wMeta->labelBlacklistedKeywords.'</label>
						<textarea class="span12" rows="2" name="page_keywords_blacklist" id="input_page_keywords_blacklist" disabled="disabled" readonly="readonly">'.htmlentities( join( ', ', $metaBlacklist ), ENT_QUOTES, 'UTF-8' ).'</textarea>
					</div>
				</div>
	<!--
				<div class="row-fluid">
					<div class="span6">
						<label for="input_page_author">'.$wMeta->labelAuthor.'</label>
						<input class="span12" type="text" name="page_author" id="input_page_author" value="'.htmlentities( ""/*$page->author*/, ENT_QUOTES, 'UTF-8' ).'"/>
					</div>
					<div class="span6">
						<label for="input_page_author">'.$wMeta->labelDefaultAuthor.'</label>
						<input class="span12" type="text" name="page_default_author" id="input_page_default_author" value="'.htmlentities( $meta['author'], ENT_QUOTES, 'UTF-8' ).'"/>
						<button type="button" class="btn btn-small">save</button>
					</div>
				</div>

				<div class="row-fluid">
					<div class="span6">
						<label for="input_page_publisher">'.$wMeta->labelPublisher.'</label>
						<input class="span12" type="text" name="page_publisher" id="input_page_publisher" value="'.htmlentities( ""/*$page->publisher*/, ENT_QUOTES, 'UTF-8' ).'"/>
					</div>
					<div class="span6">
						<label for="input_page_author">'.$wMeta->labelDefaultPublisher.'</label>
						<input class="span12" type="text" name="page_default_publisher" id="input_page_default_publisher" value="'.htmlentities( $meta['publisher'], ENT_QUOTES, 'UTF-8' ).'"/>
						<button type="button" class="btn btn-small">save</button>
					</div>
				</div>-->
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i> '.$w->buttonSave.'</button>
			</div>
		</form>
	</div>
</div>
';
?>
