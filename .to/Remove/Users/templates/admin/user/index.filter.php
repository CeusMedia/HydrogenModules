<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

//  --  FILTER  --  //

$session	= $env->getSession();

$optRole	= array( HtmlElements::Option( '', '' ) );
foreach( array_reverse( $roles ) as $role ){
	$selected	= $role->roleId == $session->get( 'filter-user-roleId' );
	$class		= 'role role'.$role->roleId;
	$optRole[]	= HtmlElements::Option( $role->roleId, $role->title, $selected, FALSE, $class );
}
$optRole	= join( $optRole );

krsort( $states );
$optStatus	= array( HtmlElements::Option( '', '' ) );
foreach( $states as $status => $label ){
	$selected	= (string) $status === (string) $session->get( 'filter-user-status' );
	$class		= 'user-status status'.$status;
	$optStatus[]	= HtmlElements::Option( (string) $status, $label, $selected, FALSE, $class );
}
$optStatus	= join( $optStatus );

$optOrder	= array( '' => '-' );
foreach( $words['indexFilterOrders'] as $column => $label )
	$optOrder[$column]	= $label;
$optOrder['_selected']	= $env->getSession()->get( 'filter-user-order' );

$optDirection	= array( HtmlElements::Option( '', '' ) );
foreach( $words['indexFilterDirections'] as $key => $label ){
	$selected	= $key == $session->get( 'filter-user-direction' );
	$class		= 'direction direction'.$key;
	$optDirection[]	= HtmlElements::Option( $key, $label, $selected, FALSE, $class );
}
$optDirection	= join( $optDirection );

$script	= '
/*$(document).ready(function(){
	UI.autocompleteUser("input.complete-username");
});*/
';
$env->page->js->addScript( $script );

return '
<form id="form_filter-users" name="filterUsers" action="./admin/user/filter" method="post">
	<fieldset>
		<legend class="filter">'.$words['indexFilter']['legend'].'</legend>
		<ul class="input">
			<li>
				<label for="username">'.$words['indexFilter']['labelUsername'].'</label><br/>
				'.HtmlElements::Input( 'username', $username, 'm complete-username' ).'
			</li>
			<li>
				<label for="roleId">'.$words['indexFilter']['labelRole'].'</label><br/>
				'.HtmlElements::Select( 'roleId', $optRole, 'm', NULL, '' ).'
			</li>
			<li>
				<label for="status">'.$words['indexFilter']['labelStatus'].'</label><br/>
				'.HtmlElements::Select( 'status', $optStatus, 'm', NULL, '' ).'
			</li>
			<li>
				<label for="order">'.$words['indexFilter']['labelOrder'].'</label><br/>
				'.HtmlElements::Select( 'order', $optOrder, 'm', NULL, '' ).'
			</li>
			<li>
				<label for="direction">'.$words['indexFilter']['labelDirection'].'</label><br/>
				'.HtmlElements::Select( 'direction', $optDirection, 'm', NULL, '' ).'
			</li>
			<li>
				<label for="limit">'.$words['indexFilter']['labelLimit'].'</label><br/>
				'.HtmlElements::Input( 'limit', $limit, 'xs numeric' ).'
			</li>
		</ul>
		<div class="buttonbar">
			'.HtmlElements::Button( 'filter', $words['indexFilter']['buttonFilter'], 'button filter' ).'
			'.HtmlElements::LinkButton( './admin/user/filter/reset', $words['indexFilter']['buttonReset'], 'button reset' ).'
		</div>
	</fieldset>
</form>
';
?>