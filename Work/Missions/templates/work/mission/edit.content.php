<?php
/*  THIS TEMPLATE IS USED FOR MARKDOWN - NOT FOR HTML  */
if( strtoupper( $format ) === "HTML" )
	return '';

$mode	= "splitted";
$mode	= "tabbed";

$w	= (object) $words['edit-content'];

if( $mode === "tabbed" ){
	$linkMarkdown	= UI_HTML_Tag::create( 'a', $w->hintMarkdownLabel, array(
		'href'		=> $w->hintMarkdownLink,
		'target'	=> '_blank',
	) );
	$hintMarkdown	= UI_HTML_Tag::create( 'small', sprintf( $w->hintMarkdown, $linkMarkdown ), array( 'class' => 'muted' ) );
	$panelContentTabbed	= '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<ul class="nav nav-tabs">
			<li class="active"><a href="#tab1" data-toggle="tab">'.$w->tabEdit.'</a></li>
			<li><a href="#tab2" data-toggle="tab">'.$w->tabView.'</a></li>
			<li><a href="#tab3" data-toggle="tab">'.$w->tabConvert.'</a></li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active" id="tab1">
				<div id="mirror-container">
					<textarea id="input_content" name="content" rows="22" class="span12 -max -cmGrowText -cmClearInput">'.htmlentities( $mission->content, ENT_QUOTES, 'utf-8' ).'</textarea>
					<p>
						'.$hintMarkdown.'
					</p>
				</div>
			</div>
			<div class="tab-pane" id="tab2">
				<div id="content-editor">
					<div id="mission-content-html"></div>
				</div>
			</div>
			<div class="tab-pane" id="tab3">
				<div class="row-fluid">
					<div class="span6">
						<p>
							Diese Beschreibung ist im <a href="http://de.wikipedia.org/wiki/Markdown" target="_blank">Markdown</a>-Format.
							Alternativ dazu kann dieser Text auch mit einem HTML-Editor bearbeitet werden.
						</p>
						<p>
							Dazu kann man diese Beschreibung von Markdown in HTML.
							Bei zukünftigen Veränderungen wird immer der HTML-Editor angezeigt.
							Das betrifft auch ggfs. Andere, die diesen Eintrag bearbeiten können.
						</p>
					</div>
					<div class="span6">
						<div class="alert alert-danger">
							<b>Achtung: </b>Dieser Vorgang kann nicht rückgängig gemacht werden.<br/>
						</div>
						<p>
							<a href="./work/mission/convertContent/'.$mission->missionId.'/markdown/html" class="btn btn-inverse">Konvertieren: Markdown -> HTML</a>
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>';
	return $panelContentTabbed;
}

$panelContentSplitted	= '
<div class="row-fluid">
	<div class="span6">
		<div class="content-panel content-panel-form">
			<div class="content-panel-inner">
				<form>
					<h3>Editor</h3>
					<div id="work-missions-loader" style=""><em class="muted">... lade Inhalte ...</em></div>
					<textarea id="input_content" name="content" rows="4" class="span12 -max -cmGrowText -cmClearInput" style="visibility: hidden">'.htmlentities( $mission->content, ENT_QUOTES, 'utf-8' ).'</textarea>
					<p>
						<span class="muted">Du kannst hier den <a href="http://de.wikipedia.org/wiki/Markdown" target="_blank">Markdown-Syntax</a> benutzen.</span>
					</p>
				</form>
			</div>
		</div>
	</div>
	<div class="span6">
		<div class="content-panel content-panel-form">
			<div class="content-panel-inner">
				<h3>Beschreibung / Inhalt</h3>
				<div id="content-editor">
					<div id="mission-content-html"></div>
				</div>
			</div>
		</div>
	</div>
</div>';
return $panelContentSplitted;
?>
