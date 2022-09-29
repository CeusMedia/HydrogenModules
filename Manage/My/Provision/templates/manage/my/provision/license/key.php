<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

if(0){
print_m( $userLicense );
print_m( $product );
print_m( $userLicenseKey );
die;
}

function renderDefinitionList( $data ){
	if( !count( $data ) )
		return '';
	$list	= [];
	foreach( $data as $key => $value ){
		$list[]	= HtmlTag::create( 'dt', $key );
		$list[]	= HtmlTag::create( 'dd', $value );
	}
	return HtmlTag::create( 'dl', $list, ['class' => 'dl-horizontal'] );
}

/*		if( $userLicenseKey->status == 2 ){
			$dateStart	= date( 'd.m.Y', $userLicenseKey->startsAt );
			$dateEnd 	= date( 'd.m.Y', $userLicenseKey->endsAt );
			$duration	= 'läuft: '.$dateStart.' - '.$dateEnd;
		}
		if( $userLicenseKey->status == 3 ){
			$dateStart	= date( 'd.m.Y', $userLicenseKey->startsAt );
			$dateEnd 	= date( 'd.m.Y', $userLicenseKey->endsAt );
			$duration	= 'lief: '.$dateStart.' - '.$dateEnd;
		}
*/

$data1	= [];
$data1['Produkt']				= $product->title;
$data1['Lizenz']				= $userLicense->productLicense->title;
$data1['Schlüssel in Lizenz']	= $userLicense->users;
$data1['Schlüssel vergeben']	= $userLicense->users;
$data1['Preis']					= $userLicense->price;
//$data1['davon vergeben']		= $userLicense->users;

$list1	= renderDefinitionList( $data1 );

$panelFacts	= '
<div class="content-panel">
	<h3><span class="muted">Schlüssel: </span>'.$userLicenseKey->userLicenseKeyId.'</h3>
	<div class="content-panel-inner">
		'.$list1.'
		<div class="buttonbar">
			<a href="./manage/my/provision/license" class="btn btn-small"><i class="icon-arrow-left"></i>&nbsp;zurück</a>

		</div>
	</div>
</div>';

$panelFilter	= $view->loadTemplateFile( 'manage/my/provision/license/index.filter.php' );

return '
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span9">
		'.$panelFacts.'
		<br/>
	</div>
</div>
';
