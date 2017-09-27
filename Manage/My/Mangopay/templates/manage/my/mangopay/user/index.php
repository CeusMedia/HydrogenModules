<?php

/*
$list	= array();
foreach( $users as $user ){
	$link	= UI_HTML_Tag::create( 'a', $user->Id, array( 'href' => './manage/my/mangopay/user/view/'.$user->Id ) );
	$list[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', $link ),
		UI_HTML_Tag::create( 'td', $user->FirstName.' '.$user->LastName ),
		UI_HTML_Tag::create( 'td', $user->Email ),
	) );
}

$tbody	= UI_HTML_Tag::create( 'tbody', $list );
$table	= UI_HTML_Tag::create( 'table', $tbody, array( 'class' => 'table table-striped' ) );
*/

$table	= print_m( $user, NULL, NULL, TRUE );

return '
<div class="content-panel">
	<div class="content-panel-inner">
		'.$table.'
	</div>
</div>';
