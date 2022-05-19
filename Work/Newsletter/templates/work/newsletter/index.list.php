<?php

$w		= (object) $words->index;

$statusIcons		= array(
	-1		=> 'remove',
	0		=> 'star',
	1		=> 'check',
	2		=> 'globe',
);
$statusColors	= array(
	-1		=> "error",
	0		=> "warning",
	1		=> "success",
	2		=> "info",
);

extract( $view->populateTexts( array( 'noneExisting', 'noneFound' ), 'html/work/newsletter/index/list/' ) );

$list		= $textNoneExisting;
if( $total ){
	$list		= $textNoneFound;
	if( $newsletters ){
		$list	= [];
		foreach( $newsletters as $newsletter ){
			$attributes		= array( 'href' => './work/newsletter/edit/'.$newsletter->newsletterId );
			$link			= UI_HTML_Tag::create( 'a', $newsletter->title, $attributes );
			$iconStatus		= UI_HTML_Tag::create( 'i', "", array( 'class' => 'fa fa-fw fa-'.$statusIcons[$newsletter->status] ) );
			$cellLink		= UI_HTML_Tag::create( 'td', $link );
			$cellStatus		= UI_HTML_Tag::create( 'td', $iconStatus.' '.$words->states[$newsletter->status] );
			$cellCreated	= UI_HTML_Tag::create( 'td', date( 'd.m.Y', $newsletter->createdAt ) );
			$cellModified	= UI_HTML_Tag::create( 'td', $newsletter->modifiedAt ? date( 'd.m.Y', $newsletter->modifiedAt ) : '-' );
			$cellSent		= UI_HTML_Tag::create( 'td', $newsletter->sentAt ? date( 'd.m.Y', $newsletter->sentAt ) : '-' );
			$rowColor		= $statusColors[$newsletter->status];
			$cells			= array( $cellLink, $cellStatus, $cellCreated, $cellModified, $cellSent );
			$attributes		= array( 'class' => $rowColor );
			$list[]			= UI_HTML_Tag::create( 'tr', $cells, $attributes );
		}
		$tableRows		= join( $list );
		$tableHeads		= UI_HTML_Elements::TableHeads( array( $w->columnTitle, $w->columnStatus, $w->columnCreatedAt, $w->columnModifiedAt, $w->columnSentAt ) );
		$tableColumns	= UI_HTML_Elements::ColumnGroup( array( '', '140px', '120px', '120px', '120px' ) );
		$tableHead		= UI_HTML_Tag::create( 'thead', $tableHeads );
		$tableBody		= UI_HTML_Tag::create( 'tbody', $tableRows );
		$list			= UI_HTML_Tag::create( 'table', $tableColumns.$tableHead.$tableBody, array( 'class' => 'table table-condensed table-hover table-striped' ) );
	}
}
$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) ).'&nbsp;';

$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.$w->link_add, array(
	'href'	=> './work/newsletter/add',
	'class'	=> 'btn btn-small btn-success btn-small'
) );

$modalAddTrigger	= new \CeusMedia\Bootstrap\Modal\Trigger( 'modal-add-trigger' );
$modalAddTrigger->setModalId( 'modal-add' );
$modalAddTrigger->setLabel( $iconAdd.$w->link_add );
$modalAddTrigger->setAttributes( array( 'class' => 'btn btn-success' ) );

$buttonAdd	= $modalAddTrigger;

if( $limiter && $limiter->denies( 'Work.Newsletter.Newsletter:maxItems', count( $newsletters ) + 1 ) ){
	$buttonAdd	= UI_HTML_Tag::create( 'button', $iconAdd.$w->link_add, array(
		'class'		=> 'btn btn-small btn-success disabled',
		'onclick'	=> 'alert("Weitere Kampagnen sind in dieser Demo-Installation nicht möglich.")',
	) );
}

$pagination	= new \CeusMedia\Bootstrap\PageControl( './work/newsletter', $page, $pages );

$panelFilter	= $view->loadTemplateFile( 'work/newsletter/index.filter.php', array( 'inlineFilter' => TRUE ) );

return '
<div class="content-panel">
	<h3>'.$words->index->heading.'</h3>
	<div class="content-panel-inner">
		'.$panelFilter.'
		'.$list.'
		<div class="buttonbar">
			'.$buttonAdd.'
			'.$pagination.'
		</div>
	</div>
</div>';
