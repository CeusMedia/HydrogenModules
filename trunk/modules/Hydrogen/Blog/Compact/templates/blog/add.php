<?php
$title		= $request->get( 'title' );
$content	= $request->get( 'content' );

return '
<div id="blog-edit-form">
	<form name="editArticle" id="form-blogArticleEdit" action="./blog/add" method="post">
		<fieldset>
			<legend class="messages-new">Artikel verändern</legend>
			<ul class="input">
				<li>
					<label for="title">Titel</label><br/>
					<input type="text" name="title" id="input-title" value="'.htmlentities( $title ).'"/>
				</li>
				<li>
					<label for="title">Titel</label><br/>
					<textarea name="content" id="input-content" rows="20" class="max CodeMirror">'.$content.'</textarea>
				</li>
			</ul>
			<div class="buttonbar">
				<button type="button" onclick="document.location.href=\'./blog\';" class="button cancel"><span>zurück</span></button>&nbsp;&nbsp;|&nbsp;&nbsp;
				<button type="submit" name="do" value="save" class="button save"><span>speichern</span></button>
			</div>
		</fieldset>
	</form>
</div>
';

?>