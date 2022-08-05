<?php

$iconView	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-eye-open not-icon-white' ) );
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-trash icon-white' ) );

$from		= 'admin/log/exception'.($page ? '/'.$page : '' );

$selectInstance	= '';
if( count( $instances ) > 1 ){
	$optInstance	= [];
	foreach( $instances as $instanceKey => $instanceData )
		$optInstance[$instanceKey]	= $instanceData->title;
	$optInstance	= UI_HTML_Elements::Options( $optInstance, $currentInstance );
	$selectInstance	= UI_HTML_Tag::create( 'select', $optInstance, array(
		'oninput'	=> 'document.location.href = "./admin/log/exception/setInstance/" + jQuery(this).val();',
		'class'		=> '',
		'style'		=> 'width: 100%',
	) );

}

$dropdown	= '';
$list		= '<div class="muted"><em><small>No exceptions logged.</small></em></div>';
if( $exceptions ){
	$list	= [];
	foreach( $exceptions as $nr => $exception ){
//print_m($exception);die;
		$exceptionEnv		= unserialize( $exception->env );
		$exceptionRequest	= unserialize( $exception->request );

		$link	= UI_HTML_Tag::create( 'a', $exception->message, array( 'href' => './admin/log/exception/view/'.$exception->exceptionId ) );
		$date	= date( 'Y.m.d', $exception->createdAt );
		$time	= date( 'H:i:s', $exception->createdAt );

		$buttons	= UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'a', $iconView, array(
				'class'	=> 'btn btn-mini not-btn-info',
				'href'	=> './admin/log/exception/view/'.$exception->exceptionId
			) ),
			UI_HTML_Tag::create( 'a', $iconRemove, array(
				'class'	=> 'btn btn-mini btn-danger',
				'href'	=> './admin/log/exception/remove/'.$exception->exceptionId
			) ),
		), array( 'class' => 'btn-group' ) );

		$checkbox		= UI_HTML_Tag::create( 'input', NULL, [
			'type'		=> 'checkbox',
			'class'		=> 'checkbox-item',
			'data-id'	=> $exception->exceptionId,
		] );

		$requestPath	= '<small class="muted">'.$exceptionRequest->get( '__path' ).'</small>';
		$envClass		= preg_replace( '/^(CMF_Hydrogen_Environment_)/', '<small class="muted">\\1</small>', $exceptionEnv['class'] );
		$exceptionClass	= preg_replace( '/Exception$/', '', $exception->type );
		$list[]			= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $checkbox ),
			UI_HTML_Tag::create( 'td', $link.'<br/>'.$requestPath, array( 'class' => 'autocut' ) ),
//			UI_HTML_Tag::create( 'td', $envClass ),
			UI_HTML_Tag::create( 'td', '<small class="muted">'.$exceptionClass.'</small>' ),
			UI_HTML_Tag::create( 'td', $date.'&nbsp;<small class="muted">'.$time.'</small>' ),
			UI_HTML_Tag::create( 'td', $buttons ),
		) );
	}


	$checkboxAll	= UI_HTML_Tag::create( 'input', NULL, [
		'type'	=> 'checkbox',
		'id'	=> 'admin-log-exception-list-all-items-toggle',
	] );

	$heads	= UI_HTML_Elements::TableHeads( [
		$checkboxAll,
		'',
		'',
		'',
	] );

	$colgroup	= UI_HTML_Elements::ColumnGroup( '20px', ''/*, '180px'*/, '180px', '150px', '100px' );
	$thead		= UI_HTML_Tag::create( 'thead', $heads );
	$tbody		= UI_HTML_Tag::create( 'tbody', $list );
	$table		= UI_HTML_Tag::create( 'table', [$colgroup, $thead, $tbody], [
		'class'	=> 'table table-striped table-condensed',
		'style'	=> 'table-layout: fixed'
	] );

	$dropdownMenu	= UI_HTML_Tag::create( 'ul', [
		UI_HTML_Tag::create( 'li',
			UI_HTML_Tag::create( 'a', '<i class="fa fa-trash"></i> entfernen', ['class' => '#', 'id' => 'action-button-remove'] )
		),
	], ['class' => 'dropdown-menu not-pull-right'] );

	$dropdownToggle	= UI_HTML_Tag::create( 'button', 'Aktion <span class="caret"></span>', [
		'type'		=> 'button',
		'class'		=> 'btn dropdown-toggle',
	], ['toggle' => 'dropdown'] );
	$dropdown		= UI_HTML_Tag::create( 'div', [$dropdownToggle, $dropdownMenu], ['class' => 'btn-group dropup'] );
}

$pagination	= new \CeusMedia\Bootstrap\Nav\PageControl( './admin/log/exception', $page, ceil( $total / $limit ) );
$pagination	= $pagination->render();

$panelList	= '
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

return $panelList;

return '
<div class="row-fluid">
	<div class="span3">
		<div class="content-panel">
			<h3>Filter</h3>
			<div class="content-panel-inner">
			</div>
		</div>
	</div>
	<div class="span9">
		'.$panelList.'
	</div>
</div>';
