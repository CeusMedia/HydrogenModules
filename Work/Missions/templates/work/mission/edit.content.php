<?php

$w	= (object) $words['edit'];

$panelContentSplitted	= '
<div class="row-fluid">
	<div class="span6">
		<div class="content-panel content-panel-form">
			<div class="content-panel-inner">
				<form>
					<h3>Editor</h3>
					<textarea id="input_content" name="content" rows="4" class="span12 -max -cmGrowText -cmClearInput">'.htmlentities( $mission->content, ENT_QUOTES, 'utf-8' ).'</textarea>
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
					<div id="descriptionAsMarkdown"></div>
				</div>
			</div>
		</div>
	</div>
</div>';

/*
<!--	<fieldset>
		<legend>Beschreibung / Mitschrift</legend>
		<div class="row-fluid">
			<div class="span12">
-->				<div class="tabbable">
					<ul class="nav nav-tabs">
						<li class="active"><a href="#tab1" data-toggle="tab">Ansicht</a></li>
						<li><a href="#tab2" data-toggle="tab">Editor</a></li>
					</ul>
					<div class="tab-content">
						<div class="tab-pane active" id="tab1">
							<div id="content-editor">
								<div id="descriptionAsMarkdown"></div>
							</div>
						</div>
						<div class="tab-pane" id="tab2">
							<div id="mirror-container">
<!--							<label for="input_content">'.$w->labelContent.'</label>-->
								<textarea id="input_content" name="content" rows="4" class="span12 -max -cmGrowText -cmClearInput">'.htmlentities( $mission->content, ENT_QUOTES, 'utf-8' ).'</textarea>
								<p>
									<span class="muted">Du kannst hier den <a href="http://de.wikipedia.org/wiki/Markdown" target="_blank">Markdown-Syntax</a> benutzen.</span>
								</p>
							</div>
						</div>
					</div>
				</div>
<!--			</div>
		</div>
	</fieldset>
--></form>
*/

return $panelContentSplitted.'
<script src="javascripts/bindWithDelay.js"></script>
<script>
$(document).ready(function(){
	WorkMissionEditor.init('.(int) $mission->missionId.');
});
</script>';
?>
