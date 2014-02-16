<?php

$panelEdit		= '';
$panelAddFolder	= '';
$panelAddFile	= '';

if( $fileId && isset( $content ) ){
	$attributesTextarea	= array(
		'style'	=> 'width: 100%; height: 400px; font-size: 0.9em; padding: 0.5em 1em;',
		'name'	=> 'content',
		'id'	=> 'input_content',
		'class'	=> 'max CodeMirror CodeMirror-auto',
	);
	$attributesButton	= array(
		'type'	=> 'submit',
		'name'	=> 'do',
		'value'	=> 'save',
		'class'	=> 'button save btn btn-success',
	);
	$textarea	= UI_HTML_Tag::create( 'textarea', htmlentities( $content, ENT_COMPAT, 'UTF-8' ), $attributesTextarea );
	$buttonSave	= UI_HTML_Tag::create( 'button', '<i class="icon-ok icon-white"></i> '.$words['edit']['buttonSave'], $attributesButton );

	$panelEdit	= '
	<h3>'.$words['edit']['heading'].'</h3>
	<form id="form_content-editor" name="editContent" action="./manage/content/edit/'.$language.'/'.$type.'/'.$fileId.'" method="post">
		'.$textarea.'
		<div class="buttonbar">
			'.$buttonSave.'
		</div>
	</form>
	';
}
else if( $language ){
//	$panelAddFolder	= '[AddFolder]';
//	$panelAddFile	= '[AddFile]';
}

$languageSelector	= '';
if( count( $languages ) > 1 ){
	$optLanguage	= UI_HTML_Elements::Options( array_combine( $languages, $languages ), $language );
	$filterLanguage	= '
		<div class="row-fluid">
			<div class="span12">
				<label for="input_language" class="mandatory">'.$words['index']['labelLanguage'].'</label>
				<select name="language" id="input_language" class="max mandatory not-cmSelectBox span12" onchange="this.form.submit()">'.$optLanguage.'</select>
			</div>
		</div>';
}

$optType	= UI_HTML_Elements::Options( $words['types'], $type );

$panelFilter	= '
<form name="form_content-selector" action="./manage/content" method="post">
	<h3>'.$words['index']['heading'].'</h3>
	'.$filterLanguage.'
	<div class="row-fluid">
		<div class="span12">
			<label for="input_type">'.$words['index']['labelType'].'</label>
			<select name="type" id="input_type" class="span12" onchange="this.form.submit()">'.$optType.'</select>
		</div>
	</div>
	'.$fileTree.'
</form>';

extract( $view->populateTexts( array( 'index.top', 'index.bottom' ), 'html/manage/content' ) );

return $textIndexTop.'
<div class="row-fluid">
	<div class="not-column-left-20 span3">
		'.$panelFilter.'
	</div>
	<div class="not-column-left-80 span9">
		'.$panelEdit.'
		'.$panelAddFolder.'
		'.$panelAddFile.'
	</div>
</div>'.$textIndexBottom;
?>
