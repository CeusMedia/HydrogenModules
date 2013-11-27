<?php

$panelFilter	= $this->loadTemplate( 'manage/locale', 'filter' );
$panelList		= $this->loadTemplate( 'manage/locale', 'list' );

$w	= (object) $words['edit'];

$attributesTextarea	= array(
	'style'	=> 'width: 97%; min-height: 400px; font-size: 1.1em; padding: 0.2em 0.3em;',
	'name'	=> 'content',
	'class'	=> 'CodeMirror',
);
$attributesButton	= array(
	'type'	=> 'submit',
	'name'	=> 'do',
	'value'	=> 'save',
	'class'	=> 'button save btn btn-success',
);

$optPath	= array_merge( array( '' ), $paths );
$optPath	= array_combine( $optPath, $optPath );
$optPath	= UI_HTML_Elements::Options( $optPath, $pathName );

//$textarea		= UI_HTML_Tag::create( 'textarea', utf8_encode( htmlentities( utf8_decode( $content ) ) ), $attributesTextarea );
$textarea		= UI_HTML_Tag::create( 'textarea', $content, $attributesTextarea );
$buttonSave		= UI_HTML_Tag::create( 'button', '<i class="icon-ok icon-white"></i> '.$w->buttonSave.'</span>', $attributesButton );
$buttonCancel	= UI_HTML_Elements::LinkButton( './manage/locale', '<i class="icon-arrow-left"></i> '.$w->buttonCancel, 'button cancel btn' );
$buttonRemove	= UI_HTML_Elements::LinkButton( './manage/locale/remove/'.$fileHash, '<i class="icon-remove icon-white"></i> '.$w->buttonRemove, 'button remove btn btn-danger', $w->buttonRemoveConfirm );

$panelEdit	= '
<form id="form_locale-editor" name="editContent" action="./manage/locale/edit/'.$fileHash.'" method="post">
	<h4>'.$w->legend.'</h4>
	<div class="row-fluid">
		<div class="span6">
			<label for="input_name">'.$w->labelName.'</label>
			<input type="text" name="name" id="input_name" class="max span11" value="'.addslashes( $fileName ).'"/>
		</div>
		<div class="span6">
			<label for="input_path">'.$w->labelPath.'</label>
			<select name="path" id="input_path" class="max span11">'.$optPath.'</select>
		</div>
	</div>
	<div class="row-fluid">
		'.$textarea.'
	</div>
	<div class="buttonbar">
		'.$buttonCancel.'
		'.$buttonSave.'
		'.$buttonRemove.'
	</div>
</form>';

return '
<!--<h2>HTML Inhalte</h2>-->
<div class="row-fluid">
	<div class="span4">
		'.$panelFilter.'
		'.$panelList.'
	</div>
	<div class="span8">
		'.$panelEdit.'
	</div>
</div>
';
?>
