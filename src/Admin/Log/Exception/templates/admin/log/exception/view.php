<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;

/** @var Web $env */
/** @var View_Admin_Log_Exception $view */
/** @var array<array<string,string>> $words */
/** @var object $server */
/** @var Entity_Log_Exception $exception */
/** @var ?Entity_Log_Request $request */
/** @var int $page */
/** @var array $exceptionEnv */
/** @var HttpRequest|Dictionary $exceptionRequest */
/** @var Dictionary $exceptionSession */
/** @var ?object $user */
/** @var bool $canIndex */
/** @var bool $canRemove */

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );

if( NULL !== $request ){
	$sections	= [
		'facts'		=> $view->renderFactsSection( $exception, $exceptionEnv, $request ),
		'file'		=> $view->renderFileSection( $exception ),
		'trace'		=> $view->renderTraceSection( $exception, $exceptionEnv ),
		'request'	=> $view->renderRequestSection2( $request ),
		'session'	=> $view->renderSessionSection2( $request ),
		'user'		=> $view->renderUserSection( $exception, $user ),
	];

}
else{
	$sections	= [
		'facts'		=> $view->renderFactsSection( $exception, $exceptionEnv, $exceptionRequest ),
		'file'		=> $view->renderFileSection( $exception ),
		'trace'		=> $view->renderTraceSection( $exception, $exceptionEnv ),
		'request'	=> $view->renderRequestSection( $exception, $exceptionRequest ),
		'session'	=> $view->renderSessionSection( $exception, $exceptionSession ),
		'user'		=> $view->renderUserSection( $exception, $user ),
	];

}
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
				'.join( '<hr/>', array_filter( $sections ) ).'
				<div class="buttonbar">
					'.$buttonCancel.'
					'.$buttonRemove.'
				</div>
			</div>
		</div>
	</div>
</div>';

