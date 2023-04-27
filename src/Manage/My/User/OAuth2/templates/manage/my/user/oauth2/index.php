<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plug'] );
$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );

$w			= (object) $words['index'];

$table		= HtmlTag::create( 'div', $w->empty, ['class' => 'alert alert-info'] );
if( $providers ){
	$rows		= [];
	foreach( $providers as $provider ){
		$buttonAdd		= HtmlTag::create( 'a', $iconAdd.'&nbsp;'.$w->buttonAdd, [
			'href'	=> './manage/my/user/oauth2/add/'.$provider->oauthProviderId,
			'class'	=> 'btn btn-small btn-success',
		] );
		$buttonRemove	= HtmlTag::create( 'a', $iconRemove.'&nbsp;'.$w->buttonRemove, [
			'href'	=> './manage/my/user/oauth2/remove/'.$provider->oauthProviderId,
			'class'	=> 'btn btn-small btn-inverse',
		] );
		$connected	= array_key_exists( $provider->oauthProviderId, $relations );
		$status		= $connected ? $iconAdd.'&nbsp;'.$words['statuses'][1] : $iconRemove.'&nbsp;'.$words['statuses'][0];
		$rows[]		= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $provider->title ),
			HtmlTag::create( 'td', $status ),
			HtmlTag::create( 'td', $connected ? $buttonRemove : $buttonAdd, ['style' => 'text-align: right'] ),
		), ['class' => $connected ? 'success' : NULL] );
	}
	$colgroup	= HtmlElements::ColumnGroup( ['', '35%', '120px'] );
	$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( [
		$w->headProvider,
		$w->headStatus,
		$w->headAction,
	] ) );
	$tbody		= HtmlTag::create( 'tbody', $rows );
	$table		= HtmlTag::create( 'table', [$colgroup, $thead, $tbody], ['class' => 'table table-striped table-fixed'] );
}

$tabs		= View_Manage_My_User::renderTabs( $env, 'oauth2' );
extract( $view->populateTexts( ['top', 'info', 'bottom'], 'html/manage/my/user/oauth2/' ) );

return $tabs.$textTop.HtmlTag::create( 'div', array(
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'h4', $w->heading ),
			HtmlTag::create( 'div', [
				$table,
			], ['class' => 'content-panel-inner'] ),
		), ['class' => 'content-panel'] ),
	), ['class' => 'span8'] ),
	HtmlTag::create( 'div', $textInfo, ['class' => 'span4'] ),
), ['class' => 'row-fluid'] ).$textBottom;
