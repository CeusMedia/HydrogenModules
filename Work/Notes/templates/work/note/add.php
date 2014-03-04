<?php

$w		= (object) $words['add'];
extract( $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/work/note/add.' ) );

$optProject	= array( '' => '' );
foreach( $projects as $project )
	$optProject[$project->projectId]	= $project->title;
$optProject	= UI_HTML_Elements::Options( $optProject, $note->projectId );

$optFormat	= array();
foreach( $words['formats'] as $formatKey => $formatLabel )
	$optFormat[$formatKey]	= $formatLabel;
$optFormat	= UI_HTML_Elements::Options( $optFormat, $note->format );

return $textTop.'
<div class="row-fluid">
	<div class="span9 note-add -column-left-75">
		<form name="note_add" id="form_note_add" action="./work/note/add" method="post">
			<fieldset>
				<legend class="comment-add">'.$w->legend.'</legend>
				<ul class="input">
					<li class="column-left-50">
						<label for="input_note_title" class="mandatory">'.$w->labelTitle.'</label>
						<input type="text" id="input_note_title" name="note_title" class="mandatory max" value="'.htmlentities( $note->title, ENT_QUOTES ).'"/>
					</li>
					<li class="column-left-25">
						<label for="input_note_projectId">'.$w->labelProjectId.'</label>
						<select id="input_note_projectId" name="note_projectId" class="max">'.$optProject.'</select>
					</li>
					<li class="column-right-20">
						<br/>
						<label for="input_note_public">
							<input type="checkbox" id="input_note_public" name="note_public" value="1" '.( $note->public ? 'checked="checked"' : '' ).'/>
							&nbsp;'.$w->labelPublic.'
						</label>
					</li>
					<li class="column-clear column-left-20">
						<label for="input_note_format">'.$w->labelFormat.'</label>
						<select id="input_note_format" name="note_format" class="max">'.$optFormat.'</select>
					</li>
					<li class="column-clear">
						<label for="input_note_content">'.$w->labelContent.'</label>
						<textarea id="input_note_content" name="note_content" class="max CodeMirror-auto" rows="16"></textarea>
					</li>
					<li class="column-clear column-left-30">
						<label for="input_tags">'.$w->labelTags.'</label><br/>
						<input type="text" id="input_tags" name="tags" class="max" value="'.htmlentities( $note->tags, ENT_QUOTES ).'"/>
					</li>
					<li class="column-left-40">
						<label for="input_link_url">'.$w->labelLinkUrl.'</label><br/>
						<input type="text" id="input_link_url" name="link_url" class="max" value="'.htmlentities( $note->link_url, ENT_QUOTES ).'"/>
					</li>
					<li class="column-left-30">
						<label for="input_link_title">'.$w->labelLinkTitle.'</label><br/>
						<input type="text" id="input_link_title" name="link_title" class="max" value="'.htmlentities( $note->link_title, ENT_QUOTES ).'"/>
					</li>
				</ul>
				<div class="buttonbar">
					'.UI_HTML_Elements::LinkButton( './work/note', $w->buttonCancel, 'button cancel back left' ).'
					'.UI_HTML_Elements::Button( 'add', $w->buttonAdd, 'button save' ).'
				</div>
			</fieldset>
		</form>
	</div>
	<div class="note-add span3 -column-left-25">
		'.$textInfo.'
	</div>
</div>
<div class="column-clear"></div>
'.$textBottom;
?>
