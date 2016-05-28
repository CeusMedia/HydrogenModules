<?php

return '
<div class="content-panel content-panel-form">
	<h3>Raw Response</h3>
	<div class="content-panel-inner">
		<div id="response-raw">
			<xmp class="js">'.ADT_JSON_Formater::format( $json->raw ).'</xmp>
		</div>
	</div>
</div>
';
