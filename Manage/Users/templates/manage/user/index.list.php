<?php

/*  --  PAGINATION  --  */
$pagination	= new \CeusMedia\Bootstrap\PageControl( './manage/user', $page, ceil( $total / $limit ) );

$heads	= UI_HTML_Elements::TableHeads( $words['indexListHeads'] );
$number	= 0;

if( $total ){
	$rows	= array();
	$phraser	= new View_Helper_TimePhraser( $env );

	foreach( $users as $nr => $user ){
		$classes	= array( 'user' );
		$classes[]	= "role role".$user->roleId;
		$classes[]	= "status".$user->status;

		if( !( $nr++ % 10 ) )
			$rows[]	= $heads;
		$classes[]	= ( ++$number % 2 ) ? "even" : "odd";
		$classes	= implode( ' ', $classes );
		$line		= '
		<tr>
			<td class="user-role role'.$user->roleId.'">%1$s</td>
			<td><span class="role role'.$user->roleId.'">%2$s</span></td>
			<td><span class="user-status status'.$user->status.'">%3$s</span></td>
			<td>%4$s</td>
			<td>%5$s</td>
<!--			<td>%6$s</td>-->
		</tr>';
		$label	= $user->username;
		$url	= './manage/user/edit/'.$user->userId;
		$alt	= sprintf( $words['indexList']['alt-user'], $user->username );
		$attr	= array( 'href' => $url, 'class' => $classes, 'alt' => $alt, 'title' => $alt );
		$link	= UI_HTML_Tag::create( 'a', $label, $attr );
		if( $user->firstname && $user->surname )
			$link	.= '<br/><small class="muted">'.$user->firstname.' '.$user->surname.'</small>';
		$line	= sprintf(
			$line,
			$link,
			$roles[$user->roleId]->title,
			$words['status'][$user->status],
			$phraser->convert( $user->createdAt, TRUE ),
			$phraser->convert( $user->loggedAt, TRUE ),
			$phraser->convert( $user->activeAt, TRUE )
		);
		$rows[]	= $line;
	}
	$rows	= join( $rows );
	$list	= '
<table id="users" class="table not-table-condensed table-striped">
	<colgroup>
		<col width="32%"/>
		<col width="17%"/>
		<col width="15%"/>
		<col width="12%"/>
		<col width="12%"/>
		<col width="12%"/>
<!--				<col width="12%"/>-->
	</colgroup>
	'.$rows.'
</table>';
}
else
	$list	= '<div class="muted"><em>'.$words['indexList']['noEntries'].'</em></div><br/>';

$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
if( $env->getModules()->get( 'UI_Font_FontAwesome' ) )
	$iconAdd		= UI_HTML_Tag::create( 'b', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$buttonAdd	= UI_HTML_Elements::LinkButton( './manage/user/add', $iconAdd.'&nbsp;'.$words['indexList']['buttonAdd'], 'btn btn-small btn-success' );

return '
<div class="content-panel">
	<h3>'.$words['indexList']['heading'].' <small class="muted">('.$total.'/'.$all.')</small></h3>
	<div class="content-panel-inner">
		'.$list.'
		<div class="buttonbar">
			'.$pagination.'
			'.$buttonAdd.'
		</div>
	</div>
</div>';
?>
