<?php
try{
if( !$issue->notes )
	return;

$helper	= new View_Helper_Work_Issue_Changes( $env );
$helper->setIssue( $issue );
$list	= $helper->render();

return '
<div class="content-panel">
	<h3>Entwicklung</h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';
}
catch( Exception $e ){
	UI_HTML_Exception_Page::display( $e);
}
?>
