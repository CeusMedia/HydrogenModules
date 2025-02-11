<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

/** @var array<string,array<string,string>> $words */
/** @var Entity_Page $page */
/** @var array $versions */
/** @var ?string $version */
/** @var array $editors */
/** @var ?string $editor */

$w				= (object) $words['edit'];

if( $page->type == Model_Page::TYPE_MODULE )
	$content	= '<div class="alert alert-info"><em>'.$w->no_editor.'</em></div>';
else if( $page->type == Model_Page::TYPE_BRANCH )
	$content	= '<div class="alert alert-info"><em>'.$w->no_content.'</em></div>';
else{
	$optVersion	= ['' => '- latest -'];
	foreach( $versions as $entry )
		$optVersion[$entry->version]	= $entry->version;

	$editor		= $editor ?: current( array_keys( $editors ) );

	$optVersion	= HtmlElements::Options( $optVersion, $version );
	$optEditor	= HtmlElements::Options( $editors, $editor );
	$optFormat	= HtmlElements::Options( $words['formats'], $page->format );

	$format		= $page->format === "MD" ? "Markdown" : "HTML";

	$fieldVersion	= '';
	if( $versions ){
		$fieldVersion	= '
				<div class="span2">
					<label for="input_page_version">'.$words['edit']['labelVersion'].'</label>
					<select class="span12" name="page_version" id="input_page_version" onchange="ModuleManagePages.PageEditor.loadVersion('.$page->pageId.', this.value);">'.$optVersion.'</select>
				</div>';
		}

		$content	= '
<div class="content-panel content-panel-form">
	<div class="content-panel-inner">
		<form action="./manage/page/edit/'.$page->pageId.'/'.$version.'" method="post" class="not-cmFormChange-auto not-form-changes-auto">
			<div class="row-fluid">
				<div class="span4">
					<label for="input_page_editor">'.$words['edit']['labelEditor'].'</label>
					<select class="span12" name="page_editor" id="input_page_editor">'.$optEditor.'</select>
		<!--			<div class="input-prepend">
						<span class="add-on">'.$words['edit']['labelEditor'].'</span>
						<select class="span12" name="page_editor" id="input_page_editor" onchange="ModuleManagePages.PageEditor.setEditor(this);">'.$optEditor.'</select>
					</div>-->
				</div>
				'.$fieldVersion.'
		<!--		<div class="span3">
					<label class="checkbox">
						<input type="checkbox" name="page_autosave" disabled="disabled"/>
						automatisch speichern
					</label>
				</div>-->
				<div class="span4 pull-right text-right">
					<label>Format</label>
					<span class="muted" style="font-size: 2em">'.$format.'</span>
				</div>
			</div>
			<div class="row-fluid">
				<textarea name="page_content" id="input_page_content" class="span12" rows="20" data-ace-option-max-lines="32">'.htmlentities( $page->content ?? '', ENT_QUOTES, 'UTF-8' ).'</textarea>
				<div id="hint"></div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i> '.$w->buttonSave.'</button>
			</div>
		</form>
	</div>
</div>
	<script>
var pageType = '.(int) $page->type.';
	</script>';
}
return $content;
