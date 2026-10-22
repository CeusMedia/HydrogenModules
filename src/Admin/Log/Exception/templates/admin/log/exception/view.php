<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;

/** @var Web $env */
/** @var View_Admin_Log_Exception $view */
/** @var array<array<string,string>> $words */
/** @var object $server */
/** @var Entity_Log_Exception $exception */
/** @var int $page */
/** @var bool $canIndex */
/** @var bool $canRemove */

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );


$helper	= new View_Helper_Admin_Log_Exception();
$helper->setEnv( $env );
$helper->setExceptionEntity( $exception );

$buttonCancel	= '';
if( $canIndex )
	$buttonCancel	= HtmlTag::create( 'a', $iconCancel.'&nbsp;zur Liste', [
		'href'		=> './admin/log/exception'.( $page ? '/'.$page : '' ),
		'class'		=> 'btn btn-small',
	] );
$buttonRemove	= '';
if( $canRemove )
	$buttonRemove	= HtmlTag::create( 'a', $iconRemove.'&nbsp;entfernen', [
		'href'		=> './admin/log/exception/remove/'.$exception->exceptionId,
		'class'		=> 'btn btn-small btn-danger',
	] );

return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h3>Exception</h3>
			<div class="content-panel-inner">
				'.$helper->render().'
				<br/>
				<div class="buttonbar">
					'.$buttonCancel.'
					'.$buttonRemove.'
				</div>
			</div>
		</div>
	</div>
</div>';

