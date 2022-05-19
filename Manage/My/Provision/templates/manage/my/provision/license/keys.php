<?php

$w	= (object) $words['index.list'];

$states	= 	array(
	'0'		=> '<br/><span class="alert alert-error">Noch keiner Person zugewiesen.</span>',
	'1'		=> 'bereit',
	'2'		=> 'aktuell aktiv',
	'3'		=> 'abgelaufen',
);

$iconsStatus	= array(
	0	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove' ) ),
	1	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-pause' ) ),
	2	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-play' ) ),
	3	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-stop' ) ),
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
		$link	= UI_HTML_Tag::create( 'a', $userLicenseKey->userLicenseKeyId, array(
			'href'	=> './manage/my/provision/license/view/'.$userLicenseKey->userLicenseKeyId,
		) );
		$userName	= '---';//UI_HTML_Tag::create( 'small', $userLicense->user->firstname.' '.$userLicense->user->firstname, array( 'class' => 'muted' ) );
		$product	= $userLicenseKey->product->title;
		if( $userLicenseKey->product->url )
			$product	= UI_HTML_Tag::create( 'a', $product, array(
				'href'		=> $userLicenseKey->product->url,
				'target'	=> '_blank',
			) );
		$rank++;
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link.'<br/>Nummer: '.$rank ),
			UI_HTML_Tag::create( 'td', $status.'<br/>'.$duration ),
			UI_HTML_Tag::create( 'td', $product.'<br/>Lizenz: '.$userLicenseKey->productLicense->title.'<br/>Besitzer: '.$userName ),
		), array( 'class' => $rowColors[$userLicenseKey->status] ) );
	}
}
$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'Lizenzschlüssel', 'Zustand', 'Lizenz' ) ) );
$tbody	= UI_HTML_Tag::create( 'tbody', $list );
$list	= UI_HTML_Tag::create( 'table', $thead.$tbody, array( 'class' => 'table' ) );

$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;neue Lizenz', array(
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
