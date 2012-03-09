<?php
$heads	= array(
	$words['index']['headTitle'],
	$words['index']['headUsers'],
	$words['index']['headAccess'],
	$words['index']['headRegister']
);
$heads	= UI_HTML_Elements::TableHeads( $heads );
$number	= 0;

$rows	= array();
foreach( $roles as $nr => $role )
{
	$classes	= array( 'role' );
	$classes[]	= "role".$role->roleId;

	if( !( $nr++ % 10 ) )
		$rows[]	= $heads;
	$classes[]	= ( ++$number % 2 ) ? "even" : "odd";
	$classes	= implode( ' ', $classes );
	$line		= '
	<tr class="'.$classes.'">
		<td>%s</td>
		<td class="role count">%s</td>
		<td><span class="role-access access'.$role->access.'">%s</span></td>
		<td><span class="role-register register'.$role->register.'">%s</span></td>
	</tr>';
	$label	= $role->title;
	$url	= './admin/role/edit/'.$role->roleId;
	$alt	= "";#sprintf( $words['index']['alt-user'], $user->username );
	$attr	= array( 'href' => $url, 'class' => $classes, 'alt' => $alt, 'title' => $alt );
	$link	= UI_HTML_Tag::create( 'a', $label, $attr );
	$line	= sprintf(
		$line,
		$link,
		count( $role->users ),
		$words['type-access'][$role->access],
		$words['type-register'][$role->register]
	);
	$rows[]	= $line;
}
$rows	= join( $rows );


return '
<!--<h2>Rollen</h2>-->
<div id="site-role-index">
	<fieldset>
		<legend class="roles">'.$words['index']['legend'].' <small>('.count( $roles ).')</small></legend>
		<table id="roles">
			<colgroup>
				<col width="50%"/>
				<col width="10%"/>
				<col width="25%"/>
				<col width="15%"/>
			</colgroup>
			'.$rows.'
		</table>
		<div class="buttonbar">
			'.UI_HTML_Elements::LinkButton( './admin/role/add', $words['index']['buttonAdd'], 'button add' ).'
		</div>
	</fieldset>
</div>
';


/*
$list	= array();
foreach( $roles as $role )
{
	$label	= $role->title;
	if( trim( $role->description ) )
		$label	= UI_HTML_Elements::Acronym( $role->title, htmlspecialchars( $role->description ) );
	$link	= UI_HTML_Elements::Link( './admin/role/edit/'.$role->roleId, $label );
	$list[]	= UI_HTML_Elements::ListItem( $link, 0, array( 'class' => 'role' ) );
}
$list	= UI_HTML_Elements::unorderedList( $list, 0, array( 'class' => 'list' ) );
return '
<fieldset style="width: 300px">
	<legend>'.$words['index']['legend'].'</legend>
	'.$list.'
	<div class="buttonbar">
		'.UI_HTML_Elements::LinkButton( './admin/role/add', $words['index']['buttonNew'], 'button add' ).'
	</div>
</fieldset>
';*/
?>
