<?php
return '
<h4>Neuer Beitrag zum Thema "'.$thread->title.'"</h4>
<form action="./info/forum/addPost/'.$thread->threadId.'" method="post">
	<div class="row-fluid">
		<div class="span12">
			<label for="input_content">Inhalt</label>
			<textarea name="content" id="input_content" rows="10" class="span12">'.$request->get( 'content' ).'</textarea>
		</div>
	</div>
	<div class="buttonbar">
		<a href="./info/forum/topic/'.$thread->topicId.'" class="btn btn-small"><i class="icon-arrow-left"></i> zurück</a>
		<button type="submit" name="save" value="1" class="btn btn-small btn-success"><i class="icon-ok icon-white"></i> speichern</button>
	</div>
</form>
';
?>