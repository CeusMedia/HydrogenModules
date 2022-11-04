<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$states	= array(
	0	=> 'disabled',
	1	=> 'active',
);

$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'icon-plus icon-white'] );
$iconEdit	= HtmlTag::create( 'i', '', ['class' => 'icon-pencil'] );
$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'icon-trash icon-white'] );
$iconActivate	= HtmlTag::create( 'i', '', ['class' => 'icon-ok icon-white'] );
$iconDeactivate	= HtmlTag::create( 'i', '', ['class' => 'icon-remove icon-white'] );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconAdd		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-plus fa-inverse'] );
	$iconEdit		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-pencil'] );
	$iconRemove		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-trash fa-inverse'] );
	$iconActivate	= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-check fa-inverse'] );
	$iconDeactivate	= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-times fa-inverse'] );
}
$helperTime	= FALSE;
if( $env->getModules()->has( 'UI_Helper_TimePhraser' ) ){
	$helperTime		= new View_Helper_TimePhraser( $env );
}

$list	= '<div><em><small>Keine IP-Lock-Gründe gefunden.</small></em></div>';
if( $reasons ){
	$list	= [];
	foreach( $reasons as $reason ){
		$buttonEdit		= HtmlTag::create( 'a', $iconEdit, array(
			'href'		=> './manage/ip/lock/reason/edit/'.$reason->ipLockReasonId,
			'class'		=> 'btn not-btn-primary btn-small btn-mini',
			'title'		=> 'bearbeiten',
		) );
		$buttonStatus	= HtmlTag::create( 'a', $iconActivate, array(
			'href'		=> './manage/ip/lock/reason/activate/'.$reason->ipLockReasonId,
			'class'		=> 'btn btn-success btn-small btn-mini',
			'title'		=> 'aktivieren',
		) );
		if( $reason->status ){
			$buttonStatus	= HtmlTag::create( 'a', $iconDeactivate, array(
				'href'		=> './manage/ip/lock/reason/deactivate/'.$reason->ipLockReasonId,
				'class'		=> 'btn btn-inverse btn-small btn-mini',
				'title'		=> 'deaktivieren',
			) );
		}
/*		$buttonRemove	= HtmlTag::create( 'a', $iconRemove, array(
			'href'		=> './manage/ip/lock/reason/remove/'.$reason->ipLockReasonId,
			'class'		=> 'btn btn-inverse btn-small btn-mini',
			'title'		=> 'entfernen',
		) );*/
		$createdAt	= $reason->createdAt ? date( 'd.m.Y H:i:s', $reason->createdAt ) : '-';
		if( $reason->createdAt && $helperTime )
			$createdAt	= 'vor '.$helperTime->convert( $reason->createdAt, TRUE );
		$appliedAt	= $reason->appliedAt ? date( 'd.m.Y H:i:s', $reason->appliedAt ) : '-';
		if( $reason->appliedAt && $helperTime )
			$appliedAt	= 'vor '.$helperTime->convert( $reason->appliedAt, TRUE );
		$httpCode	= HtmlTag::create( 'abbr', $reason->code, ['title' => Net_HTTP_Status::getText( $reason->code )] );
		$duration	= $reason->duration ? $reason->duration : '-';
		if( $reason->duration && $helperTime )
			$duration	= 'nach '.$helperTime->convert( time() - $reason->duration, !TRUE );

		$link		= HtmlTag::create( 'a', $reason->title, ['href' => './manage/ip/lock/reason/edit/'.$reason->ipLockReasonId] );
		$buttons	= HtmlTag::create( 'div', $buttonEdit.$buttonStatus/*.$buttonRemove*/, ['class' => 'btn-group'] );
		$list[]		= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $httpCode, ['class' => 'lock-reason-code'] ),
			HtmlTag::create( 'td', $link, ['class' => 'lock-reason-title'] ),
			HtmlTag::create( 'td', '<small>'.$duration.'</small>', ['class' => 'lock-reason-duration'] ),
			HtmlTag::create( 'td', '<small>'.$createdAt.'</small>', ['class' => 'lock-reason-created'] ),
			HtmlTag::create( 'td', '<small>'.$appliedAt.'</small>', ['class' => 'lock-reason-applied'] ),
			HtmlTag::create( 'td', $buttons, ['class' => 'lock-buttons'] ),
		), ['class' => $reason->status ? 'success' : 'warning'] );
	}
	$heads	= array(
		HtmlTag::create( 'abbr', 'Code', ['title' => 'HTTP-Status-Code'] ),
		'Titel',
		HtmlTag::create( 'abbr', 'Aufhebung', ['title' => 'Automatische Aufhebung der Sperre'] ),
		'Erstellung',
		HtmlTag::create( 'abbr', 'Anwendung', ['title' => 'Letzte Sperrung aus diesem Grund'] ),
		'Aktion',
	);
	$colgroup	= HtmlElements::ColumnGroup( "50", "", "120", "110", "110", "100" );
	$thead	= HtmlTag::create( 'thead', HtmlElements::TableHeads( $heads ) );
	$tbody	= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-condensed'] );
}

$buttonAdd		= HtmlTag::create( 'a', $iconAdd.' hinzufügen', array(
	'href'	=> './manage/ip/lock/reason/add',
	'class'	=> 'btn btn-primary',
) );

$panelList	= HTML::DivClass( 'content-panel',
	HtmlTag::create( 'h3', 'IP-Sperr-Gründe' ).
	HTML::DivClass( 'content-panel-inner',
		$list.
		HTML::DivClass( 'buttonbar',
			$buttonAdd
		)
	)
);

$tabs	= View_Manage_Ip_Lock::renderTabs( $env, 'reason' );
return $tabs.$panelList;
