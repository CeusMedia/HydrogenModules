<?php
//  --  FILTER  --  //

$session	= $env->getSession();

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

$optOrder	= array( '' => '-' );
foreach( $words['indexFilterOrders'] as $column => $label )
	$optOrder[$column]	= $label;
$optOrder['_selected']	= $env->getSession()->get( 'filter-user-order' );

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
$env->page->js->addScript( $script );

return '
<div class="content-panel">
	<h3>'.$words['indexFilter']['heading'].'</h3>
	<div class="content-panel-inner">
		<form id="form_filter-users" name="filterUsers" action="./manage/user/filter" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="username">'.$words['indexFilter']['labelUsername'].'</label>
					'.UI_HTML_Elements::Input( 'username', $username, 'span12  complete-username' ).'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="roleId">'.$words['indexFilter']['labelRole'].'</label>
					'.UI_HTML_Elements::Select( 'roleId', $optRole, 'span12', NULL, '' ).'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="status">'.$words['indexFilter']['labelStatus'].'</label>
					'.UI_HTML_Elements::Select( 'status', $optStatus, 'span12', NULL, '' ).'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="order">'.$words['indexFilter']['labelOrder'].'</label>
					'.UI_HTML_Elements::Select( 'order', $optOrder, 'span12', NULL, '' ).'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="direction">'.$words['indexFilter']['labelDirection'].'</label>
					'.UI_HTML_Elements::Select( 'direction', $optDirection, 'span12', NULL, '' ).'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<div class="row-fluid">
						<div class="span7">
							<label for="limit">'.$words['indexFilter']['labelLimit'].'</label>
						</div>
						<div class="span5">
						'.UI_HTML_Elements::Input( 'limit', $limit, 'span12 numeric' ).'
						</div>
					</div>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<div class="buttonbar">
						'.UI_HTML_Elements::Button( 'filter', '<i class="icon-zoom-in icon-white"></i> '.$words['indexFilter']['buttonFilter'], 'btn btn-small not-btn-info btn-primary' ).'
						'.UI_HTML_Elements::LinkButton( './manage/user/filter/reset', '<i class="icon-zoom-out"></i> '.$words['indexFilter']['buttonReset'], 'btn btn-small ' ).'
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
';
?>
