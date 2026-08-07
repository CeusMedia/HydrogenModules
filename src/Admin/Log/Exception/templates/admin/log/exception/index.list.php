<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Bootstrap\Nav\PageControl;
use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

/** @var WebEnvironment $env */
/** @var View $view */

/** @var array<array<string,string>> $words */
/** @var object $instances */
/** @var array<Entity_Log_Exception> $exceptions */
/** @var int $page */
/** @var int $total */
/** @var int $limit */
/** @var ?string $currentInstance */
/** @var bool $canView */
/** @var bool $canRemove */
/** @var bool $canBulk */
/** @var bool $canSetInstance */

/**
 *	@param		WebEnvironment	$env
 *	@param		Entity_Log_Exception[]	$exceptions
 *	@param		bool			$canView
 *	@param		bool			$canRemove
 *	@param		bool			$canBulk
 *	@return		string
 *	@throws		ReflectionException
 *	@throws		\Psr\SimpleCache\InvalidArgumentException
 */
function renderTable( WebEnvironment $env, array $exceptions, bool $canView, bool $canRemove, bool $canBulk ): string
{
	if( [] === $exceptions )
		return HtmlTag::create( 'div', 'No exceptions logged.', ['class' => "alert alert-success"] );

	$logicUser	= Logic_User::getInstance( $env );
	$iconView	= HtmlTag::create( 'i', '', ['class' => 'fa fa-eye'] );
	$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'fa fa-remove'] );
	$iconUser	= HtmlTag::create( 'i', '', ['class' => 'fa fa-user'] );
	$iconDate	= HtmlTag::create( 'i', '', ['class' => 'fa fa-clock-o'] );
	$list		= [];
	foreach( $exceptions as $nr => $exception ){
		$exceptionEnv		= unserialize( $exception->env );

		if( 0 !== ( (int) $exception->requestId ) && class_exists( 'Model_Log_Request' ) ){
			$modelRequest	= new Model_Log_Request( $env );
			/** @var Entity_Log_Request $request */
			$request		= $modelRequest->get( $exception->requestId );
			$exceptionRequest	= Dictionary::create( json_decode( $request->request, TRUE ) );
			$exceptionSession	= Dictionary::create( json_decode( $request->session, TRUE ) );
			$requestMethod		= $request->method;
			$requestPath	= '<small class="muted">'.htmlentities( $exceptionRequest->get( '__path', '???' ), ENT_QUOTES, 'utf-8' ).'</small>';
		}
		else if( $exception->request ){
			$exceptionRequest	= unserialize( $exception->request );
			$exceptionSession	= new Dictionary();
			if( NULL !== $exception->session ){
				$sessionData	= unserialize( $exception->session );
				if( $sessionData instanceof Dictionary )
					$exceptionSession	= $sessionData;
				else
					$exceptionSession	= new Dictionary( $sessionData );
			}

			$requestPath	= join( ' ', $exceptionRequest->get( 'arguments', [] ) ).' '.join( ' ', $exceptionRequest->get( 'commands', [] ) );
			$requestMethod	= 'CLI';
			if( str_contains( $exceptionEnv['class'], 'Web' ) && $exceptionRequest instanceof HttpRequest ){
				try{
					$requestMethod	= $exceptionRequest->getMethod()->get();
					$requestPath	= '<small class="muted">'.htmlentities( $exceptionRequest->get( '__path', '???' ), ENT_QUOTES, 'utf-8' ).'</small>';
				}
				catch( Error $e ){}
			}
		}
		else{
			$exceptionRequest	= new Dictionary();
			$exceptionSession	= new Dictionary();
			$requestMethod		= '?';
		}

		$link	= HtmlTag::create( 'a', $exception->message, ['href' => './admin/log/exception/view/'.$exception->exceptionId] );
		$date	= date( 'Y-m-d', $exception->createdAt );
		$time	= date( 'H:i:s', $exception->createdAt );
		$factDate	= $iconDate.'&nbsp;'.$date.'&nbsp;<small class="muted">'.$time.'</small>';

		$buttons	= [];
		if( $canView )
			$buttons[]	= HtmlTag::create( 'a', $iconView, [
				'class'	=> 'btn not-btn-mini btn-small not-btn-info',
				'href'	=> './admin/log/exception/view/'.$exception->exceptionId
			] );
		if( $canRemove )
			$buttons[]	= HtmlTag::create( 'a', $iconRemove, [
				'class'	=> 'btn not-btn-mini btn-small btn-danger',
				'href'	=> './admin/log/exception/remove/'.$exception->exceptionId
			] );

		$checkbox		= HtmlTag::create( 'input', NULL, [
			'type'		=> 'checkbox',
			'class'		=> 'checkbox-item',
			'data-id'	=> $exception->exceptionId,
		] );

		$envClass		= preg_replace( '/^(\\\\CeusMedia\\\\HydrogenFramework\\\\Environment\\\\)/', '<small class="muted">\\1</small>', $exceptionEnv['class'] );
		$exceptionClass	= preg_replace( '/Exception$/', '', $exception->type );
		$typeClass		= '<small class="muted">'.$exceptionClass.'</small>';

		$icons	= [];
		$factUser	= '';
		if( NULL !== $exceptionRequest ){
			if( $exceptionSession->get( Logic_Authentication::$sessionKeyAuthUserId ) ){
				$user	= $logicUser->getUser( $exceptionSession->get( Logic_Authentication::$sessionKeyAuthUserId ) );
	/*			if( NULL !== $user )
					$icons['user']	= HtmlTag::create( 'span', $iconUser, [
						'title'	=> $user->username.' ('.$user->firstname.' '.$user->surname.')'
					] );*/
				$factUser	= HtmlTag::create( 'span', $iconUser.'&nbsp;'.$user->username, [
					'title'	=> $user->firstname.' '.$user->surname
				] );
			}
		}

		$list[]			= HtmlTag::create( 'tr', [
			$canBulk ? HtmlTag::create( 'td', $checkbox ) : '',
			HtmlTag::create( 'td', join( '<br/>', [$link, $requestMethod.' '.$requestPath, $typeClass] ), ['class' => 'autocut'] ),
//			HtmlTag::create( 'td', $envClass ),
//			HtmlTag::create( 'td', '<small class="muted">'.$exceptionClass.'</small>' ),

//			HtmlTag::create( 'td', $icons ),
			HtmlTag::create( 'td', join( '<br/>', [$factDate, $factUser] ) ),
			HtmlTag::create( 'td', HtmlTag::create( 'div', $buttons, ['class' => 'btn-group'] ) ),
		] );
	}


	$checkboxAll	= HtmlTag::create( 'input', NULL, [
		'type'	=> 'checkbox',
		'id'	=> 'admin-log-exception-list-all-items-toggle',
	] );

	$heads	= [];
	if( $canBulk )
		$heads[]	= $checkboxAll;
	$heads[]	= 'Message';
	$heads[]	= 'Facts';
	$heads[]	= '';

	$cols		= [];
	if( $canBulk )
		$cols[]	= '22px';
	$cols[]		= '';
	$cols[]		= '160px';
	$cols[]		= '80px';

	return HtmlTag::create( 'table', [
		HtmlElements::ColumnGroup( $cols ),
		HtmlTag::create( 'thead', HtmlElements::TableHeads( $heads ) ),
		HtmlTag::create( 'tbody', $list )
	], [
		'class'	=> 'table table-striped table-condensed',
		'style'	=> 'table-layout: fixed'
	] );
}

/**
 *	@param		array<array<string,string>>	$words
 *	@param		int							$page
 *	@param		int							$limit
 *	@param		int							$total
 *	@param		int							$exceptions
 *	@param		bool						$canRemove
 *	@return		string
 */
function renderButtonBar( array $words, int $page, int $limit, int $total, int $exceptions, bool $canRemove ): string
{
	$w	= (object) $words['index.list'];
	if( 0 === $exceptions )
		return '';

	$dropdown	= '';
	$items		= [];
	if( $canRemove ){
		$items[]	= HtmlTag::create( 'li',
			HtmlTag::create( 'a', '<i class="fa fa-trash"></i> '.$w->buttonRemove, ['class' => '#', 'id' => 'action-button-remove'] )
		);
	}
	if( [] !== $items ){
		$dropdownMenu	= HtmlTag::create( 'ul', $items, ['class' => 'dropdown-menu not-pull-right'] );
		$dropdownToggle	= HtmlTag::create( 'button', $w->buttonAction.' <span class="caret"></span>', [
			'type'		=> 'button',
			'class'		=> 'btn dropdown-toggle',
		], ['toggle' => 'dropdown'] );
		$dropdown		= HtmlTag::create( 'div', [$dropdownToggle, $dropdownMenu], ['class' => 'btn-group dropup'] );
	}

	$pagination	= new PageControl( './admin/log/exception', $page, ceil( $total / $limit ) );
	return HtmlTag::create( 'div', $pagination->render().$dropdown, ['class' => 'buttonbar'] );
}

function renderInstanceSelector( array $instances, ?string $currentInstance, bool $canSetInstance ): string
{
	if( !$canSetInstance || 1 === count( $instances ) )
		return '';

	$selectInstance	= '';
	$optInstance	= [];
	foreach( $instances as $instanceKey => $instanceData )
		$optInstance[$instanceKey]	= $instanceData->title;
	$optInstance	= HtmlElements::Options( $optInstance, $currentInstance );
	$selectInstance	= HtmlTag::create( 'select', $optInstance, [
		'oninput'	=> 'document.location.href = "./admin/log/exception/setInstance/" + jQuery(this).val();',
		'class'		=> '',
		'style'		=> 'width: 100%',
	] );
	return HtmlTag::create( 'div', $selectInstance, ['style' => "position: absolute; right: 1em; top: 0.65em; width: 150px;"] );
}

$instanceSelector	= renderInstanceSelector( $instances, $currentInstance, $canSetInstance );
$table				= renderTable( $env, $exceptions, $canView, $canRemove, $canBulk );
$buttonbar			= renderButtonBar( $words, $page, $limit, $total, count( $exceptions ), $canRemove );

$from		= 'admin/log/exception'.( $page ? '/'.$page : '' );



return '
<form action="admin/log/exception/bulk" method="post" id="form-admin-log-exception">
	<div class="content-panel" style="position: relative">
		'.$instanceSelector.'
		<h3>Exceptions</h3>
		<div class="content-panel-inner">
			<input type="hidden" name="type" id="input_type"/>
			<input type="hidden" name="ids" id="input_ids"/>
			<input type="hidden" name="from" value="'.$from.'"/>
			'.$table.'
			'.$buttonbar.'
		</div>
	</div>
</form>';
