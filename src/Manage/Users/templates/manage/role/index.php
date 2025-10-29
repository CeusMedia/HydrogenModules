<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var View_Manage_Role $view */
/** @var array<string,array<string|int,string|int>> $words */
/** @var array<object> $roles */
/** @var bool $hasRightToAdd */
/** @var bool $hasRightToEdit */

$wf		= (object) $words['index'];

$heads	= [
	$wf->headTitle,
	$wf->headUsers,
	$wf->headAccess,
	$wf->headRegister,
	$wf->headRights
];

$iconType	= [
	0	=> HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-ban'] ),
	64	=> HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-lock'] ),
	128	=> HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-globe'] ),
];

$helperIndicator	= new View_Helper_Manage_Role_RightsIndicator( $env );
$helperIndicator->setRoles( $roles );

$rows	= [];
/** @var Entity_Role $role */
foreach( $roles as $role ){
	$helperIndicator->setRoleId( $role->roleId );
	$labelRole	= $role->title;
	if( $hasRightToEdit ){
		$labelRole	= HtmlTag::create( 'a', $labelRole, ['href' => './manage/role/edit/'.$role->roleId] );
	}
	$labelRole		= HtmlTag::create( 'span', $labelRole, ['class' => 'role-'.$role->roleId] );
	if( strlen( $role->description ) )
		$labelRole	.= '<br/><blockquote>'.nl2br( $role->description ).'</blockquote>';
	$labelCount		= HtmlTag::create( 'span', count( $role->users ), ['class' => 'role count'] );
	$labelAccess	= HtmlTag::create( 'span', $iconType[$role->access].'&nbsp;'.$words['type-access'][$role->access], ['class' => 'role-access access'.$role->access] );
	if( 0 !== $role->rights )
		$labelAccess	.= '&nbsp;<span class="badge">'.$role->nrRights.'</span>';
	$labelRegister	= HtmlTag::create( 'span', $words['type-register'][$role->register], ['class' => 'role-register register'.$role->register] );

	$rows[]	= HtmlTag::create( 'tr', [
		HtmlTag::create( 'td', $labelRole ),
		HtmlTag::create( 'td', $labelCount ),
		HtmlTag::create( 'td', $labelAccess ),
		HtmlTag::create( 'td', $labelRegister ),
		HtmlTag::create( 'td', $helperIndicator->render() ),
	] );
}
$heads	= HtmlElements::TableHeads( $heads );
$table	= HtmlTag::create( 'table', [
	HtmlElements::ColumnGroup( "45%", "10%", "25%", "20%", "10%" ),
	HtmlTag::create( 'thead', $heads ),
	HtmlTag::create( 'tbody', $rows ),
], ['class' => 'table not-table-condensed table-striped', 'id' => 'roles'] );

$panelFilter	= '';

$iconAdd		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-plus'] );

$buttonAdd	= '';
if( $hasRightToAdd ){
	$buttonAdd	= HtmlTag::create( 'a', $iconAdd.'&nbsp;'.$wf->buttonAdd, [
		'href'	=> './manage/role/add',
		'class'	=> 'btn btn-success'
	] );
}

extract( $view->populateTexts( ['index.top', 'index.bottom'], 'html/manage/role/' ) );

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
					'.$table.'
					<div class="buttonbar">
						<div class="btn-toolbar">
							'.$buttonAdd.'
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>'.$textIndexBottom;
