<?php
//print_m( $article );
//die;

$optStatus	= array();
foreach( $words['states'] as $value => $label )
	$optStatus[$value]	= $label;
$optStatus	= UI_HTML_Elements::Options( $optStatus, $article->status );


$optAuthor	= array();
foreach( $editors as $editor ){
	foreach( $authors as $author )
		if( $author->userId == $editor->userId )
			continue;
	$optAuthor[$editor->userId]	= $editor->username;
}
$optAuthor	= UI_HTML_Elements::Options( $optAuthor, NULL );

$tagList	= array();
foreach( $tags as $tag ){
	$url		= './blog/removeTag/'.$article->articleId.'/'.$tag->tagId;
	$link		= UI_HTML_Elements::Link( $url, $tag->title );
//	$tagList[]	= $link;
	$tagList[]	= UI_HTML_Elements::ListItem( $link );
}
//$tagList	= UI_HTML_Elements::unorderedList( $tagList );
$tagList	= $tagList ? join( $tagList ) : '<b><em>noch keine</em></b>';

$authorList	= array();
foreach( $authors as $author ){
	$url	= './blog/removeAuthor/'.$article->articleId.'/'.$author->userId;
	$link	= UI_HTML_Elements::Link( $url, $author->username, array( 'class' => 'article-author' ) );
	$authorList[]	= UI_HTML_Elements::ListItem( $link );
}
$authorList	= $authorList ? join( $authorList ) : '<b><em>noch keine</em></b>';

$buttonStatusHide	= UI_HTML_Elements::LinkButton( './blog/setStatus/'.$article->articleId.'/0', 'verstecken', 'button lock', NULL, $article->status == 0 );
$buttonStatusShow	= UI_HTML_Elements::LinkButton( './blog/setStatus/'.$article->articleId.'/1', 'veröffentlichen', 'button accept', NULL, $article->status == 1 );
$buttonRemove		= UI_HTML_Elements::LinkButton( './blog/remove/'.$article->articleId, 'entfernen', 'button remove', 'Wirklich?', $article->status > 0 );
$buttonCancel		= UI_HTML_Elements::LinkButton( './blog/article/'.$articleId, 'zurück', 'button cancel' );

return '
<div id="blog-edit-form">
	<form name="editArticle" id="form-blogArticleEdit" action="./blog/edit/'.$articleId.'" method="post">
		<fieldset>
			<legend class="messages">Artikel verändern</legend>
			<ul class="input">
				<li>
					<label for="input-title">Titel</label><br/>
					<input type="text" name="title" id="input-title" class="max" value="'.htmlentities( $article->title, ENT_QUOTES, 'UTF-8' ).'"/>
				</li>
				<li>
					<label for="input-title">Inhalt</label><br/>
					<textarea name="content" id="input-content" rows="10" class="max CodeMirror">'.$article->content.'</textarea>
				</li>
				<li class="column-left-20">
					<label for="input-status">Sichtbarkeit</label><br/>
					<select name="status" id="input-status" class="max">'.$optStatus.'</select>
				</li>
				<li class="column-left-20">
					<label for="input-date">Datum</label><br/>
					<input type="text" name="date" id="input-date" class="datepicker max" value="'.date( 'Y-m-d', $article->createdAt ).'"/>
				</li>
				<li class="column-left-20">
					<label for="input-time">Zeit</label><br/>
					<input type="text" name="time" id="input-time" class="timepicker max" value="'.date( 'H:i', $article->createdAt ).'"/>
				</li>
				<li class="column-right-20">
					<label for="input-date">zuletzt geändert</label><br/>
					<b style="line-height: 2em">'.date( 'Y-m-d H:i:s', $article->modifiedAt ).'</b>
				</li>
			</ul>
			<div class="buttonbar">
				'.$buttonCancel.'
				&nbsp;&nbsp;|&nbsp;&nbsp;
				<button type="submit" name="do" value="save" class="button save"><span>speichern</span></button>
				'.$buttonStatusShow.'
				&nbsp;&nbsp;|&nbsp;&nbsp;
				'.$buttonStatusHide.'
				'.$buttonRemove.'
			</div>
		</fieldset>
	</form>
	<div class="column-left-50">
		<form name="manageArticleTags" id="form-blogArticleTags" action="./blog/addTag/'.$articleId.'/" method="post">
			<fieldset>
				<legend>Tags</legend>
				<div class="column-left-50">
					<label>vergebene Schlagwörter</label><br/>
					'.$tagList.'
				</div>
				<div class="column-left-50">
					<label for="tag">neues Schlagwort</label><br/>
					<input type="text" name="tag" id="input-tag"/><br/>
					<button type="submit" name="do" value="add" class="button add"><span>add</span></button>
				</div>
			</fieldset>
		</form>
	</div>
	<div class="column-left-50">
		<fieldset>
			<legend>Autoren</legend>
			<div class="column-left-50">
				<label>zugewiesene Autoren</label><br/>
				'.$authorList.'
			</div>
			<div class="column-left-50">
				<label for="tag">neuer Autor</label><br/>
				<select name="authorId" id="input-authorId" class="max">'.$optAuthor.'</select><br/>
			</div>
		</fieldset>
	</div>
	<div class="column-clear"></div>
</div>
<script>
$(document).ready(function(){
	$(".datepicker").datepicker({dateFormat: "yy-mm-dd"});
	
	$("#input-authorId").bind("change",function(){
		var url = "./blog/addAuthor/'.$articleId.'/"+$(this).val();
		document.location.href = url;
	})
});
</script>
';

?>