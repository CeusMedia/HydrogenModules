<?php
$w			= (object) $words['index'];
$tabs		= View_Manage_My_User::renderTabs( $env, 'avatar' );

return $tabs.'
<div class="content-panel" id="manageMyUserAvatar">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
	</div>
</div>
';
?>
