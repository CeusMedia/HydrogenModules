<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['index.list'];

$iconsStatus	= array(
	Model_Provision_User_License::STATUS_DEACTIVATED	=> HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) ),
	Model_Provision_User_License::STATUS_REVOKED		=> HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) ),
	Model_Provision_User_License::STATUS_NEW			=> HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-pause' ) ),
	Model_Provision_User_License::STATUS_ACTIVE			=> HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-play' ) ),
 	Model_Provision_User_License::STATUS_EXPIRED		=> HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-stop' ) ),
);

$iconView		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconCart		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-shopping-cart' ) );
$iconRemove		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-times' ) );
$iconActivate	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-unlock' ) );
$iconDeactivate	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-lock' ) );
$iconAdd		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );

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
		$link	= HtmlTag::create( 'a', $userLicense->uid, array(
			'href'	=> './manage/my/provision/license/view/'.$userLicense->userLicenseId
		) );

		$buttons	= [];
		$buttons[]	= HtmlTag::create( 'a', $iconView.' anzeigen', array(
			'href'		=> './manage/my/provision/license/view/'.$userLicense->userLicenseId,
			'class'		=> 'btn btn-small'
		) );
		if( $userLicense->status == 0 ){
			if( isset( $userLicense->bridgeId ) ){
/*				$buttons[]	= HtmlTag::create( 'a', $iconCart.' weiter', array(
					'href'		=> './shop/addArticle/'.$userLicense->bridgeId.'/'.$userLicense->productLicenseId,
					'class'		=> 'btn btn-small btn-primary'
				) );*/
/*				$buttons[]	= HtmlTag::create( 'a', $iconRemove.' abbrechen', array(
					'href'		=> './manage/my/provision/license/cancel/'.$userLicense->userLicenseId,
					'class'		=> 'btn btn-small btn-inverse'
				) );*/
			}
		}
		else{
/*			if( $userLicense->status == Model_User_License::STATUS_ACTIVE ){
				$buttons[]	= HtmlTag::create( 'a', $iconRemove.' vorzeitig beenden', array(
					'href'		=> './manage/my/provision/license/deactivate/'.$userLicense->userLicenseId,
					'class'		=> 'btn btn-small btn-danger'
				) );
			}*/
/*			if( $userLicense->status == 1 ){
				$buttons[]	= HtmlTag::create( 'a', $iconDeactivate.' sperren', array(
					'href'		=> './manage/my/provision/license/deactivate/'.$userLicense->userLicenseId,
					'class'		=> 'btn btn-small btn-danger'
				) );
			}
			if( $userLicense->status == -1 ){
				$buttons[]	= HtmlTag::create( 'a', $iconActivate.' entsperren', array(
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

		$list[]	= HtmlTag::create( 'tr', array(
/*			HtmlTag::create( 'td', $link ),*/
			HtmlTag::create( 'td', $userLicense->product->title ),
			HtmlTag::create( 'td', $userLicense->productLicense->title ),
			HtmlTag::create( 'td', $iconsStatus[$userLicense->status].'&nbsp;'.$words['licenseStates'][$userLicense->status] ),
			HtmlTag::create( 'td', $duration ),
			HtmlTag::create( 'td', $buttons ),
		), array( 'class' => $rowColor ) );
	}
	$colgroup	= HtmlElements::ColumnGroup( array( /*"15%", */"20%", "", "15%", "25%", '15%' ) );
	$thead	= HtmlTag::create( 'thead', HtmlElements::TableHeads( array( /*'Lizenznummer', */'Produkt', 'Lizenz', 'Zustand', 'Zeitraum', '' ) ) );
	$tbody	= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table' ) );
}

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
