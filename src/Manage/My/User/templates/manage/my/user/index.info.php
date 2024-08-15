<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var array<string,array<string|int,string|int>> $words */
/** @var object $user */

$helper		= new View_Helper_TimePhraser( $env );

$w			= (object) $words['info'];

$mapInfo	= [];

$mapInfo[$w->labelUsername]	= $user->username;
$mapInfo[$w->labelEmail]		= $user->email;
$mapInfo[$w->labelRole]		= '<span class="role role'.$user->role->roleId.'">'.$user->role->title.'</span>';
if( !empty( $user->company ) ){
	$link	= HTML::Link( './manage/my/company', $user->company->title );
	$mapInfo[$w->labelCompany]	= '<span class="company">'.$link.'</span>';
}
//$mapInfo['Status']	= '<span class="user-status status'.$user->status.'">'.$words['status'][$user->status].'</span>';
$mapInfo[$w->labelRegistration]	= $helper->convert( $user->createdAt, TRUE, $w->timePhrasePrefixSince, $w->timePhraseSuffixSince );


$lastPasswordChange	= $user->createdAt;
$modelPassword		= new Model_User_Password( $env );
$currentPassword	= $modelPassword->getByIndex( 'status', Model_User_Password::STATUS_ACTIVE );
if( $currentPassword )
	$lastPasswordChange	= $currentPassword->createdAt;
$mapInfo[$w->labelPasswordChange]	= $helper->convert( $lastPasswordChange, TRUE, $w->timePhrasePrefixSince, $w->timePhraseSuffixSince );

$mapInfo[$w->labelLogin]		= $helper->convert( $user->loggedAt, TRUE, $w->timePhrasePrefixAgo, $w->timePhraseSuffixAgo );
//$mapInfo[$w->labelActive]		= $helper->convert( $user->activeAt, TRUE, $w->timePhrasePrefix, $w->timePhraseSuffix );

$listInfo	= [];
foreach( $mapInfo as $term => $definition )
	$listInfo[]	= HtmlTag::create( 'dt', $term ).HtmlTag::create( 'dd', $definition );
$listInfo	= HtmlTag::create( 'dl', join( $listInfo ), ['class' => 'dl-horizontal'] );

//  --  PANEL: INFO  --  //
return '
<div class="content-panel content-panel-info">
	<h4>'.$w->heading.'</h4>
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
