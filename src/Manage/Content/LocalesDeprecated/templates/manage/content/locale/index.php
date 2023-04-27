<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$panelEdit		= '';
$panelAddFolder	= '';
$panelAddFile	= '';

if( $fileId && isset( $content ) ){
	$attributesTextarea	= [
		'style'	=> 'width: 100%; height: 400px; font-size: 0.9em; padding: 0.5em 1em;',
		'name'	=> 'content',
		'id'	=> 'input_content',
		'class'	=> 'max not-CodeMirror not-CodeMirror-auto',
	];
	$attributesButton	= [
		'type'	=> 'submit',
		'name'	=> 'do',
		'value'	=> 'save',
		'id'	=> 'input_content',
		'class'	=> 'button save btn btn-primary',
	];
	$textarea	= HtmlTag::create( 'textarea', htmlentities( $content, ENT_COMPAT, 'UTF-8' ), $attributesTextarea );
	$buttonSave	= HtmlTag::create( 'button', '<i class="icon-ok icon-white"></i> '.$words['edit']['buttonSave'], $attributesButton );

	$panelEdit	= '
<div class="content-panel">
	<h3>'.$words['edit']['heading'].'</h3>
	<div class="content-panel-inner">
		<form id="form_content-editor" name="editContent" action="./manage/content/locale/edit/'.$language.'/'.$type.'/'.$fileId.'" method="post">
			'.$textarea.'
			<div class="buttonbar">
				'.$buttonSave.'
			</div>
		</form>
	</div>
</div>
';
}
else if( $language ){
//	$panelAddFolder	= '[AddFolder]';
//	$panelAddFile	= '[AddFile]';
}

$filterLanguage		= '';
if( count( $languages ) > 1 ){
	$optLanguage	= HtmlElements::Options( array_combine( $languages, $languages ), $language );
	$filterLanguage	= '
		<div class="row-fluid">
			<div class="span12">
				<label for="input_language" class="mandatory">'.$words['index']['labelLanguage'].'</label>
				<select name="language" id="input_language" class="max mandatory not-cmSelectBox span12" onchange="this.form.submit()">'.$optLanguage.'</select>
			</div>
		</div>';
}
else
	$filterLanguage		= HtmlTag::create( 'input', NULL, ['type' => 'hidden', 'name' => 'language', 'value' => $language] );

$optType	= HtmlElements::Options( $words['types'], $type );

$panelFilter	= '
<div class="content-panel">
	<h3>'.$words['index']['heading'].'</h3>
	<div class="content-panel-inner">
		<form name="form_content-selector" id="file-selector" action="./manage/content/locale" method="post">
			'.$filterLanguage.'
			<div class="row-fluid">
				<div class="span12">
					<label for="input_type">'.$words['index']['labelType'].'</label>
					<select name="type" id="input_type" class="span12" onchange="this.form.submit()">'.$optType.'</select>
				</div>
			</div>
			'.$fileTree.'
		</form>
	</div>
</div>';

extract( $view->populateTexts( ['index.top', 'index.bottom'], 'html/manage/content' ) );

return $textIndexTop.'
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="not-column-left-80 span9">
		'.$panelEdit.'
		'.$panelAddFolder.'
		'.$panelAddFile.'
	</div>
</div>'.$textIndexBottom;
