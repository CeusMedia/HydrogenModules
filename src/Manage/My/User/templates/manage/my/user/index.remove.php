<?php

use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

/** @var WebEnvironment $env */
/** @var View $view */

if( !$env->getAcl()->has( 'manage/my/user', 'remove' ) )
	return;

extract( $view->populateTexts( [
	'panel.remove.top',
	'panel.remove.above',
	'panel.remove.info',
	'panel.remove.below',
	'panel.remove.bottom',
], 'html/manage/my/user/' ) );

return '<div class="content-panel">
	<h3>Konto entfernen</h3>
	<div class="content-panel-inner">
		<form action="./manage/my/user/remove" method="post">
			'.$textPanelRemoveAbove.'
			<button type="submit" name="save" class="btn btn-inverse">Konto entfernen</button>
		</form>
	</div>
</div>';
