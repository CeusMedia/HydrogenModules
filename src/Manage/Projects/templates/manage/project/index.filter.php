<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w			= (object) $words['filter'];

$isFiltered	= count( $filterStatus ) || count( $filterPriority ) || count( $filterUser ) || $filterId || $filterQuery;

/*  --  STATUS  --  */
$optStatus	= [];
foreach( array_reverse( $words['states'], TRUE ) as $key => $value ){
	$attributes		= array(
		'value'		=> $key,
		'class'		=> 'project status'.$key,
		'selected'	=> in_array( $key, $filterStatus ) ? 'selected' : NULL
	);
	$optStatus[]	= HtmlTag::create( 'option', $value, $attributes );
}
$optStatus	= join( '', $optStatus );

/*  --  PRIORITY  --  */
$optPriority	= [];
foreach( $words['priorities'] as $key => $value ){
	$attributes		= array(
		'value'		=> $key,
		'class'		=> 'project priority'.$key,
		'selected'	=> in_array( $key, $filterPriority ) ? 'selected' : NULL
	);
	$optPriority[]	= HtmlTag::create( 'option', $value, $attributes );
}
$optPriority	= join( '', $optPriority );

/*  --  USERS  --  */
$optUser	= [];
foreach( $users as $user ){
	$attributes		= array(
		'value'		=> $user->userId,
		'class'		=> 'user user-status status'.$user->status,
		'selected'	=> in_array( $user->userId, $filterUser ) ? 'selected' : NULL
	);
	$optUser[]		= HtmlTag::create( 'option', $user->username, $attributes );
}
$optUser	= join( '', $optUser );

$optOrder	= HtmlElements::Options( $words['filter-orders'], $filterOrder );

$iconOrderAsc	= HtmlTag::create( 'i', '', ['class' => 'icon-arrow-up'] );
$iconOrderDesc	= HtmlTag::create( 'i', '', ['class' => 'icon-arrow-down'] );
$iconFilter		= HtmlTag::create( 'i', '', ['class' => 'icon-search icon-white'] );
$iconReset		= HtmlTag::create( 'i', '', ['class' => 'icon-zoom-out icon-white'] );

$iconOrderAsc	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-chevron-up'] );
$iconOrderDesc	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-chevron-down'] );
$iconFilter		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-search'] );
$iconReset		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-search-minus'] );

$disabled   	= $filterDirection == 'ASC';
$buttonUp		= HtmlElements::LinkButton( './manage/project/filter/?direction=ASC', $iconOrderAsc, 'btn not-btn-small', NULL, $disabled );
$buttonDown		= HtmlElements::LinkButton( './manage/project/filter/?direction=DESC', $iconOrderDesc, 'btn not-btn-small', NULL, !$disabled );

$buttonFilter	= HtmlElements::Button( 'filter', $iconFilter.'&nbsp;'.$w->buttonFilter, 'btn not-btn-small btn-info' );
$buttonReset	= HtmlTag::create( 'a', $iconReset/*.'&nbsp;'.$w->buttonReset*/, array(
	'href'		=> './manage/project/filter/reset',
	'title'		=> $w->buttonReset,
	'class'		=> 'btn not-btn-small '.( $isFiltered ? 'btn-inverse' : '' )
) );

$panelFilter = '
<div class="content-panel content-panel-filter">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form name="" action="./manage/project/filter" method="post">
			<div class="row-fluid">
				<div class="span10">
					<label for="input_query">Suchbegriff</label>
					<input type="text" name="query" id="input_query" class="span12" value="'.htmlentities( $filterQuery, ENT_QUOTES, 'utf-8' ).'"/>
				</div>
				<div class="span2">
					<label for="input_id">ID</label>
					<input type="text" name="id" id="input_id" class="span12" value="'.htmlentities( $filterId, ENT_QUOTES, 'utf-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_status">Status</label>
					<select name="status[]" multiple id="input_status" size="6" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_priority">Priorität</label>
					<select name="priority[]" multiple id="input_priority" size="6" class="span12">'.$optPriority.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_user">Mitarbeiter</label>
					<select name="user[]" multiple id="input_user" size="6" class="span12">'.$optUser.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span7">
					<label for="input_status">Sortierung</label>
					<select name="order" id="input_order" class="span12" onchange="this.form.submit()">'.$optOrder.'</select>
				</div>
				<div class="span5">
					<label>&nbsp;</label>
					<div class="btn-group">'.$buttonUp.$buttonDown.'</div>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span3">
					<input type="text" name="limit" id="input_limit" class="span12" value="'.htmlentities( $filterLimit, ENT_QUOTES, 'utf-8' ).'"/>
				</div>
				<div class="span9" style="padding-top: 4px">
					<label for="input_status">pro Seite</label>
				</div>
			</div>
			<div class="buttonbar">
				<div class="btn-group">
					'.$buttonFilter.'
					'.$buttonReset.'
				</div>
			</div>
		</form>
	</div>
</div>';

return $panelFilter;
?>
