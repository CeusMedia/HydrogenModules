<?php

$w			= (object) $words['filter'];

/*  --  STATUS  --  */
$optStatus	= array();
foreach( array_reverse( $words['states'], TRUE ) as $key => $value ){
	$attributes		= array(
		'value'		=> $key,
		'class'		=> 'project status'.$key,
		'selected'	=> in_array( $key, $filterStatus ) ? 'selected' : NULL
	);
	$optStatus[]	= UI_HTML_Tag::create( 'option', $value, $attributes );
}
$optStatus	= join( '', $optStatus );

/*  --  PRIORITY  --  */
$optPriority	= array();
foreach( $words['priorities'] as $key => $value ){
	$attributes		= array(
		'value'		=> $key,
		'class'		=> 'project priority'.$key,
		'selected'	=> in_array( $key, $filterPriority ) ? 'selected' : NULL
	);
	$optPriority[]	= UI_HTML_Tag::create( 'option', $value, $attributes );
}
$optPriority	= join( '', $optPriority );

/*  --  USERS  --  */
$optUser	= array();
foreach( $users as $user ){
	$attributes		= array(
		'value'		=> $user->userId,
		'class'		=> 'user user-status status'.$user->status,
		'selected'	=> in_array( $user->userId, $filterUser ) ? 'selected' : NULL
	);
	$optUser[]		= UI_HTML_Tag::create( 'option', $user->username, $attributes );
}
$optUser	= join( '', $optUser );

$optOrder	= UI_HTML_Elements::Options( $words['filter-orders'], $filterOrder );

$iconOrderAsc	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-up' ) );
$iconOrderDesc	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-down' ) );
$iconFilter		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-search icon-white' ) );
$iconReset		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-zoom-out icon-white' ) );

$disabled   = $filterDirection == 'ASC';
$buttonUp	= UI_HTML_Elements::LinkButton( './manage/project/filter/?direction=ASC', $iconOrderAsc, 'btn not-btn-small', NULL, $disabled );
$buttonDown	= UI_HTML_Elements::LinkButton( './manage/project/filter/?direction=DESC', $iconOrderDesc, 'btn not-btn-small', NULL, !$disabled );

$buttonFilter	= UI_HTML_Elements::Button( 'filter', $iconFilter.'&nbsp;'.$w->buttonFilter, 'btn not-btn-small btn-info' );
$buttonReset	= UI_HTML_Elements::LinkButton( './manage/project/filter/reset', $iconReset.'&nbsp;'.$w->buttonReset, 'btn btn-small btn-inverse' );

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
				<div class="span8">
					<label for="input_status">Sortierung</label>
					<select name="order" id="input_order" class="span12" onchange="this.form.submit()">'.$optOrder.'</select>
				</div>
				<div class="span4">
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
				'.$buttonFilter.'
				'.$buttonReset.'
			</div>
		</form>
	</div>
</div>';

return $panelFilter;
?>
