<?php
$panelLinks	= '';
if( count( $note->links ) ){
	$list	= array();
	foreach( $note->links as $item ){
		$label	= '<span class="link untitled">'.$item->url.'</span>';
		if( $item->title )
			$label	= '<span class="link titled"><acronym title="'.$item->url.'">'.htmlentities( $item->title, ENT_QUOTES, 'UTF-8' ).'</acronym></span>';
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
		$label	= '<span class="tag">'.htmlentities( $tag->content, ENT_QUOTES, 'UTF-8' ).'</span>';
		$list[]	= '<li class="tag">'.$label.'</li>';
	}
	$listTags	= '<ul class="tags-list">'.join( $list ).'</ul><div style="clear: left"></div>';
	$panelTags		= '
<fieldset>
	<legend class="icon tag">Tags</legend>
	<div class="note-tags">'.$listTags.'</div>
</fieldset>';
}

#$converter	= new View_Helper_ContentConverter();
$content	= View_Helper_ContentConverter::render( $env, $note->content );

function getShortHash( $noteId ){
	$hash	= base64_encode( $noteId );
	$hash	= str_replace( '=', '', $hash );
	return $hash;
}

$shortHash	= getShortHash( $note->noteId );
#$config->set( 'app.base.url', 'kb.ceusmedia.de/');
$shortUrl	= $config->get( 'app.base.url' ).'?'.$shortHash;

return '
<div class="row-fluid">
	<div class="span8 -column-left-75">
		<small><a href="./work/note">&laquo;&nbsp;zurück</a></small>
		<h2>'.htmlentities( $note->title, ENT_QUOTES, 'UTF-8' ).'</h2>
		<div class="note-view-content">'.$content.'</div>
		<div class="buttonbar">
			'.UI_HTML_Elements::LinkButton( './work/note', 'zur Liste', 'button cancel back left' ).'
			'.UI_HTML_Elements::LinkButton( './work/note/edit/'.$note->noteId, 'bearbeiten', 'button edit' ).'
		</div>
	</div>
	<div class="span4 -column-left-25">
		'.$panelTags.'
		'.$panelLinks.'
		<fieldset>
			<legend class="icon info">Info</legend>
	<!--		<a href="./?'.$shortHash.'">Kurzlink</a><br/>
			'.UI_HTML_Elements::Input( NULL, $shortUrl, 'max', TRUE ).'
	-->		<dl>
				<dt>Views</dt>
				<dd>'.$note->numberViews.'</dd>
				<dt>erstellt</dt>
				<dd>'.( $note->createdAt ? date( 'Y-m-d H:i', $note->createdAt ) : '-' ).'</dd>
				<dt>zuletzt verändert</dt>
				<dd>'.( $note->modifiedAt ? date( 'Y-m-d H:i', $note->modifiedAt ) : '-' ).'</dd>
				<dt>Notiz-Textverweis</dt>
				<dd><code>[note:'.$note->noteId.']</code></dd>
			</dl>
		</fieldset>
	</div>
</div>
<div class="column-left-25">
	'.$panelTags.'
	'.$panelLinks.'
	<fieldset>
		<legend class="icon info">Info</legend>
<!--		<a href="./?'.$shortHash.'">Kurzlink</a><br/>
		'.UI_HTML_Elements::Input( NULL, $shortUrl, 'max', TRUE ).'
-->		<dl>
			<dt>Views</dt>
			<dd>'.$note->numberViews.'</dd>
			<dt>erstellt</dt>
			<dd>'.( $note->createdAt ? date( 'Y-m-d H:i', $note->createdAt ) : '-' ).'</dd>
			<dt>zuletzt verändert</dt>
			<dd>'.( $note->modifiedAt ? date( 'Y-m-d H:i', $note->modifiedAt ) : '-' ).'</dd>
			<dt>Notiz-Textverweis</dt>
			<dd><code>[note:'.$note->noteId.']</code></dd>
		</dl>
	</fieldset>
</div>
<div class="column-clear"></div>';
?>
