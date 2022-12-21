<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconAdd			= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );
$iconCancel			= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconUsers			= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-users'] );

$helperTimestamp	= new View_Helper_TimePhraser( $env );

$statusClasses	= array(
	-9	=> 'label-info',
	-2	=> 'label-error',
	-1	=> '',
	0	=> 'label-warning',
	1	=> 'label-warning',
	2	=> 'label-success',
	3	=> 'label-success',
);

$list	= HtmlTag::create( 'div', 'Keine gefunden.', ['class' => 'alert alert-info'] );
if( count( $groups ) ){
	$list	= [];
	foreach( $groups as $group ){
		$label	= HtmlTag::create( 'a', $group->title, ['href' => './work/mail/group/edit/'.$group->mailGroupId] );
		$status	= HtmlTag::create( 'span', $words['group-statuses'][$group->status], ['class' => 'label '.$statusClasses[$group->status]] );
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $label ),
			HtmlTag::create( 'td', $group->address ),
			HtmlTag::create( 'td', $status ),
			HtmlTag::create( 'td', count( $group->members ) ),
			HtmlTag::create( 'td', $helperTimestamp->convert( $group->createdAt, TRUE, 'vor' ) ),
		) );
	}
	$thead	= HtmlTag::create( 'thead', HtmlElements::TableHeads( array(
		'Titel',
		'Adresse',
		'Zustand',
		$iconUsers,
		'erstellt',
	) ) );
	$tbody	= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', [$thead, $tbody], ['class' => 'table table-fixed'] );
}

$panelGroups	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Heading' ),
	HtmlTag::create( 'div', array(
		$list,
		HtmlTag::create( 'div', array(
/*			HtmlTag::create( 'a', $iconCancel.'&nbsp;...', ['href' => './work/mail/group', 'class' => 'btn'] ),*/
			HtmlTag::create( 'a', $iconAdd.'&nbsp;hinzufügen', ['href' => './work/mail/group/add', 'class' => 'btn btn-primary'] ),
		), ['class' => 'buttonbar'] )
	), ['class' => 'content-panel-inner'] )
), ['class' => 'content-panel'] );

$tabs	= $view->renderTabs( $env );

return $tabs.$panelGroups;
