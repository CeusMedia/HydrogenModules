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
	$iconFilter		= UI_HTML_Tag::create( 'b', '', array( 'class' => 'far fa-fw fa-search' ) );
	$iconReset		= UI_HTML_Tag::create( 'b', '', array( 'class' => 'far fa-fw fa-search-minus' ) );
}

return '
<div class="content-panel content-panel-filter">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form id="form_filter-users" name="filterUsers" action="./manage/user/filter" method="post">
			<div class="bs2-row-fluid bs3-row bs4-row">
				<div class="bs2-span12 bs3-col-md-12 bs3-form-group bs4-col-md-12 bs4-form-group">
					<label for="username">'.$w->labelUsername.'</label>
					'.UI_HTML_Elements::Input( 'username', $username, 'bs2-span12 bs3-form-control bs4-form-control complete-username' ).'
				</div>
			</div>
			<div class="bs2-row-fluid bs3-row bs4-row">
				<div class="bs2-span12 bs3-col-md-12 bs3-form-group bs4-col-md-12 bs4-form-group">
					<label for="roleId">'.$w->labelRole.'</label>
					'.UI_HTML_Elements::Select( 'roleId', $optRole, 'bs2-span12 bs3-form-control bs4-form-control', NULL, '' ).'
				</div>
			</div>
			<div class="bs2-row-fluid bs3-row bs4-row">
				<div class="bs2-span12 bs3-col-md-12 bs3-form-group bs4-col-md-12 bs4-form-group">
					<label for="status">'.$w->labelStatus.'</label>
					'.UI_HTML_Elements::Select( 'status', $optStatus, 'bs2-span12 bs3-form-control bs4-form-control', NULL, '' ).'
				</div>
			</div>
			<div class="bs2-row-fluid bs3-row bs4-row">
				<div class="bs2-span12 bs3-col-md-12 bs3-form-group bs4-col-md-12 bs4-form-group">
					<label for="order">'.$w->labelOrder.'</label>
					'.UI_HTML_Elements::Select( 'order', $optOrder, 'bs2-span12 bs3-form-control bs4-form-control', NULL, '' ).'
				</div>
			</div>
			<div class="bs2-row-fluid bs3-row bs4-row">
				<div class="bs2-span7 bs3-col-md-7 bs3-form-group bs4-col-md-7 bs4-form-group">
					<label for="direction">'.$w->labelDirection.'</label>
					'.UI_HTML_Elements::Select( 'direction', $optDirection, 'bs2-span12 bs3-form-control bs4-form-control', NULL, '' ).'
				</div>
				<div class="bs2-span5 bs3-col-md-5 bs3-form-group bs4-col-md-5 bs4-form-group">
					<label for="limit">'.$w->labelLimit.'</label>
					'.UI_HTML_Elements::Input( 'limit', $limit, 'bs2-span12 bs3-form-control bs4-form-control numeric' ).'
				</div>
			</div>
			<div class="buttonbar">
				'.UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'button', $iconFilter.' '.$w->buttonFilter, array(
						'type'		=> 'submit',
						'name'		=> 'filter',
						'title'		=> $w->buttonFilter,
						'class'		=> 'btn not-btn-sm btn-info'
					) ),
					UI_HTML_Tag::create( 'a', $iconReset, array(
						'href'		=> './manage/user/filter/reset',
						'title'		=> $w->buttonReset,
						'class'		=> 'btn not-btn-sm bs2-btn-inverse bs3-btn-default bs4-btn-dark'
					) ),
				), array( 'class' => 'btn-group' ) ).'
			</div>
		</form>
	</div>
</div>';
?>
