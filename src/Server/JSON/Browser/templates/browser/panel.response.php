<?php

use CeusMedia\Common\ADT\JSON\Pretty as JsonFormat;

/** @var object $response */

if( is_object( $response->data ) || is_array( $response->data ) ){
	$content		= trim( JsonFormat::print( json_encode( $response->data ) ) );
	$content	= '<xmp class="js">'.$content.'</xmp>';
}
else
	$content	= '<xmp class="code">'.$response->data.'</xmp>';

return '
<div class="content-panel content-panel-form">
	<h3>Response</h3>
	<div class="content-panel-inner">
		<div id="response-data">
			'.$content.'
		</div>
	</div>
</div>';
