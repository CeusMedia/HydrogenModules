<?php

$classAutoEditor	= $editor;
$options			= array();
$style				= array();
$fileExt			= strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );

if( $editor === "CodeMirror" ){
	$classAutoEditor	= "CodeMirror-auto";
	$style				= array(
		'width: 100%',
		'height: 485px',
		'font-size: 1.1em',
		'padding: 1em',
	);
	$options			= 	array(
		'codemirror-read-only'			=> $readonly ? 'nocursor' : NULL,
		'codemirror-callback-save'		=> 'ModuleManageContentLocale.onCodeMirrorSave',
		'codemirror-callback-change'	=> 'ModuleManageContentLocale.onCodeMirrorChange',
	);
}
else if( $editor === "Ace" ){
	$classAutoEditor	= "ace-auto";
	$mode	= NULL;
	if( $folder === 'locale' )
		$mode	= 'ace/mode/ini';
	else{
		if( $fileExt === 'txt' )
			$mode	= 'ace/mode/text';
	}
	$options			= array(
		'ace-option-mode'	=> $mode,
	);
	$script	= '
		ModuleAceAutoSave.applyToEditor(
			jQuery("#input_content").data("ace-editor-instance"),
			"./manage/content/locale/ajaxSaveContent"
		);';
	$env->getPage()->js->addScriptOnReady( $script, 9 );
}
else if( $editor === "TinyMCE" ){
	$classAutoEditor	= "TinyMCE";
	$options			= array( 'tinymce-mode' => 'extended' );
}

$textarea	= UI_HTML_Tag::create( 'textarea', htmlentities( $content, ENT_COMPAT, 'UTF-8' ), array(
	'name'		=> 'content',
	'id'		=> 'input_content',
	'class'		=> 'span12 '.$classAutoEditor,
	'readonly'	=> $readonly ? 'readonly' : NULL,
	'disabled'	=> $readonly ? 'disabled' : NULL,
	'rows'		=> 30,
	'style'		=> join( "; ", $style ),
), $options );

$buttonSave	= UI_HTML_Tag::create( 'button', '<i class="icon-ok icon-white"></i> '.$words['edit']['buttonSave'], array(
	'type'	=> 'submit',
	'name'	=> 'do',
	'value'	=> 'save',
	'class'	=> 'btn btn-primary',
	'disabled'	=> $readonly ? 'disabled' : NULL,
) );

$buttonbar	= '';
if( $editor !== "Ace" )
	$buttonbar	= UI_HTML_Tag::create( 'div', $buttonSave, array( 'class' => 'buttonbar' ) );

$optEditor	= array( 'Plain' => $words['editors']['Plain'] );
foreach( $editors as $key )
	$optEditor[$key]	= $words['editors'][$key];
$optEditor	= UI_HTML_Elements::Options( $optEditor, $editor );

$panelEdit	= '
<div class="content-panel">
	<h3>'.$words['edit']['heading'].'</h3>
	<div class="content-panel-inner">
		<form class="form-inline" action="./manage/content/locale/setEditor" method="post">
			<input type="hidden" name="from" value="./manage/content/locale"/>
			<input type="hidden" name="ext" value="'.$fileExt.'"/>
			<label for="input_editor">'.$words['edit']['labelEditor'].'&nbsp;&nbsp;</label>
			<select name="editor" id="input_editor" onchange="this.form.submit()" class="span3">'.$optEditor.'</select>&nbsp;&nbsp;
			<label class="checkbox">
				<input type="checkbox" name="default" onchange="this.form.submit()"'.( $editorByExt == $editor  ? ' checked="checked"' : '' ).'>&nbsp;'.$words['edit']['labelDefault'].' <span class="muted">.'.$fileExt.'</span>
			</label>
		</form>
		<div class="muted">'.$words['edit']['labelFile'].' <code>'.$folderPathFull.$file.'</code></div>
		<form id="form_content-editor" name="editContent" action="./manage/content/locale/edit/'.$folder.'/'.$language.'/'.base64_encode( $file ).'" method="post">
			'.$textarea.'
			'.$buttonbar.'
		</form>
	</div>
</div>';

$panelFilter	= $view->loadTemplateFile( 'manage/content/locale/filter.php' );

extract( $view->populateTexts( array( 'index.top', 'index.bottom' ), 'html/manage/content/locale' ) );

return $textIndexTop.'
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span9">
		'.$panelEdit.'
	</div>
</div>'.$textIndexBottom;
?>
