<?php

$mapInfo	= array();
#if( $config->get( 'module.roles' ) )
	$mapInfo[$words['info']['labelRole']]	= '<span class="role role'.$user->role->roleId.'">'.$user->role->title.'</span>';
if( !empty( $user->company ) ){
	$link	= HTML::Link( './manage/my/company', $user->company->title );
	$mapInfo[$words['info']['labelCompany']]	= '<span class="company">'.$link.'</span>';
}
$mapInfo['Status']	= '<span class="user-status status'.$user->status.'">'.$words['status'][$user->status].'</span>';

$listInfo	= array();
foreach( $mapInfo as $term => $definition )
	$listInfo[]	= UI_HTML_Tag::create( 'dt', $term ).UI_HTML_Tag::create( 'dd', $definition );
$listInfo	= UI_HTML_Tag::create( 'dl', join( $listInfo ), array( 'class' => 'dl-horizontal' ) );

//  --  PANEL: INFO  --  //
$helper			= new View_Helper_TimePhraser( $env );
$mapTimes	= array();
$mapTimes[$words['info']['labelRegistration']]	= $helper->convert( $user->createdAt, TRUE, $words['info']['timePhrasePrefix'], $words['info']['timePhraseSuffix'] );
if( $user->userId !== $currentUserId ){
	$mapTimes[$words['info']['labelLogin']]		= $helper->convert( $user->loggedAt, TRUE, $words['info']['timePhrasePrefix'], $words['info']['timePhraseSuffix'] );
	$mapTimes[$words['info']['labelActive']]	= $helper->convert( $user->activeAt, TRUE, $words['info']['timePhrasePrefix'], $words['info']['timePhraseSuffix'] );
}

$listTimes	= array();
foreach( $mapTimes as $term => $definition )
	$listTimes[]	= UI_HTML_Tag::create( 'dt', $term ).UI_HTML_Tag::create( 'dd', $definition );
$listTimes	= UI_HTML_Tag::create( 'dl', join( $listTimes ), array( 'class' => 'dl-horizontal' ) );

return '
<div class="content-panel">
	<h3>'.$words['info']['heading'].'</h3>
	<div class="content-panel-inner">'.
		$listInfo.
		'<hr/>'.
		$listTimes.'
	</div>
</div>';
?>
