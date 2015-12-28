<?php

$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;neue Seite', array(
	'href'	=> './manage/page/add',
	'class'	=> 'btn btn btn-primary'
) );

$panelTree	= $view->loadTemplateFile( 'manage/page/tree.php' );

//  --  LAYOUT  --  //
return '
<div class="row-fluid">
	<div id="manage-page-tree" class="span3">
		'.$panelTree.'
	</div>
	<div id="manage-page-main" class="span9">
		<div style="float: left; width: 100%">
			<h4>Seitenverwaltung</h4>
			<p>
				Links siehst du die Struktur deiner Webseite.<br/>
				Hier kannst du nun:
				<ul>
					<li>die Reihenfolge der Seiten verändern</li>
					<li>den Inhalt (statischer) Seiten verändern</li>
					<li>Seiten verstecken oder veröffentlichen</li>
					<li>neue Seiten hinzufügen</li>
				</ul>
			</p>
			'.$buttonAdd.'
		</div>
	</div>
</div>';
?>
