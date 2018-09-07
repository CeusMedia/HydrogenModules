<?php
$w				= (object) $words['edit'];
$wMeta			= (object) $words['edit-meta'];

$iconCopy		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );

if( $page->type == 1 ){
	return '<div class="alert alert-info"><em>'.$wMeta->no_meta.'</em></div>';
}

$optChangefreq	= UI_HTML_Elements::Options( $words['changefreqs'], $page->changefreq );
$optPriority	= UI_HTML_Elements::Options( $words['priorities'], $page->priority );

$buttonSuggest		= '';
$buttonBlacklist	= '';
if( $page->type == 0 ){
	$buttonSuggest		= '<button type="button" class="btn btn-mini" onclick="ModuleManagePages.PageEditor.suggestKeyWords('.$page->pageId.', \'#input_page_keywords\');">'.$wMeta->buttonSuggest.'</button>';
	$buttonBlacklist	= '<button type="button" class="btn btn-mini" onclick="ModuleManagePages.PageEditor.blacklistSuggestedWords('.$page->pageId.', \'#input_page_keywords\');">'.$wMeta->buttonBlacklist.'</button>';
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
						<textarea class="span12" rows="2" name="page_default_description" id="input_page_default_description">'.htmlentities( $meta['default.description'], ENT_QUOTES, 'UTF-8' ).'</textarea>
						<button type="button" class="btn btn-mini" id="btn-copy-description">'.$iconCopy.' '.$wMeta->buttonCopy.'</button>
						<button type="button" class="btn btn-mini" disabled="disabled">save</button>
					</div>
				</div>
				<div class="row-fluid">
					<div class="span6">
						<label for="input_page_keywords">'.$wMeta->labelKeywords.'</label>
						<textarea class="span12" rows="6" name="page_keywords" id="input_page_keywords">'.htmlentities( $page->keywords, ENT_QUOTES, 'UTF-8' ).'</textarea>
						'.$buttonSuggest.' '.$buttonBlacklist.'
					</div>
					<div class="span6">
						<label for="input_page_description">'.$wMeta->labelDefaultKeywords.'</label>
						<textarea class="span12" rows="4" name="page_default_keywords" id="input_page_default_keywords">'.htmlentities( $meta['default.keywords'], ENT_QUOTES, 'UTF-8' ).'</textarea>
						<button type="button" class="btn btn-mini" id="btn-copy-keywords">'.$iconCopy.' '.$wMeta->buttonCopy.'</button>
						<button type="button" class="btn btn-mini" disabled="disabled">save</button>
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
						<input class="span12" type="text" name="page_default_author" id="input_page_default_author" value="'.htmlentities( $meta['default.author'], ENT_QUOTES, 'UTF-8' ).'"/>
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
						<input class="span12" type="text" name="page_default_publisher" id="input_page_default_publisher" value="'.htmlentities( $meta['default.publisher'], ENT_QUOTES, 'UTF-8' ).'"/>
						<button type="button" class="btn btn-small">save</button>
					</div>
				</div>-->
			</div>
		</form>
	</div>
</div>
';
?>
