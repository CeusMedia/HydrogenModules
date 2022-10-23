<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$panelFilter	= $view->loadTemplate( 'manage/content/static', 'filter' );
$panelList		= $view->loadTemplate( 'manage/content/static', 'list' );

$w	= (object) $words['edit'];

$attributesTextarea	= array(
	'style'	=> 'width: 97%; min-height: 250px; font-size: 1.1em; padding: 0.2em 0.3em;',
	'name'	=> 'content',
);
$attributesButton	= array(
	'type'	=> 'submit',
	'name'	=> 'do',
	'value'	=> 'save',
	'class'	=> 'button save',
);

$optPath	= array_merge( [''], $paths );
$optPath	= array_combine( $optPath, $optPath );
$optPath	= HtmlElements::Options( $optPath, $pathName );

$textarea		= HtmlTag::create( 'textarea', utf8_encode( htmlentities( utf8_decode( $content ) ) ), $attributesTextarea );
$buttonSave		= HtmlTag::create( 'button', '<span>'.$w->buttonSave.'</span>', $attributesButton );
$buttonCancel	= HtmlElements::LinkButton( './manage/content/static', $w->buttonCancel, 'button cancel' );
$buttonRemove	= HtmlElements::LinkButton( './manage/content/static/remove/'.$fileHash, $w->buttonRemove, 'button remove', $w->buttonRemoveConfirm );

$panelEdit	= '
<form id="form_content-editor" name="editContent" action="./manage/content/static/edit/'.$fileHash.'" method="post">
	<fieldset>
		<legend class="edit">'.$w->legend.'</legend>
		<ul class="input">
			<li>
				<div class="column-left-50">
					<label for="input_name">'.$w->labelName.'</label></br>
					<input type="text" name="name" id="input_name" class="max" value="'.addslashes( $fileName ).'"/>
				</div>
				<div class="column-right-50">
					<label for="input_path">'.$w->labelPath.'</label></br>
					<select name="path" id="input_path" class="max">'.$optPath.'</select>
				</div>
				<div class="column-clear"></div>
			</li>
			<li>
				'.$textarea.'
			</li>
		</ul>
		<div class="buttonbar">
			'.$buttonCancel.'
			'.$buttonSave.'
			'.$buttonRemove.'
		</div>
	</fieldset>
</form>';

return '
<!--<h2>HTML Inhalte</h2>-->
<div class="column-left-20">
	'.$panelFilter.'
	'.$panelList.'
</div>
<div class="column-right-80"">
	'.$panelEdit.'
</div>
';
?>
