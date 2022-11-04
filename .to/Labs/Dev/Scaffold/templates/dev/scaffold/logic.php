<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

$panelCode	= '
	<fieldset>
		<legend>Code Preview</legend>
		<b>File:</b> <cite>classes/Logic/'.( str_replace( '_', '/', $classKey ) ).'.php5</cite>
		<xmp class="php">'.( !empty( $code ) ? $code : '' ).'</xmp>
	</fieldset>
		
';
if( empty( $code ) )
	$panelCode	= '';

$panel	= '
<h3>Create Logic Class</h3>
<form action="./dev/scaffold/logic" method="post">
	<fieldset>
		<legend>Logic</legend>
		<ul class="input">
			<li>
				<label for="input_class_key">Class Key <small>(e.G. Blog_Image)</small></label><br/>
				<input type="text" name="class_key" id="input_class_key" value="'.$classKey.'"/>
			</li>
		</ul>
		<div class="buttonbar">
			'.HtmlElements::LinkButton( './dev/scaffold', 'zurück', 'button cancel' ).'
			'.HtmlElements::Button( 'preview', 'preview', 'button view' ).'
			'.HtmlElements::Button( 'create', 'create', 'button save' ).'
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
