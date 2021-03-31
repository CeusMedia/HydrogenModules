<?php
$tabsMain	= $tabbedLinks ? $this->renderMainTabs() : '';

extract( $view->populateTexts( array( 'index.top', 'index.bottom' ), 'html/work/newsletter/dashboard/' ) );

return '
'.$tabsMain.'
'.$textIndexTop.'
<h3>Übersicht</h3>
<div class="row-fluid">
	<div class="span3">
		<h4>Aufgaben</h4>
		<em><small class="muted">Hier kommen ein paar statistische Angaben.</small></em>
	</div>
	<div class="span3">
		<h4>Fakten über Leser</h4>
		<ul>
			<li><a href="./work/newsletter/reader/filter/reset">'.( $readers[-1] + $readers[0] + $readers[1] ).' gesamt</a></li>
			<li><a href="./work/newsletter/reader/filter/reset?filter&status=1">'.$readers[1].' aktiviert</a></li>
			<li><a href="./work/newsletter/reader/filter/reset?filter&status=0">'.$readers[0].' neu</a></li>
			<li><a href="./work/newsletter/reader/filter/reset?filter&status=-1">'.$readers[-1].' abgemeldet</a></li>
		</ul>
	</div>
	<div class="span3">
		<h4>Fakten über Gruppen</h4>
		<em><small class="muted">Hier kommen ein paar statistische Angaben.</small></em>
	</div>
	<div class="span3">
		<h4>Fakten über Newsletters</h4>
		<em><small class="muted">Hier kommen ein paar statistische Angaben.</small></em>
	</div>
</div>'.$textIndexBottom;
