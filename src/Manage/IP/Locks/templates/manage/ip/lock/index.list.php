<?php

use CeusMedia\Bootstrap\Nav\PageControl;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var array<Entity_IP_Lock> $locks */
/** @var int $total */
/** @var int $count */
/** @var int $limit */
/** @var int $page */
/** @var bool $canAdd */
/** @var bool $canCancel */
/** @var bool $canEdit */
/** @var bool $canLock */
/** @var bool $canOrder */
/** @var bool $canUnlock */

$helperTime = FALSE;
if( $env->getModules()->has( 'UI_Helper_TimePhraser' ) )
    $helperTime     = new View_Helper_TimePhraser( $env );

$urlSuffixFrom	= '';
if( $page > 0 )
	$urlSuffixFrom	= '?from=manage/ip/lock/'.$limit.'/'.$page;

$helper		= new View_Helper_Manage_IP_Lock_List( $env );

//$uri			= './manage/ip/lock/'.$limit;
$table			= $helper->renderList( $locks, $urlSuffixFrom, $helperTime );
$buttonAdd		= $helper->renderAddButton();
$pagination		= new PageControl( './manage/ip/lock/15', $page, ceil( $total / 15 ) );
$listNumbers	= $helper->renderListNumbers( $page, $limit, $count, $total );

$panelList	= HTML::DivClass( 'content-panel',
	HtmlTag::create( 'h3', 'IP-Sperren&nbsp;'.$listNumbers ).
	HTML::DivClass( 'content-panel-inner',
		$table.
		HTML::DivClass( 'buttonbar',
			HTML::DivClass( 'btn-toolbar',
				$pagination.
				$buttonAdd
			)
		)
	)
);

return $panelList;
