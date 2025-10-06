<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var View_Work_Meeting $view */
/** @var Entity_Work_Meeting[] $meetings */
/** @var ?Entity_Work_Meeting $meeting */
/** @var int|string|NULL $meetingId */
/** @var int|string $currentUserId */
/** @var array $words */
/** @var bool|NULL $gone */

if( [] !== $meetings ){
	$list	= $view->renderViewCards( $words, $meetings, $currentUserId );
}
else {
	$list	= HtmlTag::create( 'div', 'Keine Meetings vorhanden, zur Zeit.', ['class' => 'alert alert-success'] );
}


if( NULL !== $meetingId ){
	if( NULL !== $meeting ){
		if( Model_Work_Meeting::STATUS_CANCELLED === $meeting->status ){
			$modal	= $view->renderViewModal( $words, $meeting );
			$list	.= $modal;
		}
	}
}



//  Hint: The trigger to open a meeting modal by given meeting id (in request path) is already done in controller action

return $list;
