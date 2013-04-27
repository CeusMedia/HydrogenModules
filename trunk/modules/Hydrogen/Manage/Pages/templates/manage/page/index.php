<?php

$listPages	= $this->renderTree( $tree, NULL );

//  --  LAYOUT  --  //
return '
<div>
	<div id="manage-page-tree">
		<h4>Seiten</h4>
		'.$listPages.'
	</div>
	<div id="manage-page-main">
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
			<button type="button" onclick="document.location.href=\'./manage/page/add\';" class="btn btn-small btn-info"><i class="icon-plus icon-white"></i> neue Seite</button>
		</div>
	</div>
</div>
';

?>
