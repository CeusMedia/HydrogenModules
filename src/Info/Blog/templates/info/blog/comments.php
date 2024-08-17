<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

/** @var WebEnvironment $env */
/** @var View $view */
/** @var Dictionary $moduleConfig */
/** @var array<string,array<string,string>> $words */
/** @var object $post */

$w		= (object) $words['comments'];

$listComments	= '<div class="alert">'.$w->empty.'</div>';

$list	= [];
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
