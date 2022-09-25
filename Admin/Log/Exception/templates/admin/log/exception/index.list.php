<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['index.list'];

$modelUser	= new Model_User( $this->env );

$iconView	= HtmlTag::create( 'i', '', ['class' => 'fa fa-eye'] );
$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'fa fa-remove'] );
$iconUser	= HtmlTag::create( 'i', '', ['class' => 'fa fa-user'] );

$from		= 'admin/log/exception'.($page ? '/'.$page : '' );

$selectInstance	= '';
if( count( $instances ) > 1 ){
	$optInstance	= [];
	foreach( $instances as $instanceKey => $instanceData )
		$optInstance[$instanceKey]	= $instanceData->title;
	$optInstance	= HtmlElements::Options( $optInstance, $currentInstance );
	$selectInstance	= HtmlTag::create( 'select', $optInstance, array(
		'oninput'	=> 'document.location.href = "./admin/log/exception/setInstance/" + jQuery(this).val();',
		'class'		=> '',
		'style'		=> 'width: 100%',
	) );

}

$dropdown	= '';
$table		= '<div class="muted"><em><small>No exceptions logged.</small></em></div>';
if( $exceptions ){
	$list	= [];
	foreach( $exceptions as $nr => $exception ){
//print_m($exception);die;
		$exceptionEnv		= unserialize( $exception->env );
		$exceptionRequest	= unserialize( $exception->request );
		$exceptionSession	= new ADT_List_Dictionary( unserialize( $exception->session ) ?: [] );

		$link	= HtmlTag::create( 'a', $exception->message, array( 'href' => './admin/log/exception/view/'.$exception->exceptionId ) );
		$date	= date( 'Y.m.d', $exception->createdAt );
		$time	= date( 'H:i:s', $exception->createdAt );

		$buttons	= HtmlTag::create( 'div', array(
			HtmlTag::create( 'a', $iconView, array(
				'class'	=> 'btn not-btn-mini btn-small not-btn-info',
				'href'	=> './admin/log/exception/view/'.$exception->exceptionId
			) ),
			HtmlTag::create( 'a', $iconRemove, array(
				'class'	=> 'btn not-btn-mini btn-small btn-danger',
				'href'	=> './admin/log/exception/remove/'.$exception->exceptionId
			) ),
		), array( 'class' => 'btn-group' ) );

		$checkbox		= HtmlTag::create( 'input', NULL, [
			'type'		=> 'checkbox',
			'class'		=> 'checkbox-item',
			'data-id'	=> $exception->exceptionId,
		] );

		$requestPath	= '<small class="muted">'.htmlentities( $exceptionRequest->get( '__path' ), ENT_QUOTES, 'utf-8' ).'</small>';
		$method			= 'CLI';
		if( preg_match( '/Web/', $exceptionEnv['class'] ) )
			$method		= $exceptionRequest->getMethod()->get();
		$envClass		= preg_replace( '/^(CMF_Hydrogen_Environment_)/', '<small class="muted">\\1</small>', $exceptionEnv['class'] );
		$exceptionClass	= preg_replace( '/Exception$/', '', $exception->type );
		$typeClass		= '<small class="muted">'.$exceptionClass.'</small>';

		$icons	= [];

		if( $exceptionSession->get( 'auth_user_id' ) ){
			$user	= $modelUser->get( $exceptionSession->get( 'auth_user_id' ) );
			$icons['user']	= HtmlTag::create( 'span', $iconUser, [
				'title'	=> $user->username.' ('.$user->firstname.' '.$user->surname.')'
			] );
		}

		$list[]			= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $checkbox ),
			HtmlTag::create( 'td', $link.'<br/>'.$method.' '.$requestPath, array( 'class' => 'autocut' ) ),
//			HtmlTag::create( 'td', $envClass ),
//			HtmlTag::create( 'td', '<small class="muted">'.$exceptionClass.'</small>' ),

			HtmlTag::create( 'td', $icons ),
			HtmlTag::create( 'td', $typeClass.'<br/>'.$date.'&nbsp;<small class="muted">'.$time.'</small>' ),
			HtmlTag::create( 'td', $buttons ),
		) );
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

$pagination	= new \CeusMedia\Bootstrap\Nav\PageControl( './admin/log/exception', $page, ceil( $total / $limit ) );
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
