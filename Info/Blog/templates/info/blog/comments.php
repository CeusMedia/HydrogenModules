<?php

$w		= (object) $words['comments'];

$list	= array();
foreach( $post->comments as $comment ){
	if( $comment->status >= 0 ){
		$infobar	= $view->renderCommentInfoBar( $comment );
		$content	= '<blockquote>'.nl2br( trim( $comment->content ) ).'</blockquote>';
		$list[]	= UI_HTML_Tag::create( 'div', $infobar.$content, array(
			'class'		=> 'list-comments-item'
		) );
	}
}
if( $list )
	$list	= join( $list );
else
	$list	= '<div class="alert">Zu diesem Eintrag gibt es noch keine Kommentare.</div>';

return '
<div class="not-content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="not-content-panel-inner">
		<div class="list-comments">
			'.$list.'
		</div>
		<br/>
	</div>
</div>';
