<?php

use CeusMedia\Common\ADT\JSON\Pretty as JsonFormat;

/** @var object $json */

return '
<div class="content-panel content-panel-form">
	<h3>Raw Response</h3>
	<div class="content-panel-inner">
		<div id="response-raw">
			<xmp class="js">'.JsonFormat::print( $json->raw ).'</xmp>
		</div>
	</div>
</div>';
