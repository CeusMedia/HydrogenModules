<?php

$helper		= new View_Helper_TimePhraser( $env );

$mapInfo	= array();
$mapInfo[$words['info']['labelRole']]	= '<span class="role role'.$user->role->roleId.'">'.$user->role->title.'</span>';
if( !empty( $user->company ) ){
	$link	= HTML::Link( './manage/my/company', $user->company->title );
	$mapInfo[$words['info']['labelCompany']]	= '<span class="company">'.$link.'</span>';
}
$mapInfo['Status']	= '<span class="user-status status'.$user->status.'">'.$words['status'][$user->status].'</span>';
$mapInfo[$words['info']['labelRegistration']]	= $helper->convert( $user->createdAt, TRUE, $words['info']['timePhrasePrefix'], $words['info']['timePhraseSuffix'] );
if( $user->userId !== $currentUserId ){
	$mapInfo[$words['info']['labelLogin']]		= $helper->convert( $user->loggedAt, TRUE, $words['info']['timePhrasePrefix'], $words['info']['timePhraseSuffix'] );
	$mapInfo[$words['info']['labelActive']]	= $helper->convert( $user->activeAt, TRUE, $words['info']['timePhrasePrefix'], $words['info']['timePhraseSuffix'] );
}

$listInfo	= array();
foreach( $mapInfo as $term => $definition )
	$listInfo[]	= UI_HTML_Tag::create( 'dt', $term ).UI_HTML_Tag::create( 'dd', $definition );
$listInfo	= UI_HTML_Tag::create( 'dl', join( $listInfo ), array( 'class' => 'dl-horizontal' ) );

//  --  PANEL: INFO  --  //
return '
<div class="content-panel content-panel-info">
	<h4>'.$words['info']['heading'].'</h4>
	<div class="content-panel-inner">
		'.$listInfo.'
	</div>
</div>
<style>
.content-panel-info .dl-horizontal dt {
	width: 40%;
	}
.content-panel-info .dl-horizontal dd {
	margin-left: 45%;
	}
</style>
';
?>
