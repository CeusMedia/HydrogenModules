<?php

$w	= (object) $words['add'];

/*  --  STATES  --  */
$optStatus	= array();
foreach( array_reverse( $words['states'], TRUE ) as $key => $value ){
	$attributes		= array(
		'value'		=> $key,
		'class'		=> 'project status'.$key,
		'selected'	=> ( $key == 0 ? 'selected' : NULL )
	);
	$optStatus[]	= UI_HTML_Tag::create( 'option', $value, $attributes );
}
$optStatus		= join( '', $optStatus );

/*  --  PRIORITIES  --  */
$optPriority	= array();
foreach( $words['priorities'] as $key => $value ){
	$attributes		= array(
		'value'		=> $key,
		'class'		=> 'project priority'.$key,
		'selected'	=> ( $key == 0 ? 'selected' : NULL )
	);
	$optPriority[]	= UI_HTML_Tag::create( 'option', $value, $attributes );
}
$optPriority		= join( '', $optPriority );

$panelAdd		= '
<div class="content-panel content-panel-form">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form name="" action="./manage/project/add" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title" class="mandatory">'.$w->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12 max mandatory"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_description">'.$w->labelDescription.'</label>
					<textarea name="description" id="input_description" rows="6" class="span12 max CodeMirror-auto"></textarea>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span4">
					<label for="input_status" class="mandatory">'.$w->labelStatus.'</label>
					<select name="status" id="input_status" class="span12 max">'.$optStatus.'</select>
				</div>
				<div class="span4">
					<label for="input_priority" class="not-mandatory">'.$w->labelPriority.'</label>
					<select name="priority" id="input_priority" class="span12 max">'.$optPriority.'</select>
				</div>
				<div class="span4">
					<label for="input_url">'.$w->labelUrl.'</label>
					<input type="text" name="url" id="input_url" class="span12 max"/>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./manage/project" class="btn btn-small"><i class="not-icon-arrow-left icon-list"></i> '.$w->buttonCancel.'</a>
				<button type="submit" name="save" class="btn not-btn-small btn-success"><i class="icon-ok icon-white"></i> '.$w->buttonSave.'</button>
			</div>
		</form>
	</div>
</div>';

return '
<div class="row-fluid">
	<div class="span9">
		'.$panelAdd.'
	</div>
	<div class="span3">
	</div>
</div>
';

$panelFilter	= $view->loadTemplateFile( 'manage/project/index.filter.php' );
return '
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span6">
		'.$panelAdd.'
	</div>
	<div class="span3">
	</div>
</div>
';
?>
