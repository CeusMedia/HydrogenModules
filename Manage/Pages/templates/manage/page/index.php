<?php

$listPages	= $this->renderTree( $tree, NULL );

$optScope	= array();
foreach( $words['scopes'] as $key => $value )
	$optScope[$key]	= $value;
$optScope	= UI_HTML_Elements::Options( $optScope, $scope );

$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;neue Seite', array(
	'href'	=> './manage/page/add',
	'class'	=> 'btn btn btn-primary'
) );

//  --  LAYOUT  --  //
return '
<div class="row-fluid">
	<div id="manage-page-tree" class="span3">
		<div>
			<label for="input_scope">Navigationstyp</label>
			<a href="./manage/page/add" class="btn btn-mini btn-primary pull-right">'.$iconAdd.'</a>
			<select class="span10" name="scope" id="input_scope" class="span10" onchange="document.location.href=\'./manage/page/setScope/\'+this.value;">'.$optScope.'</select>
		</div>
		'.$listPages.'
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
</div>
';

?>
