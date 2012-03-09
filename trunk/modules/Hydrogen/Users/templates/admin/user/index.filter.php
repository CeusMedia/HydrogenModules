<?php
//  --  FILTER  --  //

$session	= $this->env->getSession();



$optRole	= array( UI_HTML_Elements::Option( '', '' ) );
foreach( array_reverse( $roles ) as $role ){
	$selected	= $role->roleId == $session->get( 'filter-user-roleId' );
	$class		= 'role role'.$role->roleId;
	$optRole[]	= UI_HTML_Elements::Option( $role->roleId, $role->title, $selected, FALSE, $class );
}
$optRole	= join( $optRole );

krsort( $states );
$optStatus	= array( UI_HTML_Elements::Option( '', '' ) );
foreach( $states as $status => $label ){
	$selected	= (string) $status === (string) $session->get( 'filter-user-status' );
	$class		= 'user-status status'.$status;
	$optStatus[]	= UI_HTML_Elements::Option( (string) $status, $label, $selected, FALSE, $class );
}
$optStatus	= join( $optStatus );

krsort( $activities );
$optActivitiy	= array( UI_HTML_Elements::Option( '', '' ) );
foreach( $activities as $activity => $label ){
	$selected	= (string) $activity === (string) $session->get( 'filter-user-activity' );
	$class		= 'user-activity activity'.$activity;
	$optActivitiy[]	= UI_HTML_Elements::Option( (string) $activity, $label, $selected, FALSE, $class );
}
$optActivitiy	= join( $optActivitiy );

$optOrder	= array( '' => '-' );
foreach( $words['indexFilterOrders'] as $column => $label )
	$optOrder[$column]	= $label;
$optOrder['_selected']	= $this->env->getSession()->get( 'filter-user-order' );

$optDirection	= array( UI_HTML_Elements::Option( '', '' ) );
foreach( $words['indexFilterDirections'] as $key => $label ){
	$selected	= $key == $session->get( 'filter-user-direction' );
	$class		= 'direction direction'.$key;
	$optDirection[]	= UI_HTML_Elements::Option( $key, $label, $selected, FALSE, $class );
}
$optDirection	= join( $optDirection );

$script	= '
/*$(document).ready(function(){
	UI.autocompleteUser("input.complete-username");
});*/
';
$this->env->page->js->addScript( $script );

return '
<form id="form_filter-users" name="filterUsers" action="./admin/user/filter" method="post">
	<fieldset>
		<legend class="filter">'.$words['indexFilter']['legend'].'</legend>
		<ul class="input">
			<li>
				<label for="username">'.$words['indexFilter']['labelUsername'].'</label><br/>
				'.UI_HTML_Elements::Input( 'username', $username, 'm complete-username' ).'
			</li>
			<li>
				<label for="roleId">'.$words['indexFilter']['labelRole'].'</label><br/>
				'.UI_HTML_Elements::Select( 'roleId', $optRole, 'm', NULL, '' ).'
			</li>
			<li>
				<label for="status">'.$words['indexFilter']['labelStatus'].'</label><br/>
				'.UI_HTML_Elements::Select( 'status', $optStatus, 'm', NULL, '' ).'
			</li>
			<li>
				<label for="activity">'.$words['indexFilter']['labelActivity'].'</label><br/>
				'.UI_HTML_Elements::Select( 'activity', $optActivitiy, 'm', NULL, '' ).'
			</li>
			<li>
				<label for="order">'.$words['indexFilter']['labelOrder'].'</label><br/>
				'.UI_HTML_Elements::Select( 'order', $optOrder, 'm', NULL, '' ).'
			</li>
			<li>
				<label for="direction">'.$words['indexFilter']['labelDirection'].'</label><br/>
				'.UI_HTML_Elements::Select( 'direction', $optDirection, 'm', NULL, '' ).'
			</li>
			<li>
				<label for="limit">'.$words['indexFilter']['labelLimit'].'</label><br/>
				'.UI_HTML_Elements::Input( 'limit', $limit, 'xs numeric' ).'
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::Button( 'filter', $words['indexFilter']['buttonFilter'], 'button filter' ).'
			'.UI_HTML_Elements::LinkButton( './admin/user/filter/reset', $words['indexFilter']['buttonReset'], 'button reset' ).'
		</div>
	</fieldset>
</form>
';
?>