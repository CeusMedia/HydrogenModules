<?php

$wf		= (object) $words['index'];

$heads	= array(
	$wf->headTitle,
	$wf->headUsers,
	$wf->headAccess,
	$wf->headRegister
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
	if( $hasRightToEdit ){
		$url	= './manage/role/edit/'.$role->roleId;
		$alt	= "";#sprintf( $wf->alt-user'], $user->username );
		$attr	= array( 'href' => $url, 'alt' => $alt, 'title' => $alt );
		$label	= UI_HTML_Tag::create( 'a', $label, $attr );
	}
	$label	= UI_HTML_Tag::create( 'span', $label, array( 'class' => $classes ) );
	if( strlen( $role->description ) )
		$label	.= '<br/><blockquote>'.nl2br( $role->description ).'</blockquote>';

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

$panelFilter	= '';

extract( $view->populateTexts( array( 'index.top', 'index.bottom' ), 'html/manage/role/' ) );

return $textIndexTop.'
<style>
table tr td blockquote {
	margin: 0 0 0.1em 0.4em;
	}
</style>
<div id="site-role-index">
	<div class="row-fluid">
<!--		<div class="span3">
			'.$panelFilter.'
		</div>-->
		<div class="span12">
			<div class="content-panel">
				<h3>'.$wf->heading.' <small class="muted">('.count( $roles ).')</small></h3>
				<div class="content-panel-inner">
					<table id="roles" class="table not-table-condensed table-striped">
						<colgroup>
							<col width="45%"/>
							<col width="10%"/>
							<col width="25%"/>
							<col width="20%"/>
						</colgroup>
						'.$rows.'
					</table>
					<div class="buttonbar">
						<div class="btn-toolbar">
							'.UI_HTML_Elements::LinkButton( './manage/role/add', '<i class="icon-plus icon-white"></i> '.$wf->buttonAdd, 'btn btn-small not-btn-info btn-success', NULL, !$hasRightToAdd ).'
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>'.$textIndexBottom;
?>
