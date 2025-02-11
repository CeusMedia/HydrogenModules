<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/*
$list		= '<div><em><small class="muted">Noch keine vorhanden.</small></em></div>';
if( $users ){
	$list	= [];
	foreach( $users as $user ){
		$link	= HtmlTag::create( 'a', $user->username, [
			'href'	=> './member/view/'.$user->userId,
		] );
		$relation	= $user->relation ? 'yes' : 'no';
		$list[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $link ),
			HtmlTag::create( 'td', $relation ),
		] );
	}
	$tbody	= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', $tbody, ['class' => 'table table-striped'] );
}
*/

$panelSearch	= '
<div class="content-panel">
	<h3>Mitglied finden</h3>
	<div class="content-panel-inner">
		<form action="./member/search" method="post">
			<label for="input_username">Benutzername</label>
			<input type="text" name="username" id="input_username" class="span12" value="'.htmlentities( $username, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
			<div class="buttonbar">
				<button type="submit" name="view" class="btn"><i class="icon-search"></i> find</button>
			</div>
		</form>
	</div>
</div>';


$panelList	= '';
if( $username ){
	$list	= '<div><em><small class="muted">Keine gefunden.</small></em></div><br/>';
	if( $users ){
		$list	= [];
		$helperMember	= new View_Helper_Member( $env );
		$helperMember->setLinkUrl( './member/view/%d' );
		$helperMember->setMode( 'thumbnail' );
		foreach( $users as $user ){
			$helperMember->setUser( $user );
			$list[]	= HtmlTag::create( 'li', $helperMember->render(), ['class' => 'span4'] );
		}
		$list	= HtmlTag::create( 'ul', $list, ['class' => 'thumbnails'] );
	}

	$panelList	= '
	<div class="content-panel">
		<h3>Gefunde Mitglieder</h3>
		<div class="content-panel-inner">
			'.$list.'
		</div>
	</div>';
}

$panelAdvice	= "";
if( isset( $advices ) && $advices ){
//	$list	= '<div><em><small class="muted">Keine.</small></em></div><br/>';
	$list	= [];
	foreach( $advices as $advice ){

	}
	$panelAdvice	= '
	<div class="content-panel">
		<h3>Vorschläge</h3>
		<div class="content-panel-inner">
			'.$list.'
		</div>
	</div>';
}

extract( $view->populateTexts( ['top', 'bottom'], 'html/member/' ) );

$tabs	= View_Member::renderTabs( $env, 'search' );

return $tabs.$textTop.'
<div class="row-fluid">
	<div class="span3">
		'.$panelSearch.'
	</div>
	<div class="span5">
		'.$panelList.'
	</div>
	<div class="span4">
		'.$panelAdvice.'
	</div>
</div>'.$textBottom;
