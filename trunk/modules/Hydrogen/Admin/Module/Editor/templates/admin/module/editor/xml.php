<?php

#remark( $moduleFile );
#remark( '<code>'. ).'</code>' );
#die;

function convertInnerTabs( $content, $replace, $indent = 4 ){
	$lines	= explode( "\n", $content );
	foreach( $lines as $nr => $line ){
		while( substr_count( $lines[$nr], "\t" ) ){
			$pos			= strpos( $lines[$nr], "\t" );
			$part1			= substr( $lines[$nr], 0, $pos );
			$part2			= substr( $lines[$nr], $pos + 1 );
			$lengthNominal	= ( floor( strlen( $part1 ) / $indent ) + 1 ) * $indent;
			$lengthReal		= strlen( $part1 );
			$stringIndent	= str_repeat( $replace, $lengthNominal - $lengthReal );
			$lines[$nr]		= $part1.$stringIndent.$part2;
		}
	}
	return implode( "\n", $lines );
}

$xmlSpaced	=  convertInnerTabs( $xml, ' ', 4 );

return '
<div id="xml-viewer" onclick="toggleXmlEditor()">
	<xmp class="xml">'.$xmlSpaced.'</xmp>
</div>
<div id="xml-editor" style="display: none">
	<form action="./manage/module/editor/saveXml/'.$moduleId.'?tab=xml" method="post">
		<textarea name="content" id="input_content" rows="20">'.$xml.'</textarea>
		<div class="buttonbar">
			<button type="button" class="button cancel" onclick="toggleXmlEditor()"><span>Ansicht</span></button>
			'.UI_HTML_Elements::Button( 'save', 'save', 'button save' ).'
		</div>
	</form>
</div>
<script>
function toggleXmlEditor(){
	$("#xml-viewer").toggle();
	$("#xml-editor").toggle();
}
</script>
';

ob_start();
xmp( $xml );
return ob_get_clean();

?>