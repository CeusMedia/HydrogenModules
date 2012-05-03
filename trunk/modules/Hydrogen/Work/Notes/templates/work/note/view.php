<?php
$panelLinks	= '';
if( count( $note->links ) ){
	$list	= array();
	foreach( $note->links as $item ){
		$label	= '<span class="link untitled">'.$item->url.'</span>';
		if( $item->title )
			$label	= '<span class="link titled"><acronym title="'.$item->url.'">'.$item->title.'</acronym></span>';
		$url	= './work/note/link/'.$item->linkId;
		$link	= '<a href="'.$url.'">'.$label.'</a>';
		$list[]	= '<li class="link">'.$link.'</li>';
	}
	$listLinks	= '<ul class="links-list">'.join( $list ).'</ul><div style="clear: left"></div>';
	$panelLinks	= '
<fieldset>
	<legend class="icon link">Links</legend>
		<div class="note-links">'.$listLinks.'</div>
</fieldset>';
}


$panelTags	= '';
if( count( $note->tags ) ){
	$list	= array();
	foreach( $note->tags as $tag ){
		$label	= '<span class="tag">'.$tag->content.'</span>';
		$list[]	= '<li class="tag">'.$label.'</li>';
	}
	$listTags	= '<ul class="tags-list">'.join( $list ).'</ul><div style="clear: left"></div>';
	$panelTags		= '
<fieldset>
	<legend class="icon tag">Tags</legend>
	<div class="note-tags">'.$listTags.'</div>
</fieldset>';
}

$converter	= new View_Helper_ContentConverter();
$content	= $converter->convert( stripslashes( $note->content ) );

function getShortHash( $noteId ){
	$hash	= base64_encode( $noteId );
	$hash	= str_replace( '=', '', $hash );
	return $hash;
}

$shortHash	= getShortHash( $note->noteId );
#$config->set( 'app.base.url', 'kb.ceusmedia.de/');
$shortUrl	= $config->get( 'app.base.url' ).'?'.$shortHash;

return '
<div class="column-left-75">
	<form id="form_edit_note" name="edit_note" action="./work/note/edit/'.$note->noteId.'" method="post">
		<fieldset>
			<legend class="icon note">'.htmlentities( $note->title, ENT_QUOTES ).'</legend>

			<div class="note-view-content">'.$content.'</div>
			<div class="buttonbar">
				'.UI_HTML_Elements::LinkButton( './work/note', 'zur Liste', 'button cancel back left' ).'
				'.UI_HTML_Elements::LinkButton( './work/note/edit/'.$note->noteId, 'bearbeiten', 'button edit' ).'
			</div>
		</fieldset>
	</form>
</div>
<div class="column-left-25">
	'.$panelTags.'
	'.$panelLinks.'
	<fieldset>
		<legend class="icon info">Info</legend>
		<a href="./?'.$shortHash.'">Kurzlink</a><br/>
		'.UI_HTML_Elements::Input( NULL, $shortUrl, 'small', TRUE ).'
		<dl>
			<dt>Views</dt>
			<dd>'.$note->numberViews.'</dd>
		</dl>
	</fieldset>
</div>
<div class="column-clear"></div>
';
?>
