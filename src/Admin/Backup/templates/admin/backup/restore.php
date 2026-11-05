<?php
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */

$w	= (object) $words['restore'];

return '
<div class="content-panel"
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner"
		<p>'.$w->notImplemented.'</p>
	</div>
</div>';
