<?php

$w		= (object) $words['index'];

$tags	= $env->session->get( 'filter_notes_tags' );
if( !is_array( $tags ) )
	$tags	= array();

$indicator	= new UI_HTML_Indicator();

$list	= array();
foreach( $notes['list'] as $note ){
	$url		= './work/note/view/'.$note->noteId;
	$title		= '<div class="note-title"><a href="'.$url.'">'.htmlentities( $note->title, ENT_QUOTES, 'UTF-8' ).'</a></div>';
	$listLinks	= array();
	foreach( $note->links as $link ){
		$label	= $link->title ? '<acronym title="'.$link->url.'">'.htmlentities( $link->title, ENT_QUOTES, 'UTF-8' ).'</acronym>' : $link->url;
		$listLinks[]	= '<a class="link" href="'.$link->url.'">'.$label.'</a>';
	}
	$listLinks	= join( ' | ', $listLinks );
	$listTags	= array();
	foreach( $note->tags as $tag ){
		$class	= in_array( $tag, $tags ) ? 'remove' : 'add';
		$label	= UI_HTML_Tag::create( 'span', htmlentities( $tag->content, ENT_QUOTES, 'UTF-8' ) );
		$button	= UI_HTML_Tag::create( 'button', $label, array( 'class' => 'button icon tag-'.$class, 'data-tag-id' => $tag->tagId ) );
		$listTags[$tag->content]	= UI_HTML_Tag::create( 'li', $button );
	}
	ksort( $listTags );
	$spanAuthor	= '<span class="info user role role'.$note->user->roleId.'">'.htmlentities( $note->user->username, ENT_QUOTES, 'UTF-8' ).'</a></span>';
	$timestamp	= max( $note->createdAt, $note->modifiedAt, 0 );
	$spanDate	= $timestamp ? '<span class="info date ">'.date( 'd.m.y', $timestamp ).'</span>' : '';
	$spanRating	= '<span class="indicator rating">'.$indicator->build( 5, 7 ).'</span>';
	$divInfo	= '<div class="info-inline">'.$spanDate.$spanAuthor.'</div>';
	$listTags	= '<div style="float: right; text-align: right"><ul class="tags-list-inline">'.join( $listTags ).'</ul></div><div style="clear: left"></div>';
	$list[]	= '<li class="note">'.$listTags.$title.$divInfo/*.$spanRating./*.$listLinks*/.'</li>';
}
if( $list )
	$list	= '<ul class="results">'.join( $list ).'</ul>';
else
	$list	= '<p><em>Nichts gefunden.</em></p>';

$pagination	= new UI_HTML_Pagination( array( 'uri' => 'work/note/' ) );
$p = $pagination->build( $notes['number'], $limit, $offset );


//  --  FILTER  --  //
$panelFilter	= $view->loadTemplateFile( 'work/note/index.filter.php' );

$iconAdd		= '<img src="http://img.int1a.net/famfamfam/silk/add.png"/>';
$buttonAdd		= UI_HTML_Elements::LinkButton( './work/note/add', $iconAdd, 'button tiny' );

return '
<div class="row-fluid">
	<div class="span3 -column-left-20">
		'.$panelFilter.'
	</div>
	<div id="results" class="span9 -column-left-80">
		<fieldset>
			<legend class="icon note">'.$w->legend.' <!--<small>'.round( $notes['time'] / 1000, 1 ).'ms</small>--> </legend>
			'.$list.'
			'.$p.'
		</fieldset>
	</div>
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


<html>
	<head>
		<script src="jquery.js"></script>
		<script src="jquery.lightbox.js"></script>
		<script>
$(document).ready(function(){
	$("#gallery a").lightbox();
});


$("div#gallery")
		</script>
	</head>
	<body>
		<div id="gallery">
			<a href="large.png"><img src="small.png"></a>
		</div>
	</body>
		
</html>
