<?php

$panelCode	= '
	<fieldset>
		<legend>Code Preview</legend>
		<b>File:</b> <cite>templates/'.$fileKey.'.php</cite>
		<xmp class="php">'.( !empty( $code ) ? $code : '' ).'</xmp>
	</fieldset>
		
';
if( empty( $code ) )
	$panelCode	= '';

$panel	= '
<h3>Create Template</h3>
<form action="./dev/scaffold/template" method="post">
	<fieldset>
		<legend>Template</legend>
		<ul class="input">
			<li>
				<label for="input_file_key">Template Key <small>(e.G. blog/image/index)</small></label><br/>
				<input type="text" name="file_key" id="input_file_key" value="'.$fileKey.'"/>
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::LinkButton( './dev/scaffold', 'zurück', 'button cancel' ).'
			'.UI_HTML_Elements::Button( 'preview', 'preview', 'button view' ).'
			'.UI_HTML_Elements::Button( 'create', 'create', 'button save' ).'
		</div>
	</fieldset>
</form>
';
return '
<div class="column-left-30">
	'.$panel.'
</div>
<div class="column-left-70">
	'.$panelCode.'
</div>
<div class="column-clear"></div>
';

?>
