<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconCancel		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );

$duration		= '<em>noch nicht aktiviert</em>';
if( in_array( $userLicense->status, array( Model_Provision_User_License::STATUS_ACTIVE, Model_Provision_User_License::STATUS_EXPIRED ) ) ){
	$dateStart	= date( 'd.m.Y', $userLicense->startsAt );
	$dateEnd 	= date( 'd.m.Y', $userLicense->endsAt );
	$duration	= $dateStart.' - '.$dateEnd;
}

$iconsStatus	= array(
	Model_Provision_User_License::STATUS_DEACTIVATED	=> HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) ),
	Model_Provision_User_License::STATUS_REVOKED		=> HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) ),
	Model_Provision_User_License::STATUS_NEW			=> HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-pause' ) ),
	Model_Provision_User_License::STATUS_ACTIVE			=> HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-play' ) ),
 	Model_Provision_User_License::STATUS_EXPIRED		=> HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-stop' ) ),
);

$avatar	= View_Helper_Member::renderStatic( $env, $userLicense->userId );

$data1	= [];
$data1['Produkt']				= $product->title;
$data1['Lizenz']				= $userLicense->productLicense->title;
$data1['Lizenznummer']			= $userLicense->uid;
$data1['Preis']					= $userLicense->price.'&nbsp;&euro;';
$data1['Besitzer']				= $avatar;
$data2['Lizenzschlüssel']	= ( $userLicense->users - count( $notAssignedKeys ) ).' von '.$userLicense->users.' Schlüssel<span class="muted">(n)</span> vergeben';
$data2['Laufzeit']				= $words['durations'][$userLicense->duration];
$data2['Zustand']				= $iconsStatus[$userLicense->status].'&nbsp;'.$words['licenseStates'][$userLicense->status];
//$data1['davon vergeben']		= $userLicense->users;
$data2['Zeitraum']				= $duration;

$list1	= View_Manage_My_Provision_License::renderDefinitionList( $data1 );
$list2	= View_Manage_My_Provision_License::renderDefinitionList( $data2 );

return '
<div class="content-panel">
	<h3><span class="muted">Lizenz: </span>'.$userLicense->uid.'</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span6">
				'.$list1.'
			</div>
			<div class="span6">
				'.$list2.'
			</div>
		</div>
		<div class="buttonbar">
			<a href="./manage/my/provision/license" class="btn btn-small">'.$iconCancel.'&nbsp;zurück</a>

		</div>
	</div>
</div>';
