<?php

$tabs	= View_Manage_Job::renderTabs( $env );

$panel	= '
<div class="content-panel">
	<h3>'.$words['index']['heading'].'</h3>
	<div class="content-panel-inner">
		<div class="alert alert-info">Noch keine Dashboard verfügbar.</div>
	</div>
</div>';

return $tabs.$panel;
