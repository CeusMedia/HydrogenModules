<?php
$iconAdd	= '<img src="http://icons.ceusmedia.de/famfamfam/silk/add.png" title="zuweisen"/>';
$iconRemove	= '<img src="http://icons.ceusmedia.de/famfamfam/silk/delete.png" title="entfernen"/>';

//  --  TAG MANAGEMENT  --  //
$listTags	= "";
if( $note->tags ){
	$list	= array();
	foreach( $note->tags as $tag ){
		$url	= './work/note/removeTag/'.$note->noteId.'/'.$tag->tagId;
		$label	= '<div class="item-tag-label">'.$tag->content.'</div>';
//		$count	= '<div class="number-indicator">'.$tag->relevance.'</div>';
		$button	= '<button type="button" class="button tiny remove" onclick="document.location=\''.$url.'\';">'.$iconRemove.'</button>';
		$tray	= '<div class="item-tag-tray">'.$button.'</div>';
		$list[]	= '<li class="item-tag-extended border-bottom">'.$label.$tray.'</li>';
	}
	$listTags	= '<ul class="tags-list">'.join( $list ).'</ul>';
}

$listRelatedTags	= "";
if( $relatedTags ){
	$list	= array();
	$relatedTags	= array_slice( $relatedTags, 0, 5 );
	foreach( $relatedTags as $tag ){
		$url	= './work/note/addTag/'.$note->noteId.'/'.$tag->tagId;
		$label	= '<div class="item-tag-label">'.$tag->content.'</div>';
		$count	= '<div class="number-indicator">'.$tag->relevance.'</div>';
		$button	= '<button type="button" class="button tiny add" onclick="document.location=\''.$url.'\';">'.$iconAdd.'</button>';
		$tray	= '<div class="item-tag-tray">'.$count.$button.'</div>';
		$list[]	= '<li class="item-tag-extended border-top">'.$label.$tray.'</li>';
	}
	$listRelatedTags	= '<h3>Vorschläge</h3><ul class="tags-list">'.join( $list ).'</ul>';
}
$panelTags	= '
	<fieldset>
		<legend class="icon tag">Tags</legend>
		'.$listTags.'
		<div style="clear: left"></div>
		<form action="./work/note/addTag/'.$note->noteId.'" method="post">
			<ul class="input">
				<li>
<!--					<label class="column-left-30" for="input_tag_content">Neues Schlagwort</label>-->
					<div class="column-left-40">
						<input type="text" name="tag_content" id="input_tag_content" class="max">
					</div>
					<div class="column-left-60">
						<button type="submit" name="do" value="addArticleTag" class="button add"><span>hinzufügen</span></button>
					</div>
				</li>
			</button>
		</form>
		'.$listRelatedTags.'
	</fieldset>
';

//  --  LINK MANAGEMENT  --  //
$list	= array();
foreach( $note->links as $link ){
	$url	= './work/note/removeLink/'.$note->noteId.'/'.$link->linkId;
	$label	= '<span class="link untitled">'.$link->url.'</span>';
	if( $link->title )
		$label	= '<span class="link titled"><acronym title="'.$link->url.'">'.$link->title.'</acronym></span>';
	$list[]	= '<li class="link action-remove" onclick="document.location=\''.$url.'\';">'.$label.'</li>';
}
$listLinks	= '<ul class="links-list">'.join( $list ).'</ul>';
$panelLinks	= '
	<fieldset>
		<legend class="link">Links</legend>
		'.$listLinks.'
		<div style="clear: left"></div>
		<form action="./work/note/addLink/'.$note->noteId.'" method="post">
			<label for="input_link_url">Link-Adresse</label><br/>
			<input type="text" name="link_url" id="input_link_url">
			<label for="input_link_title">Link-Titel</label><br/>
			<input type="text" name="link_title" id="input_link_title">
			<button type="submit" name="do" value="addArticleLink" class="button add"><span>add</span></button>
		</form>
	</fieldset>
';

$panelEdit	= '
	<form id="form_edit_note" name="edit_note" action="./work/note/edit/'.$note->noteId.'" method="post">
		<fieldset>
			<legend class="comment-edit">Artikel</legend>
			<ul class="input">
				<li>
					<label for="input_note_title">Titel</label><br/>
					<input type="text" name="note_title" id="input_note_title" value="'.htmlentities( $note->title, ENT_QUOTES ).'"/>
				</li>
				<li>
					<label for="input_note_content">Inhalt</label><br/>
					<textarea name="note_content" id="input_note_content" rows="12">'.htmlentities( $note->content, ENT_QUOTES ).'</textarea>
				</li>
			</ul>
			<div class="buttonbar">
				'.UI_HTML_Elements::LinkButton( './work/note/view/'.$note->noteId, 'zur Ansicht', 'button cancel back left' ).'
				<button type="submit" name="do" value="editArticle" class="button save"><span>save</span></button>
			</div>
		</fieldset>
	</form>
';

return '
<div class="note-edit column-left-75">
	'.$panelEdit.'
</div>
<div class="note-tags column-right-25">
	'.$panelTags.'
	'.$panelLinks.'
</div>
<div class="column-clear"></div>
';
?>
