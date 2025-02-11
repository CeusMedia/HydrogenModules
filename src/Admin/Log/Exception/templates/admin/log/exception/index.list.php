<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Bootstrap\Nav\PageControl;
use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */

/** @var array<array<string,string>> $words */
/** @var object $instances */
/** @var array<object> $exceptions */
/** @var int $page */
/** @var int $total */
/** @var int $limit */
/** @var ?string $currentInstance */

$w	= (object) $words['index.list'];

$modelUser	= new Model_User( $this->env );

$iconView	= HtmlTag::create( 'i', '', ['class' => 'fa fa-eye'] );
$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'fa fa-remove'] );
$iconUser	= HtmlTag::create( 'i', '', ['class' => 'fa fa-user'] );

$from		= 'admin/log/exception'.( $page ? '/'.$page : '' );

$selectInstance	= '';
if( count( $instances ) > 1 ){
	$optInstance	= [];
	foreach( $instances as $instanceKey => $instanceData )
		$optInstance[$instanceKey]	= $instanceData->title;
	$optInstance	= HtmlElements::Options( $optInstance, $currentInstance );
	$selectInstance	= HtmlTag::create( 'select', $optInstance, [
		'oninput'	=> 'document.location.href = "./admin/log/exception/setInstance/" + jQuery(this).val();',
		'class'		=> '',
		'style'		=> 'width: 100%',
	] );

}

$dropdown	= '';
$table		= '<div class="muted"><em><small>No exceptions logged.</small></em></div>';
if( $exceptions ){
	$list	= [];
	foreach( $exceptions as $nr => $exception ){
//print_m($exception);die;
		$exceptionEnv		= unserialize( $exception->env );
		$exceptionRequest	= unserialize( $exception->request );
		$exceptionSession	= new Dictionary();
		if( NULL !== $exception->session )
			$exceptionSession	= new Dictionary( unserialize( $exception->session ) );

		$link	= HtmlTag::create( 'a', $exception->message, ['href' => './admin/log/exception/view/'.$exception->exceptionId] );
		$date	= date( 'Y.m.d', $exception->createdAt );
		$time	= date( 'H:i:s', $exception->createdAt );

		$buttons	= HtmlTag::create( 'div', [
			HtmlTag::create( 'a', $iconView, [
				'class'	=> 'btn not-btn-mini btn-small not-btn-info',
				'href'	=> './admin/log/exception/view/'.$exception->exceptionId
			] ),
			HtmlTag::create( 'a', $iconRemove, [
				'class'	=> 'btn not-btn-mini btn-small btn-danger',
				'href'	=> './admin/log/exception/remove/'.$exception->exceptionId
			] ),
		], ['class' => 'btn-group'] );

		$checkbox		= HtmlTag::create( 'input', NULL, [
			'type'		=> 'checkbox',
			'class'		=> 'checkbox-item',
			'data-id'	=> $exception->exceptionId,
		] );

		$requestPath	= '<small class="muted">'.htmlentities( $exceptionRequest->get( '__path' ), ENT_QUOTES, 'utf-8' ).'</small>';
		$method			= 'CLI';
		if( str_contains( $exceptionEnv['class'], 'Web' ) && $exceptionRequest instanceof HttpRequest ){
			try{
				$method	= $exceptionRequest->getMethod();
			}
			catch( Error $e ){}
		}
		$envClass		= preg_replace( '/^(\\\\CeusMedia\\\\HydrogenFramework\\\\Environment\\\\)/', '<small class="muted">\\1</small>', $exceptionEnv['class'] );
		$exceptionClass	= preg_replace( '/Exception$/', '', $exception->type );
		$typeClass		= '<small class="muted">'.$exceptionClass.'</small>';

		$icons	= [];

		if( $exceptionSession->get( 'auth_user_id' ) ){
			$user	= $modelUser->get( $exceptionSession->get( 'auth_user_id' ) );
			$icons['user']	= HtmlTag::create( 'span', $iconUser, [
				'title'	=> $user->username.' ('.$user->firstname.' '.$user->surname.')'
			] );
		}

		$list[]			= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $checkbox ),
			HtmlTag::create( 'td', $link.'<br/>'.$method.' '.$requestPath, ['class' => 'autocut'] ),
//			HtmlTag::create( 'td', $envClass ),
//			HtmlTag::create( 'td', '<small class="muted">'.$exceptionClass.'</small>' ),

			HtmlTag::create( 'td', $icons ),
			HtmlTag::create( 'td', $typeClass.'<br/>'.$date.'&nbsp;<small class="muted">'.$time.'</small>' ),
			HtmlTag::create( 'td', $buttons ),
		] );
	}


	$checkboxAll	= HtmlTag::create( 'input', NULL, [
		'type'	=> 'checkbox',
		'id'	=> 'admin-log-exception-list-all-items-toggle',
	] );

	$heads	= HtmlElements::TableHeads( [
		$checkboxAll,
//		'',
		'',
		'',
	] );

	$colgroup	= HtmlElements::ColumnGroup( '20px', ''/*, '180px'*//*, '180px'*/, '60px', '150px', '100px' );
	$thead		= HtmlTag::create( 'thead', $heads );
	$tbody		= HtmlTag::create( 'tbody', $list );
	$table		= HtmlTag::create( 'table', [$colgroup, $thead, $tbody], [
		'class'	=> 'table table-striped table-condensed',
		'style'	=> 'table-layout: fixed'
	] );

	if( count( $exceptions ) > 1 ){
		$dropdownMenu	= HtmlTag::create( 'ul', [
			HtmlTag::create( 'li',
				HtmlTag::create( 'a', '<i class="fa fa-trash"></i> '.$w->buttonRemove, ['class' => '#', 'id' => 'action-button-remove'] )
			),
		], ['class' => 'dropdown-menu not-pull-right'] );

		$dropdownToggle	= HtmlTag::create( 'button', $w->buttonAction.' <span class="caret"></span>', [
			'type'		=> 'button',
			'class'		=> 'btn dropdown-toggle',
		], ['toggle' => 'dropdown'] );
		$dropdown		= HtmlTag::create( 'div', [$dropdownToggle, $dropdownMenu], ['class' => 'btn-group dropup'] );
	}
}

$pagination	= new PageControl( './admin/log/exception', $page, ceil( $total / $limit ) );
$pagination	= $pagination->render();

return '
<div class="content-panel" style="position: relative">
	<div style="position: absolute; right: 1em; top: 0.65em; width: 150px;">
		'.$selectInstance.'
	</div>
	<h3>Exceptions</h3>
	<div class="content-panel-inner">
		<form action="admin/log/exception/bulk" method="post" id="form-admin-log-exception">
			<input type="hidden" name="type" id="input_type"/>
			<input type="hidden" name="ids" id="input_ids"/>
			<input type="hidden" name="from" value="'.$from.'"/>
			'.$table.'
			<div class="buttonbar">
				'.$pagination.'
				'.$dropdown.'
			</div>
		</form>
	</div>
</div>';
