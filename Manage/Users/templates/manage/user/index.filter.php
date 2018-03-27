<?php
//  --  FILTER  --  //
$w			= (object) $words['indexFilter'];
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

$iconFilter		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-zoom-in icon-white' ) );
$iconReset		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-zoom-out icon-white' ) );
if( $env->getModules()->get( 'UI_Font_FontAwesome' ) ){
	$iconFilter		= UI_HTML_Tag::create( 'b', '', array( 'class' => 'fa fa-fw fa-search' ) );
	$iconReset		= UI_HTML_Tag::create( 'b', '', array( 'class' => 'fa fa-fw fa-search-minus' ) );
}

return '
<div class="content-panel content-panel-filter">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form id="form_filter-users" name="filterUsers" action="./manage/user/filter" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="username">'.$w->labelUsername.'</label>
					'.UI_HTML_Elements::Input( 'username', $username, 'span12  complete-username' ).'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="roleId">'.$w->labelRole.'</label>
					'.UI_HTML_Elements::Select( 'roleId', $optRole, 'span12', NULL, '' ).'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="status">'.$w->labelStatus.'</label>
					'.UI_HTML_Elements::Select( 'status', $optStatus, 'span12', NULL, '' ).'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="order">'.$w->labelOrder.'</label>
					'.UI_HTML_Elements::Select( 'order', $optOrder, 'span12', NULL, '' ).'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span7">
					<label for="direction">'.$w->labelDirection.'</label>
					'.UI_HTML_Elements::Select( 'direction', $optDirection, 'span12', NULL, '' ).'
				</div>
				<div class="span5">
					<div class="row-fluid">
						<label for="limit">'.$w->labelLimit.'</label>
						'.UI_HTML_Elements::Input( 'limit', $limit, 'span12 numeric' ).'
					</div>
				</div>
			</div>
			<div class="buttonbar">
				'.UI_HTML_Tag::create( 'div', array(
					UI_HTML_Elements::Button( 'filter', $iconFilter.' '.$w->buttonFilter, 'btn not-btn-small not-btn-info btn-primary' ),
					UI_HTML_Tag::create( 'a', $iconReset, array(
						'href'		=> './manage/user/filter/reset',
						'title'		=> $w->buttonReset,
						'class'		=> 'btn not-btn-small btn-inverse'
					) ),
				), array( 'class' => 'btn-group' ) ).'
			</div>
		</form>
	</div>
</div>';
?>
