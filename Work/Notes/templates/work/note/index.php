<?php

$term	= $this->env->session->get( 'search_term' );
$tags	= $this->env->session->get( 'search_tags' );

$iconAdd	= '<img src="http://img.int1a.net/famfamfam/silk/magnifier_zoom_in.png" title="zuweisen"/>';
$iconRemove	= '<img src="http://img.int1a.net/famfamfam/silk/magnifier_zoom_out.png" title="entfernen"/>';

$not	= array();
foreach( $tags as $tag )
	$not[]	= $tag->tagId;

$logic	= new Logic_Note( $this->env );
$tags	= $logic->getTopTags( 3, 0, $not );

$tagsMore	= "";
if( $tags ){
	$list	= array();
	foreach( $tags as $tag ){
		$url	= './work/note/addSearchTag/'.$tag->tagId;
		$label	= '<div class="item-tag-label">'.$tag->content.'</div>';
		$count	= '<div class="number-indicator">'.$tag->relations.'</div>';
		$button	= '<button type="button" class="button tiny remove" onclick="document.location=\''.$url.'\';">'.$iconAdd.'</button>';
		$tray	= '<div class="item-tag-tray">'.$count.$button.'</div>';
		$list[]	= '<li class="item-tag-extended border-top">'.$label.$tray.'</li>';
	}
	$label		= '<label>Vorschläge</label><br/>';
	$tagsMore	= '<ul class="tags-list">'.join( $list ).'</ul>';
	$tagsMore	= '<li>'.$label.$tagsMore.'</li>';
}

$tags	= $session->get( 'search_tags' );
$tagsSearch	= "";
if( $tags ){
	$list	= array();
	foreach( $tags as $tag ){
		$url	= './work/note/forgetTag/'.$tag->tagId;
		$label	= '<div class="item-tag-label">'.$tag->content.'</div>';
//		$count	= '<div class="number-indicator">'.$tag->relevance.'</div>';
		$button	= '<button type="button" class="button tiny remove" onclick="document.location=\''.$url.'\';">'.$iconRemove.'</button>';
		$tray	= '<div class="item-tag-tray">'.$button.'</div>';
		$list[]	= '<li class="item-tag-extended border-top">'.$label.$tray.'</li>';
	}
	$label		= '<label>aktive Schlagwörter</label><br/>';
	$tagsSearch	= '<ul class="tags-list">'.join( $list ).'</ul>';
	$tagsSearch	= '<li>'.$label.$tagsSearch.'</li>';
}

$list	= array();
foreach( $notes['list'] as $note ){
	$url		= './work/note/view/'.$note->noteId;
	$title		= '<div class="note-title"><a href="'.$url.'">'.$note->title.'</a></div>';
	$listLinks	= array();
	foreach( $note->links as $link ){
		$label	= $link->title ? '<acronym title="'.$link->url.'">'.$link->title.'</acronym>' : $link->url;
		$listLinks[]	= '<a class="link" href="'.$link->url.'">'.$label.'</a>';
	}
	$listLinks	= join( ' | ', $listLinks );
	$listTags	= array();
	foreach( $note->tags as $tag )
		$listTags[$tag->content]	= '<li class="tag"><span class="tag">'.$tag->content.'</span></li>';
	ksort( $listTags );
	$listTags	= '<ul class="tags-list">'.join( $listTags ).'</ul><div style="clear: left"></div>';
	$list[]	= '<li class="note">'.$listTags.$title.$listLinks.'</li>';
}
if( $list )
	$list	= '<ul class="results">'.join( $list ).'</ul>';

$pagination	= new UI_HTML_Pagination( array( 'uri' => 'work/note/' ) );
$p = $pagination->build( $notes['number'], $limit, $offset );

$panelFilter	= '
<form id="form_note_filter" action="./work/note" method="post">
	<fieldset>
		<legend class="icon filter">Suche</legend>
		<ul class="input">
			<li>
				<label>Schlagwort</label><br/>
				<input id="input_filter_query" tabindex="1" name="filter_query" value="'.$term.'" autocomplete="off"/>
				<div style="clear: left"></div>
			</li>
			'.$tagsSearch.'
			'.$tagsMore.'
		</ul>
	</fieldset>
</form>
';

return '
<div class="column-left-20">
	'.$panelFilter.'
</div>
<div id="results" class="column-left-80">
	<fieldset>
		<legend class="icon note">Artikel <small>'.round( $notes['time'] / 1000, 1 ).'ms</small></legend>
		'.$list.'
		'.$p.'
	</fieldset>
</div>
<script>
var query = "'.$query.'";
$(document).ready(function(){
	FormNoteFilter.__init();
});
</script>
<div style="clear: both"></div>
';
?>
