<?php
extract( $view->populateTexts( array( 'home' ), 'html/index/' ) );

$panelList		= $view->loadTemplate( 'index', 'index.instances' );
$panelSystem	= $view->loadTemplate( 'index', 'index.system' );

if( $instanceId )
{
	return $view->loadTemplate( 'index', 'index.instance' , array(
		'panelList'		=> $panelList,
		'panelSystem'	=> $panelSystem
	) );
}
else{
	return '
<br/>
<div class="index">
	<div class="column-left-75">
		<div class="column-right-66">
			'.$textHome.'
		</div>
		<div class="column-left-33">
			'.$panelList.'
		</div>
	</div>
	<div class="column-right-25">
		'.$panelSystem.'
	</div>
	<div class="column-clear"></div>
</div>
';
}
?>
