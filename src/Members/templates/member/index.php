<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$helperGravatar	= new View_Helper_Gravatar( $env );

$panelFilter	= '
<div class="content-panel content-panel-filter">
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

$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'icon-plus icon-white'] );
$helperPages	= new \CeusMedia\Bootstrap\PageControl( './member', $page, $pages );
$pagination		= $helperPages->render();
$buttonAdd		=HtmlTag::create( 'a', $iconAdd.'&nbsp;neuen Kontakt herstellen', [
	'href'		=> './member/search',
	'class'		=> 'btn btn-success',
] );

$table	= '<div><em><small class="muted">Keine gefunden.</small></em></div>';
if( $users ){
	$list	= [];
	$helperMember	= new View_Helper_Member( $env );
	$helperMember->setLinkUrl( './member/view/%d' );
	$helperMember->setMode( 'thumbnail' );
	foreach( $users as $user ){
		$helperMember->setUser( $user );
		$list[]	= HtmlTag::create( 'li', $helperMember->render(), ['class' => 'span4'] );
	}
	$table	= HtmlTag::create( 'ul', $list, ['class' => 'thumbnails'] );
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
	$list	= [];
	foreach( $incoming as $relation ){
		$helperMember->setUser( $relation->user );
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $helperMember->render() ),
			HtmlTag::create( 'td', '<small>'.date( 'd.m.Y', $relation->createdAt ).'</small>' ),
		) );
	}
	foreach( $outgoing as $relation ){
		$helperMember->setUser( $relation->user );
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $helperMember->render() ),
			HtmlTag::create( 'td', '<small>'.date( 'd.m.Y', $relation->createdAt ).'</small>' ),
		) );
	}
	$colgroup	= HtmlElements::ColumnGroup( "", "80" );
	$thead	= HtmlTag::create( 'thead', HtmlElements::TableHeads( ['Mitglied', 'Datum'] ) );
	$tbody	= HtmlTag::create( 'tbody', $list, [''] );
	$list	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-striped'] );
}

$panelPending	= '
<div class="content-panel content-panel-info">
	<h3>Offene Anfragen</h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';

extract( $view->populateTexts( ['top', 'bottom'], 'html/member/' ) );

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
