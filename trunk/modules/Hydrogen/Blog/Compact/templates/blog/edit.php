<?php
//print_m( $article );
//die;

$optStatus	= array();
foreach( $words['states'] as $value => $label )
	$optStatus[$value]	= $label;
$optStatus	= UI_HTML_Elements::Options( $optStatus, $article->status );

$tagList	= array();
foreach( $tags as $tag ){
	$url		= './blog/removeTag/'.$article->articleId.'/'.$tag->tagId;
	$link		= UI_HTML_Elements::Link( $url, $tag->title );
	$tagList[]	= $link;
//	$tagList[]	= UI_HTML_Elements::ListItem( $link );
}
//$tagList	= UI_HTML_Elements::unorderedList( $tagList );
$tagList	= $tagList ? join( ', ', $tagList ) : 'noch keine';

return '
<div id="blog-edit-form">
	<form name="editArticle" id="form-blogArticleEdit" action="./blog/edit/'.$articleId.'" method="post">
		<fieldset>
			<legend class="messages">Artikel verändern</legend>
			<ul class="input">
				<li>
					<label for="input-title">Titel</label><br/>
					<input type="text" name="title" id="input-title" value="'.htmlentities( $article->title ).'"/>
				</li>
				<li>
					<label for="input-title">Titel</label><br/>
					<textarea name="content" id="input-content" rows="10" class="max CodeMirror">'.$article->content.'</textarea>
				</li>
				<li>
					<label for="input-status">Sichtbarkeit</label><br/>
					<select name="status" id="input-status">'.$optStatus.'</select>
				</li>
			</ul>
			<div class="buttonbar">
				<button type="button" onclick="document.location.href=\'./blog/article/'.$articleId.'\';" class="button cancel"><span>zurück</span></button>&nbsp;&nbsp;|&nbsp;&nbsp;
				<button type="submit" name="do" value="save" class="button save"><span>speichern</span></button>
			</div>
		</fieldset>
	</form>
	<form name="addTagToArticle" id="form-blogArticleAddTag" action="./blog/addTag/'.$articleId.'/" method="post">
		<fieldset>
			<legend>Tags</legend>
			<div class="column-left-25">
				<label for="tag">neues Schlagwort</label><br/>
				<input type="text" name="tag" id="input-tag"/><br/>
				<button type="submit" name="do" value="add" class="button add"><span>add</span></button>
			</div>
			<div class="column-right-66">
				<label>vergebene Schlagwörter</label><br/>
				'.$tagList.'
			</div>
		</fieldset>
	</form>
</div>
';

?>