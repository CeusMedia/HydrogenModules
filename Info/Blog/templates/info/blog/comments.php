<?php

$w		= (object) $words['comments'];

$listComments	= '<div class="alert">'.$w->empty.'</div>';

$list	= array();
foreach( $post->comments as $comment ){
	if( $comment->status >= 0 ){
		$list[]	= $view->renderComment( $comment );
	}
}
if( $list )
	$listComments	= join( $list );

return '
<div class="not-content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="not-content-panel-inner">
		<div class="list-comments">
			'.$listComments.'
		</div>
		<br/>
	</div>
</div>';
