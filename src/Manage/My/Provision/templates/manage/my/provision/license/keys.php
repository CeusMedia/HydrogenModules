<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['index.list'];

$states	= 	array(
	'0'		=> '<br/><span class="alert alert-error">Noch keiner Person zugewiesen.</span>',
	'1'		=> 'bereit',
	'2'		=> 'aktuell aktiv',
	'3'		=> 'abgelaufen',
);

$iconsStatus	= array(
	0	=> HtmlTag::create( 'i', '', ['class' => 'icon-remove'] ),
	1	=> HtmlTag::create( 'i', '', ['class' => 'icon-pause'] ),
	2	=> HtmlTag::create( 'i', '', ['class' => 'icon-play'] ),
	3	=> HtmlTag::create( 'i', '', ['class' => 'icon-stop'] ),
);

$list	= '<div class="muted"><em>Keine vorhanden.</em></div><br/>';

$rowColors	= array(
	0	=> '',
	1	=> 'warning',
	2	=> 'success',
	3	=> 'info',
);
if( $userLicenseKeys ){
	$rank	= 0;
	$list	= [];
	foreach( $userLicenseKeys as $userLicenseKey ){
		$class		= NULL;
		$dateStart	= NULL;
		$dateEnd	= NULL;
		$duration	= '';
		$status		= $iconsStatus[$userLicenseKey->status].'&nbsp'.$states[$userLicenseKey->status];
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
		$link	= HtmlTag::create( 'a', $userLicenseKey->userLicenseKeyId, array(
			'href'	=> './manage/my/provision/license/view/'.$userLicenseKey->userLicenseKeyId,
		) );
		$userName	= '---';//HtmlTag::create( 'small', $userLicense->user->firstname.' '.$userLicense->user->firstname, ['class' => 'muted'] );
		$product	= $userLicenseKey->product->title;
		if( $userLicenseKey->product->url )
			$product	= HtmlTag::create( 'a', $product, array(
				'href'		=> $userLicenseKey->product->url,
				'target'	=> '_blank',
			) );
		$rank++;
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $link.'<br/>Nummer: '.$rank ),
			HtmlTag::create( 'td', $status.'<br/>'.$duration ),
			HtmlTag::create( 'td', $product.'<br/>Lizenz: '.$userLicenseKey->productLicense->title.'<br/>Besitzer: '.$userName ),
		), ['class' => $rowColors[$userLicenseKey->status]] );
	}
}
$thead	= HtmlTag::create( 'thead', HtmlElements::TableHeads( ['Lizenzschlüssel', 'Zustand', 'Lizenz'] ) );
$tbody	= HtmlTag::create( 'tbody', $list );
$list	= HtmlTag::create( 'table', $thead.$tbody, ['class' => 'table'] );

$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'icon-plus icon-white'] );
$buttonAdd	= HtmlTag::create( 'a', $iconAdd.'&nbsp;neue Lizenz', array(
	'href'	=> './manage/my/provision/license/add',
	'class'	=> 'btn btn-success',
) );

$panelList		= '
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

$panelFilter	= $view->loadTemplateFile( 'manage/my/provision/license/index.filter.php' );

return '
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span9">
		'.$panelList.'
	</div>
</div>';
