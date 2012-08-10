<?php
//print_m( $article );
//die;

$optStatus	= array();
foreach( $words['states'] as $value => $label )
	$optStatus[$value]	= $label;
$optStatus	= UI_HTML_Elements::Options( $optStatus, $article->status );


$optAuthor	= array( '' => '- hinzufügen -');
foreach( $editors as $editor )
	foreach( $authors as $author )
		if( $author->userId != $editor->userId )
			$optAuthor[$editor->userId]	= $editor->username;
$optAuthor	= UI_HTML_Elements::Options( $optAuthor, NULL );


$listAuthors	= '<b><em>noch keine</em></b>';
$listTags		= '<b><em>noch keine</em></b>';

$list	= array();
if( $tags ){
	foreach( $tags as $tag ){
		$urlTag		= './blog/tag/'.urlencode( $tag->title );
		$urlRemove	= './blog/removeTag/'.$article->articleId.'/'.$tag->tagId;
		$linkTag	= UI_HTML_Elements::Link( $urlTag, $tag->title, 'link-tag' );
		$linkRemove	= UI_HTML_Elements::LinkButton( $urlRemove, '', 'button tiny remove' );
		$list[]	= UI_HTML_Elements::ListItem( $linkRemove.$linkTag );
	}
	$listTags	= UI_HTML_Tag::create( 'ul', join( $list ), array( 'class' => 'editor-list' ) );
}

$list	= array();
if( $authors ){
	foreach( $authors as $author ){
		$urlUser	= './admin/user/edit/'.$author->userId;
		$urlRemove	= './blog/removeAuthor/'.$article->articleId.'/'.$author->userId;
		$linkUser	= UI_HTML_Elements::Link( $urlUser, $author->username, 'article-author role role'.$author->roleId );
		$linkRemove	= UI_HTML_Elements::LinkButton( $urlRemove, '', 'button tiny remove' );
		$list[]	= UI_HTML_Elements::ListItem( $linkRemove.$linkUser );
	}
	$listAuthors	= UI_HTML_Tag::create( 'ul', join( $list ), array( 'class' => 'editor-list' ) );
}

$buttonStatusShow	= UI_HTML_Elements::LinkButton( './blog/setStatus/'.$article->articleId.'/1', 'veröffentlichen', 'button accept', NULL, $article->status <> 0 );
$buttonStatusHide	= UI_HTML_Elements::LinkButton( './blog/setStatus/'.$article->articleId.'/0', 'verstecken', 'button lock', NULL, $article->status == 0 );
$buttonStatusRemove	= UI_HTML_Elements::LinkButton( './blog/setStatus/'.$article->articleId.'/-1', 'entfernen', 'button remove reset', NULL, $article->status < 0 );
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
					<textarea name="content" id="input-content" rows="10" class="max CodeMirror">'.htmlentities( $article->content, ENT_QUOTES, 'UTF-8' ).'</textarea>
				</li>
				<li class="column-left-20">
					<label for="input-status">Status / Sichtbarkeit</label><br/>
					<select name="status" id="input-status" class="max">'.$optStatus.'</select>
				</li>
				<li class="column-left-20">
					<label for="input-date">Datum</label><br/>
					<input type="text" name="date" id="input-date" class="datepicker max" value="'.date( 'Y-m-d', $article->createdAt ).'"/>
				</li>
				<li class="column-left-10">
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
				&nbsp;&nbsp;|&nbsp;&nbsp;
				'.$buttonStatusShow.'
				'.$buttonStatusHide.'
				'.$buttonStatusRemove.'
			</div>
		</fieldset>
	</form>
	<div class="column-left-33">
		<form name="manageArticleTags" id="form-blogArticleTags" action="./blog/addTag/'.$articleId.'/" method="post">
			<fieldset>
				<legend>Schlagwörter</legend>
				'.$listTags.'
				<div class="buttonbar">
					<div style="float: left; width: 89%">
						<input type="text" name="tag" id="input-tag" class="max"/>
					</div>
					<div style="float: left; width: 11%; padding-top: 4px; text-align: right">
						<button type="submit" name="do" value="add" class="button tiny add"><span></span></button>
					</div>
					<em><small>(getrennt mit Leerzeichen)</small></em>
				</div>
			</fieldset>
		</form>
	</div>
	<div class="column-left-33">
		<fieldset>
			<legend>Autoren</legend>
				'.$listAuthors.'
				<div class="buttonbar">
					<select name="authorId" id="input-authorId" class="max">'.$optAuthor.'</select>
				</div>
		</fieldset>
	</div>
	<div class="column-clear"></div>
</div>
<script>
$(document).ready(function(){
	$(".datepicker").datepicker({dateFormat: "yy-mm-dd"});

	$("#input-content").bind("change",function(){			//  @todo	this only works without CodeMirror
		if($(this).val().length && $("#button-save").attr("disabled"))
			$("#button-save").removeAttr("disabled");
		else
			$("#button-save").attr("disabled", "disabled");
	}).trigger("change");

	$("#input-authorId").bind("change",function(){
		var url = "./blog/addAuthor/'.$articleId.'/"+$(this).val();
		document.location.href = url;
	})
});
</script>
';

?>