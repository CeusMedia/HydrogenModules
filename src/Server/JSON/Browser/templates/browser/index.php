<?php

$panelForm		= $view->loadTemplateFile( 'browser/panel.form.php' );
$panelInfo		= $view->loadTemplateFile( 'browser/panel.info.php' );
$panelResponse	= $view->loadTemplateFile( 'browser/panel.response.php' );
$panelJson		= $view->loadTemplateFile( 'browser/panel.json.php' );
$panelError		= $view->loadTemplateFile( 'browser/panel.error.php' );
$panelDebug		= $view->loadTemplateFile( 'browser/panel.debug.php' );

return '
<div class="container">
	<div class="navbar navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container">
				<h2>'.$config->get( 'app.name' ).'</h2>
			</div>
		</div>
	</div>
	<br/>
	<br/>
	<br/>
	<br/>
	<div class="row-fluid">
		<div class="span4">
			'.$panelForm.'
			'.$panelInfo.'
		</div>
		<div class="span8">
			'.$panelResponse.'
			'.$panelError.'
			'.$panelDebug.'
			'.$panelJson.'
		</div>
	</div>
</div>';
?>
