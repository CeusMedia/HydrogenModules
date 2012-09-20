<?php
$modelUser	= new Model_User( $env );
$userId		= $session->get( 'userId' );
$user		= $modelUser->get( $userId );

$list	= array();
foreach( $invites->codes as $invite ){
	$url	= './manage/my/user/invite/invite/?code='.$invite->userInviteId;
	$link	= UI_HTML_Tag::create( 'a', $invite->code, array( 'href' => $url ) );
	$list[]	= UI_HTML_Tag::create( 'li', $link );
}
$list	= UI_HTML_Tag::create( 'ul', join( $list ) );

return '
<dl>
	<dt>User ID</dt>
	<dd>'.$userId.'</dd>
	<dt>User Name</dt>
	<dd>'.$user->username.'</dd>
	<dt>Invite Codes ('.count( $invites->codes ).')</dt>
	<dd>'.$list.'</dd>
</dl>
';
?>