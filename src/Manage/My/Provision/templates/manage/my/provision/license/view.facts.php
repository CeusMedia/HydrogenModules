<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['view'];

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );

$duration		= '<em>'.$w->textNotActivated.'</em>';
if( in_array( $userLicense->status, [Model_Provision_User_License::STATUS_ACTIVE, Model_Provision_User_License::STATUS_EXPIRED] ) ){
	$dateStart	= date( 'd.m.Y', $userLicense->startsAt );
	$dateEnd 	= date( 'd.m.Y', $userLicense->endsAt );
	$duration	= $dateStart.' - '.$dateEnd;
}

$iconsStatus	= array(
	Model_Provision_User_License::STATUS_DEACTIVATED	=> HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] ),
	Model_Provision_User_License::STATUS_REVOKED		=> HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] ),
	Model_Provision_User_License::STATUS_NEW			=> HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-pause'] ),
	Model_Provision_User_License::STATUS_ACTIVE			=> HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-play'] ),
 	Model_Provision_User_License::STATUS_EXPIRED		=> HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-stop'] ),
);

$avatar	= View_Helper_Member::renderStatic( $env, $userLicense->userId );

$data1	= [];
$data1[$w->labelProductTitle]	= $product->title;
$data1[$w->labelLicenseTitle]	= $userLicense->productLicense->title;
$data1[$w->labelLicenseNumber]	= $userLicense->uid;
$data1[$w->labelPrice]			= $userLicense->price.'&nbsp;&euro;';
$data1[$w->labelUser]			= $avatar;
$data2[$w->labelKeys]			= sprintf( $w->textKeysAssigned, $userLicense->users - count( $notAssignedKeys ), $userLicense->users );
$data2[$w->labelRuntime]		= $words['durations'][$userLicense->duration];
$data2[$w->labelStatus]			= $iconsStatus[$userLicense->status].'&nbsp;'.$words['licenseStates'][$userLicense->status];
//$data1['davon vergeben']		= $userLicense->users;
$data2[$w->labelPeriod]			= $duration;

$list1	= View_Manage_My_Provision_License::renderDefinitionList( $data1 );
$list2	= View_Manage_My_Provision_License::renderDefinitionList( $data2 );

return '
<div class="content-panel">
	<h3><span class="muted">'.$w->headingPrefix.'</span>'.$userLicense->uid.'</h3>
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
			<a href="./manage/my/provision/license" class="btn btn-small">'.$iconCancel.'&nbsp;'.$w->buttonCancel.'</a>

		</div>
	</div>
</div>';
