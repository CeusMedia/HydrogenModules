<?php

use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var View_Work_Meeting $view */
/** @var Entity_Work_Meeting[] $meetings */
/** @var ?Entity_Work_Meeting $meeting */
/** @var int|string $currentUserId */
/** @var array $words */

$list	= $view->renderViewCards( $words, $meetings, $currentUserId );

//  Hint: The trigger to open a meeting modal by given meeting id (in request path) is already done in controller action

return $list;
