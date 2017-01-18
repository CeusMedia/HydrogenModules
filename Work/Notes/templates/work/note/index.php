<?php
$w		= (object) $words['index'];

$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
$iconTag	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-tag' ) );


$tags	= $env->session->get( 'filter_notes_tags' );
if( !is_array( $tags ) )
	$tags	= array();

$indicator	= new UI_HTML_Indicator();
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
		if( !in_array( $tag, $tags ) ){
/*			$listTags[$tag->content]	= UI_HTML_Tag::create( 'a', htmlentities( $tag->content, ENT_QUOTES, 'UTF-8' ), array(
				'href'	=> './work/note/addSearchTag/'.$tag->tagId.'/'.$page,
				'class'	=> 'list-item-tag-link',
			) );*/
			$listTags[$tag->content]	= UI_HTML_Tag::create( 'span', htmlentities( $tag->content, ENT_QUOTES, 'UTF-8' ), array(
				'class'	=> 'list-item-tag',
			) );
		}
		else
			$listTags[$tag->content]	= htmlentities( $tag->content, ENT_QUOTES, 'UTF-8' );
	}
	ksort( $listTags );
//	$listTags	= '<div class="pull-right">'.join( ' ', $listTags ).'</div>';
	$listTags	= $listTags ? '<i class="icon-tags"></i> '.join( ', ', $listTags ) : '<em><small class="muted">Keine Tags.</small></em>';
	$spanTags	= '<span class="">'.$listTags.'</span>';
	$spanAuthor	= '<span class=""><i class="icon-user"></i> '.htmlentities( $note->user->username, ENT_QUOTES, 'UTF-8' ).'</span>';
	$timestamp	= max( $note->createdAt, $note->modifiedAt, 0 );
	$time		= $helper->convert( $timestamp, TRUE );
	$spanDate	= $timestamp ? '<span class=""><i class="icon-time"></i> '.$time.'</span>' : '';
	$spanRating	= '<span class="indicator rating">'.$indicator->build( 5, 7 ).'</span>';
	$divInfo	= '<div class="info-inline">'.$spanAuthor.' &minus; '.$spanDate.' &minus; '.$spanTags.'</div>';
	$list[]	= '<li class="note">'./*$listTags.*/$title.$divInfo/*.$listTags/*.$spanRating./*.$listLinks*/.'</li>';
}
if( $list )
	$list	= '<ul class="results">'.join( $list ).'</ul>';
else
	$list	= '<p><em>Nichts gefunden.</em></p>';

#$pagination	= new UI_HTML_Pagination( array( 'uri' => 'work/note/' ) );
#$p = $pagination->build( $notes['number'], $limit, $offset );

$pagination	= $pagination->render();


//  --  FILTER  --  //
$panelFilter	= $view->loadTemplateFile( 'work/note/index.filter.php' );

$buttonAdd		= UI_HTML_Tag::create( 'a', $iconAdd.' neue Notiz', array( 'href' => './work/note/add', 'class' => 'btn not-btn-small btn-primary' ) );


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
				<div class="buttonbar">
					'.$buttonAdd.'
					'.$pagination.'
				</div>
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
