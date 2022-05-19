<?php

$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-user' ) );
$iconRevoke		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

$iconsStatus	= array(
	0	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) ),
	Model_Provision_User_License_Key::STATUS_NEW		=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-pause' ) ),
	Model_Provision_User_License_Key::STATUS_ASSIGNED	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-play' ) ),
	Model_Provision_User_License_Key::STATUS_EXPIRED	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-stop' ) ),
);

$list	= [];
foreach( $userLicense->keys as $key ){
	$dateStart	= NULL;
	$dateEnd	= NULL;
	$duration	= '';
	$status		= $iconsStatus[$key->status].'&nbsp'.$words['keyStates'][$key->status];
	$helper		= new View_Helper_Member( $this->env );
	$user		= '';
	$buttonAssign	= '';
	if( $key->userId ){
		$helper->setUser( $key->userId );
		if( $key->userId != $currentUserId )
			$helper->setLinkUrl( './member/view/%d' );
//		$helper->setMode( 'inline' );
		$user	= $helper->render();
	}
	if( $key->status == Model_Provision_User_License_Key::STATUS_NEW ){
		$buttonAssign	= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;vergeben', array(
			'href'	=> './manage/my/provision/license/assign/'.$key->userLicenseKeyId,
			'class'	=> 'btn btn-success btn-small'
		) );
	}
	if( $key->status == Model_Provision_User_License_Key::STATUS_ASSIGNED ){
		$buttonAssign	= UI_HTML_Tag::create( 'a', $iconRevoke.'&nbsp;entziehen', array(
			'href'	=> './manage/my/provision/license/revoke/'.$key->userLicenseKeyId,
			'class'	=> 'btn btn-inverse btn-small'
		) );

	}



	$link	= $key->uid;
	if( 1 ){
		$link	= UI_HTML_Tag::create( 'a', $link, array(
			'href'	=> './manage/my/provision/license/key/view/'.$key->userLicenseKeyId,
		) );
	}
	$list[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', $link ),
		UI_HTML_Tag::create( 'td', $status ),
		UI_HTML_Tag::create( 'td', $user ),
		UI_HTML_Tag::create( 'td', $buttonAssign ),
	) );
}
$colgroup	= UI_HTML_Elements::ColumnGroup( array( "15%", "25%", "30%") );
$heads	= UI_HTML_Elements::TableHeads( array( 'Schlüssel', 'Zustand', 'Besitzer' ) );
$thead	= UI_HTML_Tag::create( 'thead', $heads );
$tbody	= UI_HTML_Tag::create( 'tbody', $list );
$list	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table' ) );

return '
<style>
span.user {
	display: block;
	width: 12em;
/*	border: 1px solid rgba(0,0,0,0.25);*/
	}
span.user img {
	margin: 1px;
	min-height: 20px;
	}
</style>
<div class="content-panel">
	<h3>Schlüssel dieser Lizenz</h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';
