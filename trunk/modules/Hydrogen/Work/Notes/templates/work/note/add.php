<?php
return '
<div class="note-add column-left-75">
	<form name="note_add" id="form_note_add" action="./work/note/add" method="post">
		<fieldset>
			<legend class="comment-add">Neue Notiz</legend>
			<ul class="input">
				<li>
					<label for="input_note_title" class="mandatory">Titel</label><br/>
					<input type="text" id="input_note_title" name="note_title" class="mandatory max" value="'.htmlentities( $note->title, ENT_QUOTES ).'"/>
				</li>
				<li class="column-left-50">
					<label for="input_link_url">Link-Adresse</label><br/>
					<input type="text" id="input_link_url" name="link_url" class="max" value="'.htmlentities( $note->link_url, ENT_QUOTES ).'"/>
				</li>
				<li class="column-left-50">
					<label for="input_link_title">Link-Title</label><br/>
					<input type="text" id="input_link_title" name="link_title" class="max" value="'.htmlentities( $note->link_title, ENT_QUOTES ).'"/>
				</li>
				<li>
					<label for="input_note_content">Text</label><br/>
					<textarea id="input_note_content" name="note_content" class="max" rows="6"></textarea>
				</li>
				<li class="column-clear">
					<label for="input_tags">Tags <small><em>(Schlagwörter mit Leerzeichen getrennt)</em></small></label><br/>
					<input type="text" id="input_tags" name="tags" class="max" value="'.htmlentities( $note->tags, ENT_QUOTES ).'"/>
				</li>
			</ul>
			<div class="buttonbar">
				'.UI_HTML_Elements::LinkButton( './work/note', 'zur Liste', 'button cancel back left' ).'
				<button type="submit" name="do" value="addNote" class="button save"><span>save</span></button>
			</div>
		</fieldset>
	</form>
</div>
';
?>
