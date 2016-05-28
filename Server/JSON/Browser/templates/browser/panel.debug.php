<?php

if( !strlen( $response->debug ) )
	return;

return '
<div class="content-panel content-panel-form">
	<h3>Debug Notice</h3>
	<div class="content-panel-inner">
		<div id="response-debug">
			<xmp class="code">'.$response->debug.'</xmp>
		</div>
	</div>
</div>
';
