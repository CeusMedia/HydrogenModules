<?php
$helperGravatar	= new View_Helper_Gravatar( $env );


$panelFilter	= '
<div class="content-panel">
	<h3>Filter</h3>
	<div class="content-panel-inner">
		<form action="./member/filter" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_query">Benutzername</label>
					<input type="text" name="query" id="input_query" class="span12" value="'.htmlentities( $filterQuery, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="filter" class="btn btn-primary"><i class="icon-zoom-in icon-white"></i> filter</button>
				<a href="./member/filter/reset" class="btn btn-small btn-inverse"><i class="icon-zoom-out icon-white"></i> reset</a>
			</div>
		</form>
	</div>
</div>';

/*
$table	= '<div><em><small class="muted">Keine gefunden.</small></em></div>';
if( $users ){
	$list	= array();
	$helperMember	= new View_Helper_Member( $env );
	$helperMember->setLinkUrl( './member/view/%d' );
	foreach( $users as $user ){
		$helperMember->setUser( $user );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $helperMember->render() ),
			UI_HTML_Tag::create( 'td', '<small>'.date( 'd.m.Y', $user->relation->createdAt ).'</small>' ),
			UI_HTML_Tag::create( 'td', '<small>'.date( 'd.m.Y', $user->relation->modifiedAt ).'</small>' ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( "", "120", "120" );
	$heads		= UI_HTML_Elements::TableHeads( array( 'Benutzer', 'angefragt', 'bestätigt' ) );
	$thead		= UI_HTML_Tag::create( 'thead', $heads );
	$tbody		= UI_HTML_Tag::create( 'tbody', $list );
	$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table' ) );
}
*/

$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
$helperPages	= new \CeusMedia\Bootstrap\PageControl( './member', $page, $pages );
$pagination		= $helperPages->render();
$buttonAdd		=UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;neuen Kontakt herstellen', array(
	'href'		=> './member/search',
	'class'		=> 'btn btn-success',
) );

$table	= '<div><em><small class="muted">Keine gefunden.</small></em></div>';
if( $users ){
	$list	= array();
	$helperMember	= new View_Helper_Member( $env );
	$helperMember->setLinkUrl( './member/view/%d' );
	$helperMember->setMode( 'thumbnail' );
	foreach( $users as $user ){
		$helperMember->setUser( $user );
		$list[]	= UI_HTML_Tag::create( 'li', $helperMember->render(), array( 'class' => 'span4' ) );
	}
	$table	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'thumbnails' ) );
}

$panelList	= '
<div class="content-panel">
	<h3>Verbindungen</h3>
	<div class="content-panel-inner">
		'.$table.'
		<div class="buttonbar">
			<div class="btn-toolbar">
				'.$pagination.'
				'.$buttonAdd.'
			</div>
		</div>
	</div>
</div>';

$helperMember	= new View_Helper_Member( $env );
$helperMember->setLinkUrl( "member/view/%d" );

$list	= '<div><em><small class="muted">Keine.</small></em></div><br/>';
if( $incoming || $outgoing ){
	$list	= array();
	foreach( $incoming as $relation ){
		$helperMember->setUser( $relation->user );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $helperMember->render() ),
			UI_HTML_Tag::create( 'td', '<small>'.date( 'd.m.Y', $relation->createdAt ).'</small>' ),
		) );
	}
	foreach( $outgoing as $relation ){
		$helperMember->setUser( $relation->user );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $helperMember->render() ),
			UI_HTML_Tag::create( 'td', '<small>'.date( 'd.m.Y', $relation->createdAt ).'</small>' ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( "", "80" );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'Mitglied', 'Datum' ) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list, array( '' ) );
	$list	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );
}

$panelPending	= '
<div class="content-panel">
	<h3>Offene Anfragen</h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/member/' ) );

$tabs	= View_Member::renderTabs( $env, '' );

return $tabs.$textTop.'
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span5">
		'.$panelList.'
	</div>
	<div class="span4">
		'.$panelPending.'
	</div>
</div>'.$textBottom.'
<style>
</style>';
