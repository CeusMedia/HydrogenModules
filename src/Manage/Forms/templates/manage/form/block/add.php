<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */

$iconList	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
$iconSave	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );

$env->getPage()->js->addScriptOnReady('FormEditor.applyAceEditor("#input_content");');

return '
<h2><span class="muted">Block:</span> Neu</h2>
<div class="content-panel">
	<div class="content-panel-inner">
		<form action="./manage/form/block/add" method="post">
			<div class="row-fluid">
				<div class="span6">
					<label for="input_title">Titel</label>
					<input type="text" name="title" id="input_title" class="span12"/>
				</div>
				<div class="span6">
					<label for="input_identifier">Shortcode</label>
					<input type="text" name="identifier" id="input_identifier" class="span12"/>
				</div>
			</div>
			<div class="row-fluid" style="margin-bottom: 1em">
				<div class="span12">
					<label for="input_content">Inhalt</label>
					<textarea name="content" id="input_content" class="span12" rows="20"></textarea>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./manage/form/block" class="btn">'.$iconList.'&nbsp;zur Liste</a>
				<button type="submit" name="save" class="btn btn-primary">'.$iconSave.'&nbsp;speichern</button>
			</div>
		</form>
	</div>
</div>
<style>
.ace_editor {
	border: 1px solid rgba(127, 127, 127, 0.5);
	border-radius: 4px;
	padding: 6px;
	}
</style>
';
