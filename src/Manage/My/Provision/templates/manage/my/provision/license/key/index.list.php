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

$list	= '<div class="muted"><em>Keine vorhanden.</em></div><br/>';

$rowColors	= array(
	0	=> '',
	1	=> 'warning',
	2	=> 'success',
	3	=> 'info',
);

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
		$link	= HtmlTag::create( 'a', $userLicenseKey->userLicenseId, array(
			'href'	=> './manage/my/provision/license/view/'.$userLicenseKey->userLicenseId,
		) );
	$helper->setUser( $userLicenseKey->userLicense->userId );
//	$helper->setMode( 'inline' );
	$userName	= $helper->render();
//		$userName	= HtmlTag::create( 'small', $userLicense->user->firstname.' '.$userLicense->user->firstname, ['class' => 'muted'] );
		$product	= $userLicenseKey->product->title;
/*		if( $userLicenseKey->product->url )
			$product	= HtmlTag::create( 'a', $product, array(
				'href'		=> $userLicenseKey->product->url,
				'target'	=> '_blank',
			) );*/

		$licenseUid	= HtmlTag::create( 'a', $userLicenseKey->userLicense->uid, array(
			'href'	=> './manage/my/provision/license/view/'.$userLicenseKey->userLicenseId
		) );

		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', 'Lizenznummer: '.$licenseUid.'<br/>Lizenz: '.$userLicenseKey->productLicense->title.'<br/>Produkt: '.$product.'<br/>Besitzer: '.$userName ),
			HtmlTag::create( 'td', $status.'<br/>'.$duration ),
			HtmlTag::create( 'td', 'Schlüssernummer: '.$userLicenseKey->uid ),
		), ['class' => $rowColors[$userLicenseKey->status]] );
	}
}
$thead	= HtmlTag::create( 'thead', HtmlElements::TableHeads( ['Lizenz', 'Zustand', 'Lizenzschlüssel'] ) );
$tbody	= HtmlTag::create( 'tbody', $list );
$list	= HtmlTag::create( 'table', $thead.$tbody, ['class' => 'table'] );

$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'icon-plus icon-white'] );
$buttonAdd	= HtmlTag::create( 'a', $iconAdd.'&nbsp;neue Lizenz', array(
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
