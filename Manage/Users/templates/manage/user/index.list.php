<?php

/*  --  PAGINATION  --  */
$pagination	= "";
if( $limit && $total > $limit )
{
	if( 0 ){
	$options	= array(
		'uri'	=> './manage/user/index/'.$limit.'/',
		'keyRequest'	=> '',
		'keyParam'		=> '',
		'keyOffset'		=> '',
		'keyAssign'		=> '',
	);
	$pagination	= new UI_HTML_Pagination( $options );
	$pagination	= $pagination->build( $total, $limit, $offset );
	}else{
		$uri		= './manage/user/index/'.$limit;
		$pagination	= new View_Helper_Pagination( $options );
		$pagination	= $pagination->render( $uri, $total, $limit, $page );
	}
}

$heads	= UI_HTML_Elements::TableHeads( $words['indexListHeads'] );
$number	= 0;

if( count( $total ) ){

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
}
else
	$rows	= '<tr><td colspan="5"><em>Nichts gefunden.</em></td></tr>';

return '
<div class="content-panel">
	<h4>'.$words['indexList']['legend'].' <small class="muted">('.$total.'/'.$all.')</small></h4>
	<div class="content-panel-inner">
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
		</table>
<!--		'.$pagination.'<br/>-->
		<div class="row-fluid buttonbar">
			<div class="pull-right">
				'.$pagination.'
			</div>
			<div class="pull-left">
				'.UI_HTML_Elements::LinkButton( './manage/user/add', '<i class="icon-plus icon-white"></i> '.$words['indexList']['buttonAdd'], 'btn btn-primary' ).'
			</div>
		</div>
	</div>
</div>
';
?>
