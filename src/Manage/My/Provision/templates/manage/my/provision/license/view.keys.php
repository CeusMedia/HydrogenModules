<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-user'] );
$iconRevoke		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );

$iconsStatus	= array(
	0	=> HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] ),
	Model_Provision_User_License_Key::STATUS_NEW		=> HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-pause'] ),
	Model_Provision_User_License_Key::STATUS_ASSIGNED	=> HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-play'] ),
	Model_Provision_User_License_Key::STATUS_EXPIRED	=> HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-stop'] ),
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
		$buttonAssign	= HtmlTag::create( 'a', $iconAdd.'&nbsp;vergeben', [
			'href'	=> './manage/my/provision/license/assign/'.$key->userLicenseKeyId,
			'class'	=> 'btn btn-success btn-small'
		] );
	}
	if( $key->status == Model_Provision_User_License_Key::STATUS_ASSIGNED ){
		$buttonAssign	= HtmlTag::create( 'a', $iconRevoke.'&nbsp;entziehen', [
			'href'	=> './manage/my/provision/license/revoke/'.$key->userLicenseKeyId,
			'class'	=> 'btn btn-inverse btn-small'
		] );

	}



	$link	= $key->uid;
	if( 1 ){
		$link	= HtmlTag::create( 'a', $link, [
			'href'	=> './manage/my/provision/license/key/view/'.$key->userLicenseKeyId,
		] );
	}
	$list[]	= HtmlTag::create( 'tr', array(
		HtmlTag::create( 'td', $link ),
		HtmlTag::create( 'td', $status ),
		HtmlTag::create( 'td', $user ),
		HtmlTag::create( 'td', $buttonAssign ),
	) );
}
$colgroup	= HtmlElements::ColumnGroup( array( "15%", "25%", "30%") );
$heads	= HtmlElements::TableHeads( ['Schlüssel', 'Zustand', 'Besitzer'] );
$thead	= HtmlTag::create( 'thead', $heads );
$tbody	= HtmlTag::create( 'tbody', $list );
$list	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table'] );

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
