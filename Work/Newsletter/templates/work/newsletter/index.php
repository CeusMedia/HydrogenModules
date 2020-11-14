<?php

$tabsMain		= $tabbedLinks ? $this->renderMainTabs() : '';

$panelAdd		= $view->loadTemplateFile( 'work/newsletter/index.add.php' );
$panelFilter	= $view->loadTemplateFile( 'work/newsletter/index.filter.php', array( 'inlineFilter' => FALSE ) );
$panelList		= $view->loadTemplateFile( 'work/newsletter/index.list.php' );

extract( $view->populateTexts( array( 'above', 'bottom', 'top' ), 'html/work/newsletter', array( 'heading' => $words->index->heading ) ) );

return $textTop.'
<div class="newsletter-content">
	'.$tabsMain.'
	'.$textAbove.'
	'.$panelList.'
</div>
'.$textBottom.$panelAdd;

return $textTop.'
<div class="newsletter-content">
	'.$tabsMain.'
	'.$textAbove.'
	<div class="row-fluid">
		<div class="span3">
			'.$panelFilter.'
		</div>
		<div class="span9">
			'.$panelList.'
		</div>
	</div>
</div>
'.$textBottom;
?>
