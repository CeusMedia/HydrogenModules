<?php

$w	= (object) $words['index.list'];

$iconsStatus	= array(
	Model_Provision_User_License::STATUS_DEACTIVATED	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) ),
	Model_Provision_User_License::STATUS_REVOKED		=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) ),
	Model_Provision_User_License::STATUS_NEW			=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-pause' ) ),
	Model_Provision_User_License::STATUS_ACTIVE			=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-play' ) ),
 	Model_Provision_User_License::STATUS_EXPIRED		=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-stop' ) ),
);

$iconView		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconCart		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-shopping-cart' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-times' ) );
$iconActivate	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-unlock' ) );
$iconDeactivate	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-lock' ) );
$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );

$list	= '<div class="muted"><em>Keine vorhanden.</em></div><br/>';

$rowColors	= array(
	-1	=> '',
	0	=> 'warning',
	1	=> 'success',
	2	=> 'info',
);

$helper		= new View_Helper_Member( $this->env );

if( $userLicenses ){
	$list	= [];
	foreach( $userLicenses as $userLicense ){
		$rowColor	= $rowColors[$userLicense->status];
		$link	= UI_HTML_Tag::create( 'a', $userLicense->uid, array(
			'href'	=> './manage/my/provision/license/view/'.$userLicense->userLicenseId
		) );

		$buttons	= [];
		$buttons[]	= UI_HTML_Tag::create( 'a', $iconView.' anzeigen', array(
			'href'		=> './manage/my/provision/license/view/'.$userLicense->userLicenseId,
			'class'		=> 'btn btn-small'
		) );
		if( $userLicense->status == 0 ){
			if( isset( $userLicense->bridgeId ) ){
/*				$buttons[]	= UI_HTML_Tag::create( 'a', $iconCart.' weiter', array(
					'href'		=> './shop/addArticle/'.$userLicense->bridgeId.'/'.$userLicense->productLicenseId,
					'class'		=> 'btn btn-small btn-primary'
				) );*/
/*				$buttons[]	= UI_HTML_Tag::create( 'a', $iconRemove.' abbrechen', array(
					'href'		=> './manage/my/provision/license/cancel/'.$userLicense->userLicenseId,
					'class'		=> 'btn btn-small btn-inverse'
				) );*/
			}
		}
		else{
/*			if( $userLicense->status == Model_User_License::STATUS_ACTIVE ){
				$buttons[]	= UI_HTML_Tag::create( 'a', $iconRemove.' vorzeitig beenden', array(
					'href'		=> './manage/my/provision/license/deactivate/'.$userLicense->userLicenseId,
					'class'		=> 'btn btn-small btn-danger'
				) );
			}*/
/*			if( $userLicense->status == 1 ){
				$buttons[]	= UI_HTML_Tag::create( 'a', $iconDeactivate.' sperren', array(
					'href'		=> './manage/my/provision/license/deactivate/'.$userLicense->userLicenseId,
					'class'		=> 'btn btn-small btn-danger'
				) );
			}
			if( $userLicense->status == -1 ){
				$buttons[]	= UI_HTML_Tag::create( 'a', $iconActivate.' entsperren', array(
					'href'		=> './manage/my/provision/license/activate/'.$userLicense->userLicenseId,
					'class'		=> 'btn btn-small btn-success'
				) );
			}*/
		}

		$duration	= '';
		if( $userLicense->status == Model_Provision_User_License::STATUS_ACTIVE ){
			$dateStart	= date( 'd.m.Y', $userLicense->startsAt );
			$dateEnd 	= date( 'd.m.Y', $userLicense->endsAt );
			$duration	= 'läuft: '.$dateStart.' - '.$dateEnd;
		}
		if( $userLicense->status == Model_Provision_User_License::STATUS_EXPIRED ){
			$dateStart	= date( 'd.m.Y', $userLicense->startsAt );
			$dateEnd 	= date( 'd.m.Y', $userLicense->endsAt );
			$duration	= 'lief: '.$dateStart.' - '.$dateEnd;
		}

		$list[]	= UI_HTML_Tag::create( 'tr', array(
/*			UI_HTML_Tag::create( 'td', $link ),*/
			UI_HTML_Tag::create( 'td', $userLicense->product->title ),
			UI_HTML_Tag::create( 'td', $userLicense->productLicense->title ),
			UI_HTML_Tag::create( 'td', $iconsStatus[$userLicense->status].'&nbsp;'.$words['licenseStates'][$userLicense->status] ),
			UI_HTML_Tag::create( 'td', $duration ),
			UI_HTML_Tag::create( 'td', $buttons ),
		), array( 'class' => $rowColor ) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( array( /*"15%", */"20%", "", "15%", "25%", '15%' ) );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( /*'Lizenznummer', */'Produkt', 'Lizenz', 'Zustand', 'Zeitraum', '' ) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table' ) );
}

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
