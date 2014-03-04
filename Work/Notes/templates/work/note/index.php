<?php
$w		= (object) $words['index'];

$tags	= $env->session->get( 'filter_notes_tags' );
if( !is_array( $tags ) )
	$tags	= array();

$indicator	= new UI_HTML_Indicator();
$iconTag	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-tag' ) );
$pagination	= new CMM_Bootstrap_PageControl( './work/note', $page, ceil( $notes['number'] / $limit ) );
$helper		= new View_Helper_TimePhraser( $this->env );
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
		$class		= in_array( $tag, $tags ) ? 'active' : '';
		$action		= in_array( $tag, $tags ) ? 'forgetTag' : 'addSearchTag';
		$label		= htmlentities( $tag->content, ENT_QUOTES, 'UTF-8' );
		$attributes	= array(
			'class'			=> 'btn btn-small '.$class,
			'data-toggle'	=> 'button',
			'href'			=> './work/note/'.$action.'/'.$tag->tagId.'/'.$page,
		);
		$listTags[$tag->content]	= UI_HTML_Tag::create( 'a', $iconTag.' '.$label, $attributes );
	}
	ksort( $listTags );
	$listTags	= '<div class="pull-right">'.join( ' ', $listTags ).'</div>';

	$spanAuthor	= '<span class=""><i class="icon-user"></i> '.htmlentities( $note->user->username, ENT_QUOTES, 'UTF-8' ).'</span>';
	$timestamp	= max( $note->createdAt, $note->modifiedAt, 0 );
	$time		= $helper->convert( $timestamp, TRUE );
	$spanDate	= $timestamp ? '<span class=""><i class="icon-time"></i> '.$time.'</span>' : '';
	$spanRating	= '<span class="indicator rating">'.$indicator->build( 5, 7 ).'</span>';
	$divInfo	= '<div class="info-inline">'.$spanAuthor.' &minus; '.$spanDate.'</div>';
	$list[]	= '<li class="note">'.$listTags.$title.$divInfo/*.$spanRating./*.$listLinks*/.'</li>';
}
if( $list )
	$list	= '<ul class="results">'.join( $list ).'</ul>';
else
	$list	= '<p><em>Nichts gefunden.</em></p>';

#$pagination	= new UI_HTML_Pagination( array( 'uri' => 'work/note/' ) );
#$p = $pagination->build( $notes['number'], $limit, $offset );

$p	= $pagination->render();


//  --  FILTER  --  //
$panelFilter	= $view->loadTemplateFile( 'work/note/index.filter.php' );

$iconAdd		= '<img src="http://img.int1a.net/famfamfam/silk/add.png"/>';
$buttonAdd		= UI_HTML_Elements::LinkButton( './work/note/add', $iconAdd, 'button tiny' );

return '
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span9">
		<div class="content-panel content-panel-list">
			<h3>'.$w->legend.'</h3>
			<div class="content-panel-inner" id="results">
				'.$list.'
				'.$p.'
			</div>
		</div>
	</div>
</div>
<script>
var query = "'.$query.'";
$(document).ready(function(){
	FormNoteFilter.__init();
});
</script>
<div style="clear: both"></div>';
?>
