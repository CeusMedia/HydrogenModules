<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['index'];

$iconView	= HtmlTag::create( 'i', '', ['class' => 'icon-eye-open not-icon-white'] );
$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'icon-trash icon-white'] );
$iconWeb		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-earth' ) );
$iconConsole	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-terminal' ) );

if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconView	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] );
	$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
	$iconWeb		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-globe' ) );
	$iconConsole	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-terminal' ) );
	$version		= $env->getModules()->get( 'UI_Font_FontAwesome' )->config['version']->value;
	if( version_compare( $version, '5.0', '>' ) ){
		$iconWeb		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fas fa-fw fa-globe-europe' ) );
		$iconConsole	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'far fa-square-terminal' ) );
	}
}

$list	= '<div class="muted"><em><small>No exceptions logged.</small></em></div>';
if( $exceptions ){
	$list	= [];
	foreach( $exceptions as $item ){

		$_env	= unserialize( $item->env );
		$_isWeb	= in_array( $_env['class'], ["CMF_Hydrogen_Environment_Web", "CeusMedia\\HydrogenFramework\\Environment\\Web"] );
		$_path	= '';
		if( $_isWeb ){
			$_req	= unserialize( $item->request );
			$_path	= $_req->get( '__path' );
		}
		$pathLabel	= $_path ? $iconWeb.'&nbsp;<small>'.$_path.'</small>' : '';

		$exLabel	= preg_replace( '/Exception$/', '', $item->type );

		$link	= HtmlTag::create( 'a', $item->message, [
			'href'	=> './server/log/exception/view/'.$item->exceptionId,
			'class'	=> 'autocut',
		] );
		$date	= date( 'Y.m.d', $item->createdAt );
		$time	= date( 'H:i:s', $item->createdAt );
		$buttons	= HtmlTag::create( 'div', array(
			HtmlTag::create( 'a', $iconView, [
				'class'	=> 'btn btn-mini not-btn-info',
				'href'	=> './server/log/exception/view/'.$item->exceptionId,
				'title'	=> $w->buttonView,
			] ),
			HtmlTag::create( 'a', $iconRemove, [
				'class'	=> 'btn btn-mini btn-danger',
				'href'	=> './server/log/exception/remove/'.$item->exceptionId,
				'title'	=> $w->buttonRemove,
			] ),
		), ['class' => 'btn-group'] );

		$list[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $link, ['class' => 'autocut'] ),
			HtmlTag::create( 'td', $exLabel, ['class' => 'autocut'] ),
			HtmlTag::create( 'td', $pathLabel, ['class' => 'autocut'] ),
			HtmlTag::create( 'td', $date.'&nbsp;<small class="muted">'.$time.'</small>' ),
			HtmlTag::create( 'td', $buttons ),
		] );
	}
	$colgroup	= HtmlElements::ColumnGroup( '', '160px', '', '150px', '100px' );
	$tbody	= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', $colgroup.$tbody, ['class' => 'table table-striped table-condensed', 'style' => 'table-layout: fixed'] );
}

$pagination	= new \CeusMedia\Bootstrap\PageControl( './server/log/exception', $page, ceil( $total / $limit ) );
$pagination	= $pagination->render();

$panelList	= '
		<div class="content-panel">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				'.$list.'
				'.$pagination.'
			</div>
		</div>
';
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
</div>
';
