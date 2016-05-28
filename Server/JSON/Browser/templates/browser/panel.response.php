<?php

if( is_object( $response->data ) || is_array( $response->data ) ){
	$content		= trim( ADT_JSON_Formater::format( json_encode( $response->data ) ) );
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
</div>
';
