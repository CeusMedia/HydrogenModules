<?php
$w				= (object) $words['edit'];

if( $page->type == 2 )
	$content	= '<div class="alert alert-info"><em>'.$w->no_editor.'</em></div>';
else if( $page->type == 1 )
	$content	= '<div class="alert alert-info"><em>'.$w->no_content.'</em></div>';
else if( $page->type == 0 ){
	$optVersion	= array( '' => '- latest -' );
	foreach( $versions as $entry )
		$optVersion[$entry->version]	= $entry->version;

	$optVersion	= UI_HTML_Elements::Options( $optVersion, $version );

	if( $page->format === "MD" )
		unset( $words['editors']['TinyMCE'] );
	$optEditor	= $words['editors'];

	$optEditor	= UI_HTML_Elements::Options( $optEditor, $editor );

	$optFormat	= $words['formats'];

	$optFormat	= UI_HTML_Elements::Options( $optFormat, $page->format );

	$format		= $page->format === "MD" ? "Markdown" : "HTML";


	$fieldVersion	= '';
	if( $versions ){
		$fieldVersion	= '
				<div class="span2">
					<label for="input_version">'.$words['edit']['labelVersion'].'</label>
					<select class="span12" name="version" id="input_version" onchange="loadVersion('.$page->pageId.', this.value);">'.$optVersion.'</select>
				</div>';
		}

		$content	= '
<div class="content-panel content-panel-form">
	<div class="content-panel-inner">
		<form action="./manage/page/edit/'.$page->pageId.'/'.$version.'" method="post" class="cmFormChange-auto form-changes-auto">
			<div class="row-fluid">
				<div class="span3">
					<label for="input_editor">'.$words['edit']['labelEditor'].'</label>
					<select class="span12" name="editor" id="input_editor">'.$optEditor.'</select>
		<!--			<div class="input-prepend">
						<span class="add-on">'.$words['edit']['labelEditor'].'</span>
						<select class="span12" name="editor" id="input_editor" onchange="PageEditor.setEditor(this);">'.$optEditor.'</select>
					</div>-->
				</div>
				'.$fieldVersion.'
		<!--		<div class="span3">
					<label class="checkbox">
						<input type="checkbox" name="autosave" disabled="disabled"/>
						automatisch speichern
					</label>
				</div>-->
				<div class="span4 pull-right text-right">
					<label>Format</label>
					<span class="muted" style="font-size: 2em">'.$format.'</span>
				</div>
			</div>
			<div class="row-fluid">
				<textarea name="content" id="input_content" class="span12" rows="20">'.htmlentities( $page->content, ENT_QUOTES, 'UTF-8' ).'</textarea>
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
function loadVersion(pageId, version){
	document.location.href="./manage/page/edit/"+pageId+"/"+version;
}
	</script>';
}
return $content;
?>
