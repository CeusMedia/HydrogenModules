<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$wf		= (object) $words['index'];

$heads	= array(
	$wf->headTitle,
	$wf->headUsers,
	$wf->headAccess,
	$wf->headRegister
);
$heads	= HtmlElements::TableHeads( $heads );
$number	= 0;

$rows	= [];
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
	if( $hasRightToEdit ){
		$url	= './admin/role/edit/'.$role->roleId;
		$alt	= "";#sprintf( $wf->alt-user'], $user->username );
		$attr	= array( 'href' => $url, 'alt' => $alt, 'title' => $alt );
		$label	= HtmlTag::create( 'a', $label, $attr );
	}
	$label	= HtmlTag::create( 'span', $label, array( 'class' => $classes ) );
	$line	= sprintf(
		$line,
		$label,
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
		<legend class="roles">'.$wf->legend.' <small>('.count( $roles ).')</small></legend>
		<table id="roles">
			<colgroup>
				<col width="45%"/>
				<col width="10%"/>
				<col width="25%"/>
				<col width="20%"/>
			</colgroup>
			'.$rows.'
		</table>
		<div class="buttonbar">
			'.HtmlElements::LinkButton( './admin/role/add', $wf->buttonAdd, 'button add', NULL, !$hasRightToAdd ).'
		</div>
	</fieldset>
</div>
';


/*
$list	= [];
foreach( $roles as $role )
{
	$label	= $role->title;
	if( trim( $role->description ) )
		$label	= HtmlElements::Acronym( $role->title, htmlspecialchars( $role->description ) );
	$link	= HtmlElements::Link( './admin/role/edit/'.$role->roleId, $label );
	$list[]	= HtmlElements::ListItem( $link, 0, array( 'class' => 'role' ) );
}
$list	= HtmlElements::unorderedList( $list, 0, array( 'class' => 'list' ) );
return '
<fieldset style="width: 300px">
	<legend>'.$wf->legend.'</legend>
	'.$list.'
	<div class="buttonbar">
		'.HtmlElements::LinkButton( './admin/role/add', $wf->buttonNew, 'button add' ).'
	</div>
</fieldset>
';*/
?>
