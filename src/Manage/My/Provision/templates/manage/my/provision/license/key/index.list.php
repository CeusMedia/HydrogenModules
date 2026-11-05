<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['index.list'];

$iconsStatus	= array(
//	Model_Provision_User_License_Key::STATUS_	=> HtmlTag::create( 'i', '', ['class' => 'icon-remove'] ),
	Model_Provision_User_License_Key::STATUS_NEW		=> HtmlTag::create( 'i', '', ['class' => 'icon-pause'] ),
	Model_Provision_User_License_Key::STATUS_ASSIGNED	=> HtmlTag::create( 'i', '', ['class' => 'icon-play'] ),
	Model_Provision_User_License_Key::STATUS_EXPIRED	=> HtmlTag::create( 'i', '', ['class' => 'icon-stop'] ),
);

$list	= '<div class="muted"><em>'.$w->textEmpty.'</em></div><br/>';

$rowColors	= [
	0	=> '',
	1	=> 'warning',
	2	=> 'success',
	3	=> 'info',
];

$helper		= new View_Helper_Member( $this->env );


if( $userLicenseKeys ){
	$list	= [];
	foreach( $userLicenseKeys as $userLicenseKey ){
		$class		= NULL;
		$dateStart	= NULL;
		$dateEnd	= NULL;
		$duration	= '';
		$status		= $iconsStatus[$userLicenseKey->status].'&nbsp'.$words['keyStates'][$userLicenseKey->status];
		if( $userLicenseKey->status == 2 ){
			$dateStart	= date( 'd.m.Y', $userLicenseKey->startsAt );
			$dateEnd 	= date( 'd.m.Y', $userLicenseKey->endsAt );
			$duration	= sprintf( $w->textRunning, $dateStart, $dateEnd );
		}
		if( $userLicenseKey->status == 3 ){
			$dateStart	= date( 'd.m.Y', $userLicenseKey->startsAt );
			$dateEnd 	= date( 'd.m.Y', $userLicenseKey->endsAt );
			$duration	= sprintf( $w->textRan, $dateStart, $dateEnd );
		}
		if( $userLicenseKey->status == 1 ){
		}
		if( $userLicenseKey->status == 0 ){
		}
		$link	= HtmlTag::create( 'a', $userLicenseKey->userLicenseId, [
			'href'	=> './manage/my/provision/license/view/'.$userLicenseKey->userLicenseId,
		] );
	$helper->setUser( $userLicenseKey->userLicense->userId );
//	$helper->setMode( 'inline' );
	$userName	= $helper->render();
//		$userName	= HtmlTag::create( 'small', $userLicense->user->firstname.' '.$userLicense->user->firstname, ['class' => 'muted'] );
		$product	= $userLicenseKey->product->title;
/*		if( $userLicenseKey->product->url )
			$product	= HtmlTag::create( 'a', $product, [
				'href'		=> $userLicenseKey->product->url,
				'target'	=> '_blank',
			] );*/

		$licenseUid	= HtmlTag::create( 'a', $userLicenseKey->userLicense->uid, [
			'href'	=> './manage/my/provision/license/view/'.$userLicenseKey->userLicenseId
		] );

		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $w->labelLicenseNumber.': '.$licenseUid.'<br/>'.$w->labelLicense.': '.$userLicenseKey->productLicense->title.'<br/>'.$w->labelProduct.': '.$product.'<br/>'.$w->labelOwner.': '.$userName ),
			HtmlTag::create( 'td', $status.'<br/>'.$duration ),
			HtmlTag::create( 'td', $w->labelKeyNumber.': '.$userLicenseKey->uid ),
		), ['class' => $rowColors[$userLicenseKey->status]] );
	}
}
$thead	= HtmlTag::create( 'thead', HtmlElements::TableHeads( [$w->headLicense, $w->headStatus, $w->headKey] ) );
$tbody	= HtmlTag::create( 'tbody', $list );
$list	= HtmlTag::create( 'table', $thead.$tbody, ['class' => 'table'] );

$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'icon-plus icon-white'] );
$buttonAdd	= HtmlTag::create( 'a', $iconAdd.'&nbsp;'.$w->buttonAdd, [
	'href'	=> './manage/my/provision/license/add',
	'class'	=> 'btn btn-success',
] );

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span12">
				'.$list.'
			</div>
		</div>
		<div class="buttonbar">
			'.$buttonAdd.'
		</div>
	</div>
</div>';
