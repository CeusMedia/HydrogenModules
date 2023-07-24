<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var string $path */

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
$buttonCancel	= HtmlTag::create( 'a', $iconCancel.'&nbsp;zurück zur Liste', [
	'href'		=> './admin/database/backup',
	'class'		=> 'btn',
] );
$buttonSave		= HtmlTag::create( 'button', $iconSave.'&nbsp;Sicherung erstellen', [
	'type'		=> 'submit',
	'name'		=> 'save',
	'class'		=> 'btn btn-primary',
] );

return '
<div class="content-panel">
	<h3>Sicherung erstellen</h3>
	<div class="content-panel-inner">
		<form action="./admin/database/backup/backup" method="post">
		<p>Hierbei wird nur der Datenbank in der Datenbank gesichert.</p>
		<div class="row-fluid">
			<div class="span4">
				<label for="input_path">Speicherort</label>
				<input type="text" name="path" id="input_path" class="span12" readonly="readonly" value="'.htmlentities( $path, ENT_QUOTES, 'UTf-8' ).'"/>
			</div>
			<div class="span8">
				<label for="input_comment">Kommentar</label>
				<input type="text" name="comment" id="input_comment" class="span12"/>
			</div>
		</div>
		<div class="alert alert-info">
			Dieser Vorgang kann ein paar Sekunden dauern, je nach Größe der Datenbank.<br/>
			Bitte etwas Geduld und den Button nicht doppelt drücken!
		</div>
		<div class="buttonbar">
			'.$buttonCancel.'
			'.$buttonSave.'
		</div>
	</div>
</div>';
