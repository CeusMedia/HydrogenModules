<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\HydrogenFramework\View;

/** @var array $words */
/** @var View $view */
/** @var object $project */

$w		= (object) $words['add'];
extract( $view->populateTexts( ['top', 'info', 'bottom'], 'html/work/note/add.' ) );

$optProject	= ['0' => '- ohne Projektbezug -'];
foreach( $projects as $project )
	$optProject[$project->projectId]	= $project->title;
$optProject	= HtmlElements::Options( $optProject, $note->projectId );

$optFormat	= [];
foreach( $words['formats'] as $formatKey => $formatLabel )
	$optFormat[$formatKey]	= $formatLabel;
$optFormat	= HtmlElements::Options( $optFormat, $note->format );

return $textTop.'
<div class="row-fluid">
	<div class="span9 note-add -column-left-75">
		<div class="content-panel content-panel-form">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				<form name="note_add" id="form_note_add" action="./work/note/add" method="post">
					<div class="row-fluid">
						<div class="span8">
							<label for="input_note_title" class="mandatory">'.$w->labelTitle.'</label>
							<input type="text" id="input_note_title" name="note_title" class="mandatory span12" value="'.htmlentities( $note->title, ENT_QUOTES ).'"/>
						</div>
						<div class="span4">
							<label for="input_note_projectId">'.$w->labelProjectId.'</label>
							<select id="input_note_projectId" name="note_projectId" class="span12">'.$optProject.'</select>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span3">
							<label for="input_note_format">'.$w->labelFormat.'</label>
							<select id="input_note_format" name="note_format" class="span12">'.$optFormat.'</select>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_note_content">'.$w->labelContent.'</label>
							<textarea id="input_note_content" name="note_content" class="span12 CodeMirror-auto" rows="10"></textarea>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span4">
							<label for="input_tags">'.$w->labelTags.'</label>
							<input type="text" id="input_tags" name="tags" class="span12" value="'.htmlentities( $note->tags, ENT_QUOTES ).'"/>
						</div>
						<div class="span4">
							<label for="input_link_url">'.$w->labelLinkUrl.'</label>
							<input type="text" id="input_link_url" name="link_url" class="span12" value="'.htmlentities( $note->link_url, ENT_QUOTES ).'"/>
						</div>
						<div class="span4">
							<label for="input_link_title">'.$w->labelLinkTitle.'</label>
							<input type="text" id="input_link_title" name="link_title" class="span12" value="'.htmlentities( $note->link_title, ENT_QUOTES ).'"/>
						</div>
					</div>
					<div class="buttonbar">
						<a href="./work/note" class="btn not-btn-small"><i class="icon-arrow-left"></i> '.$w->buttonCancel.'</a>
						<button type="submit" name="save" class="btn not-btn-small btn-success"><i class="icon-ok icon-white"></i> '.$w->buttonAdd.'</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="note-add span3 -column-left-25">
		'.$textInfo.'
	</div>
</div>
<div class="column-clear"></div>
'.$textBottom;
