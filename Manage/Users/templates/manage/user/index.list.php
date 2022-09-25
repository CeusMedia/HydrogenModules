<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w		= (object) $words['indexList'];

/*  --  PAGINATION  --  */
$pagination	= new \CeusMedia\Bootstrap\PageControl( './manage/user', $page, ceil( $total / $limit ) );

if( $total ){
	$rows		= [];
	$phraser	= new View_Helper_TimePhraser( $env );

	foreach( $users as $nr => $user ){
		if( class_exists( 'View_Helper_Member' ) ){
			$helper	= new View_Helper_Member( $env );
			$helper->setUser( $user );
	//		$helper->setMode( 'bar' );
			if( $hasRightToEdit )
				$helper->setLinkUrl( './manage/user/edit/'.$user->userId );
			$labelUser	= $helper->render();
		}
		else{
			$labelUser	= $user->username;
			if( $hasRightToEdit ){
				$url		= './manage/user/edit/'.$user->userId;
				$labelUser	= HtmlTag::create( 'a', $labelUser, array( 'href' => $url ) );
			}
			if( $user->firstname && $user->surname )
				$labelUser	.= '<br/><small class="muted">'.$user->firstname.' '.$user->surname.'</small>';
		}
		$labelRole		= HtmlTag::create( 'span', $roles[$user->roleId]->title, array( 'class' => 'role role'.$user->roleId ) );
		$labelStatus	= HtmlTag::create( 'span', $words['status'][$user->status], array( 'class' => 'user-status status'.$user->status ) );
		$rows[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $labelUser, array( 'class' => 'cell-user' ) ),
			HtmlTag::create( 'td', $labelRole, array( 'class' => 'cell-role' ) ),
			HtmlTag::create( 'td', $labelStatus, array( 'class' => 'cell-status' ) ),
			HtmlTag::create( 'td', $phraser->convert( $user->createdAt, TRUE ), array( 'class' => 'cell-created' ) ),
			HtmlTag::create( 'td', $phraser->convert( $user->loggedAt, TRUE ), array( 'class' => 'cell-logged' ) ),
		), array(
			'data-user-role'	=> $user->roleId,
			'data-user-status'	=> $user->status,
		) );
	}
	$heads		= UI_HTML_Elements::TableHeads( $words['indexListHeads'] );
	$list		= HtmlTag::create( 'table', array(
		UI_HTML_Elements::ColumnGroup( "32%", "17%", "15%", "12%", "12%" ),
		HtmlTag::create( 'thead', $heads ),
		HtmlTag::create( 'tbody', $rows ),
	), array( 'class' => 'table not-table-condensed table-striped', 'id' => "users" ) );
}
else
	$list	= '<div class="muted"><em>'.$w->noEntries.'</em></div><br/>';


$buttonAdd	= '';
if( $hasRightToAdd ){
	$iconAdd	= HtmlTag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
	if( $env->getModules()->has( 'UI_Font_FontAwesome' ) )
		$iconAdd	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
	$buttonAdd	= HtmlTag::create( 'a', $iconAdd.'&nbsp;'.$w->buttonAdd, array(
		'href'	=> './manage/user/add',
		'class'	=> 'btn btn-success'
	) );
}

return '
<div class="content-panel">
	<h3>'.$w->heading.' <small class="muted">('.$total.'/'.$all.')</small></h3>
	<div class="content-panel-inner">
		'.$list.'
		<div class="buttonbar">
			'.$pagination.'
			'.$buttonAdd.'
		</div>
	</div>
</div>';
?>
