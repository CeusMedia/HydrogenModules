<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;

/** @var Web $env */
/** @var View_Admin_Log_Exception $view */
/** @var array<array<string,string>> $words */
/** @var object $server */
/** @var object $exception */
/** @var int $page */
/** @var array $exceptionEnv */
/** @var Dictionary $exceptionRequest */
/** @var Dictionary $exceptionSession */
/** @var ?object $user */

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );

// already done in controller
//$exceptionEnv		= unserialize( $exception->env );
//$exceptionRequest	= unserialize( $exception->request );
//$exceptionSession	= unserialize( $exception->session );

//print_m($exception);die;
//print_m($exceptionRequest->getAll());die;

$sections	= [
	'facts'		=> $view->renderFactsSection( $exception, $exceptionEnv, $exceptionRequest ),
	'file'		=> $view->renderFileSection( $exception ),
	'trace'		=> $view->renderTraceSection( $exception ),
	'request'	=> $view->renderRequestSection( $exception, $exceptionRequest ),
	'session'	=> $view->renderSessionSection( $exception, $exceptionSession ),
	'user'		=> $view->renderUserSection( $exception, $user ),
];

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.'&nbsp;zur Liste', [
	'href'		=> './admin/log/exception'.( $page ? '/'.$page : '' ),
	'class'		=> 'btn btn-small',
] );
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
				'.join( '<hr/>', array_filter( $sections ) ).'
				<div class="buttonbar">
					'.$buttonCancel.'
					'.$buttonRemove.'
				</div>
			</div>
		</div>
	</div>
</div>';

