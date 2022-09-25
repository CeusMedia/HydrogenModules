<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconAdd	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plug' ) );
$iconRemove	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

$w			= (object) $words['index'];

$table		= HtmlTag::create( 'div', $w->empty, array( 'class' => 'alert alert-info' ) );
if( $providers ){
	$rows		= [];
	foreach( $providers as $provider ){
		$buttonAdd		= HtmlTag::create( 'a', $iconAdd.'&nbsp;'.$w->buttonAdd, array(
			'href'	=> './manage/my/user/oauth2/add/'.$provider->oauthProviderId,
			'class'	=> 'btn btn-small btn-success',
		) );
		$buttonRemove	= HtmlTag::create( 'a', $iconRemove.'&nbsp;'.$w->buttonRemove, array(
			'href'	=> './manage/my/user/oauth2/remove/'.$provider->oauthProviderId,
			'class'	=> 'btn btn-small btn-inverse',
		) );
		$connected	= array_key_exists( $provider->oauthProviderId, $relations );
		$status		= $connected ? $iconAdd.'&nbsp;'.$words['statuses'][1] : $iconRemove.'&nbsp;'.$words['statuses'][0];
		$rows[]		= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $provider->title ),
			HtmlTag::create( 'td', $status ),
			HtmlTag::create( 'td', $connected ? $buttonRemove : $buttonAdd, array( 'style' => 'text-align: right' ) ),
		), array( 'class' => $connected ? 'success' : NULL ) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( array( '', '35%', '120px' ) );
	$thead		= HtmlTag::create( 'thead', UI_HTML_Elements::TableHeads( array(
		$w->headProvider,
		$w->headStatus,
		$w->headAction,
	) ) );
	$tbody		= HtmlTag::create( 'tbody', $rows );
	$table		= HtmlTag::create( 'table', array( $colgroup, $thead, $tbody ), array( 'class' => 'table table-striped table-fixed' ) );
}

$tabs		= View_Manage_My_User::renderTabs( $env, 'oauth2' );
extract( $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/manage/my/user/oauth2/' ) );

return $tabs.$textTop.HtmlTag::create( 'div', array(
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'h4', $w->heading ),
			HtmlTag::create( 'div', array(
				$table,
			), array( 'class' => 'content-panel-inner' ) ),
		), array( 'class' => 'content-panel' ) ),
	), array( 'class' => 'span8' ) ),
	HtmlTag::create( 'div', $textInfo, array( 'class' => 'span4' ) ),
), array( 'class' => 'row-fluid' ) ).$textBottom;
