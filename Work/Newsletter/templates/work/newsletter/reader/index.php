<?php

$tabsMain		= $tabbedLinks ? $this->renderMainTabs() : '';

extract( $view->populateTexts( ['above', 'bottom', 'top'], 'html/work/newsletter/reader/index/', ['words' => $words] ) );

$modalImportList	= $view->loadTemplateFile( 'work/newsletter/reader/modal.import.list.php' );
$modalImportCsv		= $view->loadTemplateFile( 'work/newsletter/reader/modal.import.csv.php' );
$panelFilter		= $view->loadTemplateFile( 'work/newsletter/reader/index.filter.php' );
$panelList			= $view->loadTemplateFile( 'work/newsletter/reader/index.list.php' );

return $textTop.'
<div class="newsletter-content">
	'.$tabsMain.'
	'.$textAbove.'
	'.$panelList.'
</div>
'.$textBottom.$modalImportList.$modalImportCsv;


return $textTop.'
<div class="newsletter-content">
	'.$tabsMain.'
	'.$textAbove.'
<!--	<h3>'.$words->index->heading.'</h3>-->
	<div class="row-fluid">
		<div class="span3">
			'.$panelFilter.'
		</div>
		<div class="span9">
			'.$panelList.'
		</div>
	</div>
	<br/>
</div>
'.$textBottom.$modalImportList.$modalImportCsv;
