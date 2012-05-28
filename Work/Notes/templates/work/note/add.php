<?php

$w		= (object) $words['add'];
$text	= $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/work/note/add.' );

return '
<div class="note-add column-left-75">
	<form name="note_add" id="form_note_add" action="./work/note/add" method="post">
		<fieldset>
			<legend class="comment-add">'.$w->legend.'</legend>
			<ul class="input">
				<li class="column-left-50">
					<label for="input_note_title" class="mandatory">'.$w->labelTitle.'</label><br/>
					<input type="text" id="input_note_title" name="note_title" class="mandatory max" value="'.htmlentities( $note->title, ENT_QUOTES ).'"/>
				</li>
				<li class="column-clear">
					<label for="input_note_content">'.$w->labelContent.'</label><br/>
					<textarea id="input_note_content" name="note_content" class="max" rows="16"></textarea>
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
<div class="note-add column-left-25">
	'.$text['info'].'
</div>
<div class="column-clear"></div>
';
?>
