<?php

$w		= (object) $words['edit'];
$text	= $this->populateTexts( array( 'top', 'info', 'bottom' ), 'html/work/note/edit.' );

$iconAdd	= '<img src="http://img.int1a.net/famfamfam/silk/add.png" title="zuweisen"/>';
$iconRemove	= '<img src="http://img.int1a.net/famfamfam/silk/delete.png" title="entfernen"/>';

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
	$listRelatedTags	= '<br/><label>Vorschläge</label><ul class="tags-list">'.join( $list ).'</ul>';
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
						'.UI_HTML_Elements::Button( 'save', $w->buttonAddTag, 'button add' ).'
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
<form action="./work/note/addLink/'.$note->noteId.'" method="post">
	<fieldset>
		<legend class="icon link">Links</legend>
		'.$listLinks.'
		<div style="clear: left"></div><br/>
		<ul class="input">
			<li>
				<label for="input_link_url" class="mandatory">Link-Adresse</label><br/>
				<input type="text" name="link_url" id="input_link_url" class="max mandatory"/>
			</li>
			<li>
				<label for="input_link_title">Link-Titel</label><br/>
				<input type="text" name="link_title" id="input_link_title" class="max"/>
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::Button( 'save', $w->buttonAddLink, 'button add' ).'
		</div>
	</fieldset>
</form>
';

$panelEdit	= '
	<form id="form_edit_note" name="edit_note" action="./work/note/edit/'.$note->noteId.'" method="post">
		<fieldset>
			<legend class="icon note-edit">'.$w->legend.'</legend>
			<ul class="input">
				<li class="column-left-50">
					<label for="input_note_title" class="mandatory">'.$w->labelTitle.'</label><br/>
					<input type="text" name="note_title" id="input_note_title" class="max mandatory" value="'.htmlentities( $note->title, ENT_QUOTES ).'"/>
				</li>
				<li class="column-clear">
					<label for="input_note_content">'.$w->labelContent.'</label><br/>
					<textarea name="note_content" id="input_note_content" rows="20">'.htmlentities( $note->content, ENT_QUOTES ).'</textarea>
				</li>
			</ul>
			<div class="buttonbar">
				'.UI_HTML_Elements::LinkButton( './work/note', $w->buttonCancel, 'button cancel back left' ).'
				'.UI_HTML_Elements::LinkButton( './work/note/view/'.$note->noteId, $w->buttonView, 'button cancel back left' ).'
				'.UI_HTML_Elements::Button( 'save', $w->buttonSave, 'button save' ).'
			</div>
		</fieldset>
	</form>
';

return '
<div class="column-left-75">
	'.$panelEdit.'
</div>
<div class="column-left-25">
	'.$panelTags.'
	'.$panelLinks.'
</div>
<div class="column-clear"></div>
';
?>
