<?php

$w	= (object) $words['index.list'];

$iconsStatus	= array(
//	Model_Provision_User_License_Key::STATUS_	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove' ) ),
	Model_Provision_User_License_Key::STATUS_NEW		=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-pause' ) ),
	Model_Provision_User_License_Key::STATUS_ASSIGNED	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-play' ) ),
	Model_Provision_User_License_Key::STATUS_EXPIRED	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-stop' ) ),
);

$list	= '<div class="muted"><em>Keine vorhanden.</em></div><br/>';

$rowColors	= array(
	0	=> '',
	1	=> 'warning',
	2	=> 'success',
	3	=> 'info',
);

$helper		= new View_Helper_Member( $this->env );


if( $userLicenseKeys ){
	$list	= array();
	foreach( $userLicenseKeys as $userLicenseKey ){
		$class		= NULL;
		$dateStart	= NULL;
		$dateEnd	= NULL;
		$duration	= '';
		$status		= $iconsStatus[$userLicenseKey->status].'&nbsp'.$words['keyStates'][$userLicenseKey->status];
		if( $userLicenseKey->status == 2 ){
			$dateStart	= date( 'd.m.Y', $userLicenseKey->startsAt );
			$dateEnd 	= date( 'd.m.Y', $userLicenseKey->endsAt );
			$duration	= 'läuft: '.$dateStart.' - '.$dateEnd;
		}
		if( $userLicenseKey->status == 3 ){
			$dateStart	= date( 'd.m.Y', $userLicenseKey->startsAt );
			$dateEnd 	= date( 'd.m.Y', $userLicenseKey->endsAt );
			$duration	= 'lief: '.$dateStart.' - '.$dateEnd;
		}
		if( $userLicenseKey->status == 1 ){
		}
		if( $userLicenseKey->status == 0 ){
		}
		$link	= UI_HTML_Tag::create( 'a', $userLicenseKey->userLicenseId, array(
			'href'	=> './manage/my/provision/license/view/'.$userLicenseKey->userLicenseId,
		) );
	$helper->setUser( $userLicenseKey->userLicense->userId );
//	$helper->setMode( 'inline' );
	$userName	= $helper->render();
//		$userName	= UI_HTML_Tag::create( 'small', $userLicense->user->firstname.' '.$userLicense->user->firstname, array( 'class' => 'muted' ) );
		$product	= $userLicenseKey->product->title;
/*		if( $userLicenseKey->product->url )
			$product	= UI_HTML_Tag::create( 'a', $product, array(
				'href'		=> $userLicenseKey->product->url,
				'target'	=> '_blank',
			) );*/

		$licenseUid	= UI_HTML_Tag::create( 'a', $userLicenseKey->userLicense->uid, array(
			'href'	=> './manage/my/provision/license/view/'.$userLicenseKey->userLicenseId
		) );

		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', 'Lizenznummer: '.$licenseUid.'<br/>Lizenz: '.$userLicenseKey->productLicense->title.'<br/>Produkt: '.$product.'<br/>Besitzer: '.$userName ),
			UI_HTML_Tag::create( 'td', $status.'<br/>'.$duration ),
			UI_HTML_Tag::create( 'td', 'Schlüssernummer: '.$userLicenseKey->uid ),
		), array( 'class' => $rowColors[$userLicenseKey->status] ) );
	}
}
$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'Lizenz', 'Zustand', 'Lizenzschlüssel' ) ) );
$tbody	= UI_HTML_Tag::create( 'tbody', $list );
$list	= UI_HTML_Tag::create( 'table', $thead.$tbody, array( 'class' => 'table' ) );

$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;neue Lizenz', array(
	'href'	=> './manage/my/provision/license/add',
	'class'	=> 'btn btn-success',
) );

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
