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
$listVersions	= '<b><em>noch keine</em></b>';

$list	= array();
if( $tags ){
	foreach( $tags as $tag ){
		$url		= './blog/removeTag/'.$article->articleId.'/'.$tag->tagId;
		$linkRemove	= UI_HTML_Elements::LinkButton( $url, '', 'button tiny remove' );
		$linkTag	= View_Helper_Blog::renderTagLink( $env, $tag->title );
		$list[]		= UI_HTML_Elements::ListItem( $linkRemove.$linkTag );
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

$list	= array();
if( $article->versions ){
	$label	= 'Version '.( count( $article->versions ) + 1 );
	$link	= UI_HTML_Elements::Link( './blog/edit/'.$article->articleId, $label, 'version latest' );
	$class	= $version != count( $article->versions ) ? 'current' : NULL;
	$list[]	= UI_HTML_Elements::ListItem( $link, 0, array( 'class' => $class ) );
	foreach( $article->versions as $nr => $articleVersion ){
		$label	= 'Version '.++$nr;
		$url	= './blog/edit/'.$article->articleId.'/'.$nr;
		$class	= $version == $nr ? 'current' : NULL;
		$link	= UI_HTML_Elements::Link( $url, $label, 'version' );
		$list[]	= UI_HTML_Elements::ListItem( $link, 0, array( 'class' => $class ) );
	}
	$listVersions	= UI_HTML_Tag::create( 'ul', join( $list ), array( 'class' => 'not-editor-list versions' ) );
}

$isHistory			= $version <= count( $article->versions );

$buttonCancel		= UI_HTML_Elements::LinkButton( './blog/article/'.$articleId, 'zurück', 'button cancel' );
$buttonSave			= UI_HTML_Elements::Button( 'save', 'speichern', 'button save', NULL, $isHistory );
$buttonStatusShow	= UI_HTML_Elements::LinkButton( './blog/setStatus/'.$article->articleId.'/1', 'veröffentlichen', 'button accept', NULL, $article->status <> 0 || $isHistory );
$buttonStatusHide	= UI_HTML_Elements::LinkButton( './blog/setStatus/'.$article->articleId.'/0', 'verstecken', 'button lock', NULL, $article->status == 0 || $isHistory );
$buttonStatusRemove	= UI_HTML_Elements::LinkButton( './blog/setStatus/'.$article->articleId.'/-1', 'entfernen', 'button remove reset', NULL, $article->status < 0 || $isHistory );

$dateLastModified	= $article->modifiedAt ? date( 'Y-m-d H:i:s', $article->modifiedAt ) : '-';

if( $version > 0 && $version < $article->version ){
	$nr	= $version - 1;
	$article->title			= $article->versions[$nr]->title;
	$article->content		= $article->versions[$nr]->content;
	$article->createdAt		= $article->versions[$nr]->createdAt;
	$article->modifiedAt	= $article->versions[$nr]->modifiedAt;
	$article->version		= $version;
}

$title		= htmlentities( $article->title, ENT_QUOTES, 'UTF-8' );
$content	= htmlentities( $article->content, ENT_QUOTES, 'UTF-8' );
$date		= date( 'Y-m-d', $article->createdAt );
$time		= date( 'H:i', $article->createdAt );

return '
<style>
.versions .current {
	font-weight: bold;
	}
code {
	font-size: 0.8em;
	}
</style>
<div id="blog-edit-form">
	<form name="editArticle" id="form-blogArticleEdit" action="./blog/edit/'.$articleId.'" method="post">
		<fieldset>
			<legend class="messages">Artikel verändern</legend>
			<ul class="input">
				<li>
					<label for="input-title">Titel</label><br/>
					<input type="text" name="title" id="input-title" class="max" value="'.$title.'"/>
				</li>
				<li>
					<label for="input-title">Inhalt</label><br/>
					<textarea name="content" id="input-content" rows="30" class="max CodeMirror-auto">'.$content.'</textarea>
				</li>
				<li class="column-left-20">
					<label for="input-status">Status / Sichtbarkeit</label><br/>
					<select name="status" id="input-status" class="max">'.$optStatus.'</select>
				</li>
				<li class="column-left-20">
					<label for="input-date">Datum</label><br/>
					<input type="text" name="date" id="input-date" class="datepicker max" value="'.$date.'"/>
				</li>
				<li class="column-left-10">
					<label for="input-time">Zeit</label><br/>
					<input type="text" name="time" id="input-time" class="timepicker max" value="'.$time.'"/>
				</li>
				<li class="column-left-20" style="padding-left: 1em;">
<!--					<b>Optionen:</b><br/>-->
					<label for="input-version">
						<input type="checkbox" name="version" id="input-version" value="1"/>
						&nbsp;neue Version
					</label><br/>
					<label for="input-now">
						<input type="checkbox" name="now" id="input-now" value="1"/>
						&nbsp;aktuelle Zeit setzen
					</label><br/>
				</li>
				<li class="column-right-20" style="display: '.( $article->modifiedAt ? 'block' : 'none' ).'">
					<label for="input-date">zuletzt geändert</label><br/>
					<b style="line-height: 2em">'.$dateLastModified.'</b>
				</li>
			</ul>
			<div class="buttonbar">
				'.$buttonCancel.'
				&nbsp;&nbsp;|&nbsp;&nbsp;
				'.$buttonSave.'
				&nbsp;&nbsp;&nbsp;<b>oder</b>&nbsp;&nbsp;
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
	<div class="column-left-33">
		<fieldset>
			<legend>Versionen</legend>
				'.$listVersions.'
		</fieldset>
	</div>
	<div class="column-clear"></div>
</div>
<script>
var blogArticleId = '.(int) $articleId.';
var blogIsHistory = '.(int) $isHistory.';
$(document).ready(function(){
	$(".datepicker").datepicker({dateFormat: "yy-mm-dd"});

	$("#input-content").bind("change",function(){			//  @todo	this only works without CodeMirror
		if($(this).val().length && $("#button-save").attr("disabled"))
			$("#button-save").removeAttr("disabled");
		else
			$("#button-save").attr("disabled", "disabled");
	}).trigger("change");

	$("#input-authorId").bind("change",function(){
		var url = "./blog/addAuthor/"+blogArticleId+"/"+$(this).val();
		document.location.href = url;
	})

	if(blogIsHistory){
		$("#form-blogArticleEdit :input").each(function(){
			var id = $(this).attr("disabled","disabled").attr("id");
		});
	}

	$("#input-now").bind("change",function(){
		if($(this).is(":checked")){
			$("#input-date").attr("disabled", "disabled");
			$("#input-time").attr("disabled", "disabled");
		}
		else{
			$("#input-date").removeAttr("disabled");
			$("#input-time").removeAttr("disabled");
		}
	});

	$(window).keydown(function(event){
		if(event.ctrlKey){														//  control key is pressed
			if(event.which == 81){												//  ctrl+q
				event.preventDefault();											//  prevent default browser behaviour
				document.location.href = "./blog/article/"+blogArticleId;		//  redirect to article view
			}
			if(event.which == 83 && !blogIsHistory){							//  ctrl+s is pressed and not viewing old version
				event.preventDefault();											//  prevent default browser behaviour
				var input = $("#input-content");								//  shortcut textarea
				var mirror = $("div.CodeMirror-focused");						//  shortcut code mirror
				if(mirror.size())												//  code mirror is enabled
					input.data("codemirror").save();							//  save code mirror content to textarea
				input.add(mirror).css("opacity", 0.5);							//  dim textarea and code mirror
				$.ajax({														//  save content using AJAX
					url: "./blog/setContent/"+blogArticleId,					//  controller action and article ID
					data: {content: input.val()},								//  send content ...
					type: "post",												//  ... via POST
					success: function(response){								//  
						$("div.CodeMirror-focused").css("opacity", 1);			//  reset code mirror opacity
						$("#input-content").css("opacity", 1);					//  reset textarea opacity
					}
				});
				return false;													//  
			}
		}
	});
});
</script>
';

?>
