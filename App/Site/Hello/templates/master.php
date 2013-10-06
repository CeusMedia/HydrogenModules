<?php

$body	= '
<div id="layout-container">
	<div id="layout-page" class="container">
		<div id="layout-header"></div>
		<div id="layout-field">
			<div id="layout-messenger">'.$messenger->buildMessages().'</div>
			<div id="layout-content">
				'.$content.'
			</div>
		</div>
		<div id="layout-footer"></div>
	</div>
</div>
';

$page->addBody( $body );
return $page->build();
?>
