<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/*  --  PAGINATION  --  */
$pagination	= "";
if( $limit && $total > $limit )
{
	$options	= array(
		'uri'	=> './admin/user/index/'.$limit.'/',
		'keyRequest'	=> '',
		'keyParam'		=> '',
		'keyOffset'		=> '',
		'keyAssign'		=> '',
	);
	$pagination	= new UI_HTML_Pagination( $options );
	$pagination	= $pagination->build( $total, $limit, $offset );
}

$heads	= HtmlElements::TableHeads( $words['indexListHeads'] );
$number	= 0;

if( count( $total ) ){

	$rows	= [];
	foreach( $users as $nr => $user ){

		$classes	= array( 'user' );
		$classes[]	= "role role".$user->roleId;
		$classes[]	= "status".$user->status;

		if( !( $nr++ % 10 ) )
			$rows[]	= $heads;
		$classes[]	= ( ++$number % 2 ) ? "even" : "odd";
		$classes	= implode( ' ', $classes );
		$createdAt	= new CMF_Hydrogen_View_Helper_Timestamp( $user->createdAt );
		$loggedAt	= new CMF_Hydrogen_View_Helper_Timestamp( $user->loggedAt );
		$activeAt	= new CMF_Hydrogen_View_Helper_Timestamp( $user->activeAt );
		$line		= '
		<tr>
			<td class="user-role role'.$user->roleId.'">%1$s</td>
			<td><span class="user-status status'.$user->status.'">%2$s</span></td>
			<td>%3$s</td>
			<td>%4$s</td>
			<td>%5$s</td>
		</tr>';
		$label	= $user->username;
		$url	= './admin/user/edit/'.$user->userId;
		$alt	= sprintf( $words['indexList']['alt-user'], $user->username );
		$attr	= array( 'href' => $url, 'class' => $classes, 'alt' => $alt, 'title' => $alt );
		$link	= HtmlTag::create( 'a', $label, $attr );
		$line	= sprintf(
			$line,
			$link,
			$words['status'][$user->status],
			$createdAt->toPhrase( $env, TRUE ),
			$loggedAt->toPhrase( $env, TRUE ),
			$activeAt->toPhrase( $env, TRUE )
		);
		$rows[]	= $line;
	}
	$rows	= join( $rows );
}
else
	$rows	= '<tr><td colspan="5"><em>Nichts gefunden.</em></td></tr>';

return '
<fieldset>
	<legend class="users">'.$words['indexList']['legend'].' <small>('.$total.'/'.$all.')</small></legend>
	<table id="users">
		<colgroup>
			<col width="25%"/>
			<col width="15%"/>
			<col width="15%"/>
			<col width="15%"/>
			<col width="15%"/>
			<col width="15%"/>
		</colgroup>
		'.$rows.'
	</table>
	'.$pagination.'<br/>
	<div class="buttonbar">
		'.HtmlElements::LinkButton( './admin/user/add', $words['indexList']['buttonAdd'], 'button add' ).'
	</div>
</fieldset>
';
?>
