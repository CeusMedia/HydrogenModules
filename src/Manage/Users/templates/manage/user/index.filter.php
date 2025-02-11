<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var View_Manage_User $view */
/** @var array<string,array<string|int,string|int>> $words */
/** @var array<object> $roles */
/** @var int $limit */
/** @var ?string $username */

//  --  FILTER  --  //
$w			= (object) $words['indexFilter'];
$session	= $env->getSession();

$optRole	= [HtmlElements::Option( '', '' )];
foreach( array_reverse( $roles ) as $role ){
	$selected	= $role->roleId == $session->get( 'filter-user-roleId' );
	$class		= 'role role'.$role->roleId;
	$optRole[]	= HtmlElements::Option( $role->roleId, $role->title, $selected, FALSE, $class );
}
$optRole	= join( $optRole );

krsort( $states );
$optStatus	= [HtmlElements::Option( '', '' )];
foreach( $states as $status => $label ){
	$selected	= (string) $status === (string) $session->get( 'filter-user-status' );
	$class		= 'user-status status'.$status;
	$optStatus[]	= HtmlElements::Option( (string) $status, $label, $selected, FALSE, $class );
}
$optStatus	= join( $optStatus );

$optOrder	= ['' => '-'];
foreach( $words['indexFilterOrders'] as $column => $label ){
	$selected	= $column == $session->get( 'filter-user-order' );
	$optOrder[]	= HtmlElements::Option( $column, $label, $selected );
}
$optOrder   = join( $optOrder );

$optDirection	= [HtmlElements::Option( '', '' )];
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
$env->getPage()->js->addScript( $script );

$iconFilter		= HtmlTag::create( 'i', '', ['class' => 'icon-zoom-in icon-white'] );
$iconReset		= HtmlTag::create( 'i', '', ['class' => 'icon-zoom-out icon-white'] );
if( $env->getModules()->get( 'UI_Font_FontAwesome' ) ){
	$iconFilter		= HtmlTag::create( 'b', '', ['class' => 'fa far fa-fw fa-search'] );
	$iconReset		= HtmlTag::create( 'b', '', ['class' => 'fa far fa-fw fa-search-minus'] );
}

return '
<div class="content-panel content-panel-filter">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form id="form_filter-users" name="filterUsers" action="./manage/user/filter" method="post">
			<div class="bs2-row-fluid bs3-row bs4-row">
				<div class="bs2-span12 bs3-col-md-12 bs3-form-group bs4-col-md-12 bs4-form-group">
					<label for="username">'.$w->labelUsername.'</label>
					'.HtmlElements::Input( 'username', $username, 'bs2-span12 bs3-form-control bs4-form-control complete-username' ).'
				</div>
			</div>
			<div class="bs2-row-fluid bs3-row bs4-row">
				<div class="bs2-span12 bs3-col-md-12 bs3-form-group bs4-col-md-12 bs4-form-group">
					<label for="roleId">'.$w->labelRole.'</label>
					'.HtmlElements::Select( 'roleId', $optRole, 'bs2-span12 bs3-form-control bs4-form-control', NULL, '' ).'
				</div>
			</div>
			<div class="bs2-row-fluid bs3-row bs4-row">
				<div class="bs2-span12 bs3-col-md-12 bs3-form-group bs4-col-md-12 bs4-form-group">
					<label for="status">'.$w->labelStatus.'</label>
					'.HtmlElements::Select( 'status', $optStatus, 'bs2-span12 bs3-form-control bs4-form-control', NULL, '' ).'
				</div>
			</div>
			<div class="bs2-row-fluid bs3-row bs4-row">
				<div class="bs2-span12 bs3-col-md-12 bs3-form-group bs4-col-md-12 bs4-form-group">
					<label for="order">'.$w->labelOrder.'</label>
					'.HtmlElements::Select( 'order', $optOrder, 'bs2-span12 bs3-form-control bs4-form-control', NULL, '' ).'
				</div>
			</div>
			<div class="bs2-row-fluid bs3-row bs4-row">
				<div class="bs2-span7 bs3-col-md-7 bs3-form-group bs4-col-md-7 bs4-form-group">
					<label for="direction">'.$w->labelDirection.'</label>
					'.HtmlElements::Select( 'direction', $optDirection, 'bs2-span12 bs3-form-control bs4-form-control', NULL, '' ).'
				</div>
				<div class="bs2-span5 bs3-col-md-5 bs3-form-group bs4-col-md-5 bs4-form-group">
					<label for="limit">'.$w->labelLimit.'</label>
					'.HtmlElements::Input( 'limit', $limit, 'bs2-span12 bs3-form-control bs4-form-control numeric' ).'
				</div>
			</div>
			<div class="buttonbar">
				'.HtmlTag::create( 'div', array(
					HtmlTag::create( 'button', $iconFilter.' '.$w->buttonFilter, [
						'type'		=> 'submit',
						'name'		=> 'filter',
						'title'		=> $w->buttonFilter,
						'class'		=> 'btn not-btn-sm btn-info'
					] ),
					HtmlTag::create( 'a', $iconReset, [
						'href'		=> './manage/user/filter/reset',
						'title'		=> $w->buttonReset,
						'class'		=> 'btn not-btn-sm bs2-btn-inverse bs3-btn-default bs4-btn-dark'
					] ),
				), ['class' => 'btn-group'] ).'
			</div>
		</form>
	</div>
</div>';
