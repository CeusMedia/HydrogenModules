<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var array<string,array<string,string>> $words */
/** @var string $content */
/** @var string $fileHash */
/** @var string $filePath */
/** @var string $fileName */
/** @var string $pathName */
/** @var array<string> $paths */

$panelFilter	= $view->loadTemplate( 'manage/locale', 'filter' );
$panelList		= $view->loadTemplate( 'manage/locale', 'list' );

$w	= (object) $words['edit'];

$attributesTextarea	= [
	'style'	=> 'min-height: 400px; padding: 0.2em 0.3em;',
	'name'	=> 'content',
	'class'	=> 'span12 CodeMirror',
];
$attributesButton	= [
	'type'	=> 'submit',
	'name'	=> 'do',
	'value'	=> 'save',
	'class'	=> 'button save btn btn-success',
];

$optPath	= array_merge( [''], $paths );
$optPath	= array_combine( $optPath, $optPath );
$optPath	= HtmlElements::Options( $optPath, $pathName );

//$textarea		= HtmlTag::create( 'textarea', utf8_encode( htmlentities( utf8_decode( $content ) ) ), $attributesTextarea );
$textarea		= HtmlTag::create( 'textarea', $content, $attributesTextarea );
$buttonSave		= HtmlTag::create( 'button', '<i class="icon-ok icon-white"></i> '.$w->buttonSave.'</span>', $attributesButton );
$buttonCancel	= HtmlElements::LinkButton( './manage/locale', '<i class="icon-arrow-left"></i> '.$w->buttonCancel, 'button cancel btn' );
$buttonRemove	= HtmlElements::LinkButton( './manage/locale/remove/'.$fileHash, '<i class="icon-remove icon-white"></i> '.$w->buttonRemove, 'button remove btn btn-danger', $w->buttonRemoveConfirm );

$panelEdit	= '
<form id="form_locale-editor" name="editContent" action="./manage/locale/edit/'.$fileHash.'" method="post">
	<h3><span class="muted">'.$w->heading.' </span>'.$filePath.'</h3>
	<div class="row-fluid">
<!--		<div class="span6">
			<label for="input_name">'.$w->labelName.'</label>
			<input type="text" name="name" id="input_name" class="max span11" value="'.addslashes( $fileName ).'"/>
		</div>
		<div class="span6">
			<label for="input_path">'.$w->labelPath.'</label>
			<select name="path" id="input_path" class="max span11">'.$optPath.'</select>
		</div>-->
	</div>
	<div class="row-fluid">
		<div class="span12">
			'.$textarea.'
		</div>
	</div>
	<div class="buttonbar">
		'.$buttonCancel.'
		'.$buttonSave.'
		'.$buttonRemove.'
	</div>
</form>';

return '
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
		'.$panelList.'
	</div>
	<div class="span9">
		'.$panelEdit.'
	</div>
</div>
';
