<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$states	= [
	-10	=> '<abbr title="Grund für diese Sperre wurde deaktiviert">deaktiviert</abbr>',
	0	=> 'inaktiv',
	1	=> 'aktiv',
];

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

$lockStates	= [
	0	=> 'nur Sperranfrage',
	1	=> 'aktive Sperre',
];

$lockStates	= [
	0	=> 'Anfrage',
	1	=> 'Sperre',
];

$list	= '<div><em><small>Keine IP-Lock-Filter gefunden.</small></em></div>';
if( $filters ){
	$list	= [];
	foreach( $filters as $filter ){
		if( $filter->reason->status < 1 )
			$filter->status	= -10;

		$buttonEdit		= HtmlTag::create( 'a', $iconEdit, [
			'href'		=> './manage/ip/lock/filter/edit/'.$filter->ipLockFilterId,
			'class'		=> 'btn not-btn-primary btn-small btn-mini',
			'title'		=> 'edit',
		] );
		$buttonStatus	= "";
		if( in_array( $filter->status, [0] ) ){
			$buttonStatus	= HtmlTag::create( 'a', $iconActivate, [
				'href'		=> './manage/ip/lock/filter/activate/'.$filter->ipLockFilterId,
				'class'		=> 'btn btn-success btn-small btn-mini',
				'title'		=> 'aktivieren',
			] );
		}
		else if( in_array( $filter->status, [1] ) ){
			$buttonStatus	= HtmlTag::create( 'a', $iconDeactivate, [
				'href'		=> './manage/ip/lock/filter/deactivate/'.$filter->ipLockFilterId,
				'class'		=> 'btn btn-inverse btn-small btn-mini',
				'title'		=> 'deaktivieren',
			] );
		}
/*		$buttonRemove	= HtmlTag::create( 'a', $iconRemove, [
			'href'		=> './manage/ip/lock/filter/remove/'.$filter->ipLockFilterId,
			'class'		=> 'btn btn-inverse btn-small btn-mini',
			'title'		=> 'remove',
		] );*/
		$appliedAt	= $filter->appliedAt ? date( 'd.m.Y H:i:s', $filter->appliedAt ) : '-';
		if( $filter->appliedAt && $helperTime )
			$appliedAt	= 'vor '.$helperTime->convert( $filter->appliedAt, TRUE );

		$method		= $filter->method ?: '<span class="muted">alle</span>';
		$lockStatus	= $lockStates[$filter->lockStatus];
		$buttons	= HtmlTag::create( 'div', $buttonEdit.$buttonStatus/*.$buttonRemove*/, ['class' => 'btn-group'] );
		$link		= HtmlTag::create( 'a', $filter->title, ['href' => './manage/ip/lock/filter/edit/'.$filter->ipLockFilterId] );
		$title		= HtmlTag::create( 'div', $link, ['class' => 'autocut'] );
		$rowClass	= 'success';
		if( $filter->status < 1 )
			$rowClass	= 'warning';
		if( $filter->status < 0 )
			$rowClass	= 'info';

		$reason	= HtmlTag::create( 'div', $filter->reason->title, ['class' => 'autocut'] );
		$list[]		= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $method, ['class' => 'lock-filter-method'] ),
			HtmlTag::create( 'td', $title, ['class' => 'lock-filter-title'] ),
			HtmlTag::create( 'td', $reason, ['class' => 'lock-filter-reason'] ),
			HtmlTag::create( 'td', $lockStatus, ['class' => 'lock-filter-lock-status'] ),
			HtmlTag::create( 'td', $states[$filter->status], ['class' => 'lock-filter-status'] ),
			HtmlTag::create( 'td', '<small>'.$appliedAt.'</small>', ['class' => 'lock-filter-applied'] ),
			HtmlTag::create( 'td', $buttons, ['class' => 'lock-buttons'] ),
		), ['class' => $rowClass] );
	}
	$heads	= [
		'Methode',
		'Titel',
		'Grund',
		'Folge',
		'Zustand',
		'Anwendung',
		'Aktion',
	];
	$colgroup	= HtmlElements::ColumnGroup( "80px", "", "", "90px", "120px", "110px", "80px" );
	$thead	= HtmlTag::create( 'thead', HtmlElements::TableHeads( $heads ) );
	$tbody	= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-condensed'] );
}

$buttonAdd		= HtmlTag::create( 'a', $iconAdd.' hinzufügen', [
	'href'	=> './manage/ip/lock/filter/add',
	'class'	=> 'btn btn-primary',
] );

$panelList	= HTML::DivClass( 'content-panel',
	HTML::H3( 'IP-Sperr-Filter' ).
	HTML::DivClass( 'content-panel-inner',
		$list.
		HTML::DivClass( 'buttonbar',
			$buttonAdd
		)
	)
);

$tabs	= View_Manage_Ip_Lock::renderTabs( $env, 'filter' );
return $tabs.$panelList;
