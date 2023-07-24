<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconCancel	= HtmlTag::create( 'i', '', ['class' => 'icon-arrow-left'] );
$iconSave	= HtmlTag::create( 'i', '', ['class' => 'icon-check icon-white'] );

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.' zurück', [
	'href'	=> './manage/ip/lock/reason',
	'class'	=> 'btn btn-small',
] );
$buttonSave		= HtmlTag::create( 'button', $iconSave.' speichern', [
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary',
] );

$optStatus	= HtmlElements::Options( [
	1		=> 'aktiv',
	0		=> 'inaktiv',
] );

$panelAdd	= '
<div class="content-panel">
	<h3><a class="muted" href="./manage/ip/lock/reason">IP-Lock-Grund:</a> Neu</h2>
	<div class="content-panel-inner">
		<form action="./manage/ip/lock/reason/add" method="post">
			<div class="row-fluid">
				<div class="span7">
					<label for="input_title" class="required mandatory">Titel</label>
					<input type="text" name="title" id="input_title" class="span12" required="required"/>
				</div>
				<div class="span1">
					<label for="input_code"><abbr title="HTTP-Status-Code">Code</abbr></label>
					<input type="text" name="code" id="input_code" class="span12" required="required"/>
				</div>
				<div class="span2">
					<label for="input_duration"><abbr title="in Sekunden">Dauer</abbr></label>
					<input type="text" name="pattern" id="input_duration" class="span12"/>
				</div>
				<div class="span2">
					<label for="input_status">Zustand</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_description">Beschreibung <small class="muted">(erscheint als Text auf der Fehlerseite)</small></label>
					<textarea name="description" class="span12" rows="5"></textarea>
				</div>
			</div>
			<div class="buttonbar">
				'.$buttonCancel.'
				'.$buttonSave.'
			</div>
		</form>
	</div>
</div>
';

$tabs	= View_Manage_Ip_Lock::renderTabs( $env, 'reason' );
return $tabs.HTML::DivClass( 'row-fluid',
	HTML::DivClass( 'span8',
		 $panelAdd
	).
	HTML::DivClass( 'span4' )
);
