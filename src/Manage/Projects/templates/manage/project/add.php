<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var array $words */
/** @var ?string $from */

$w	= (object) $words['add'];

/*  --  STATES  --  */
$optStatus	= [];
foreach( array_reverse( $words['states'], TRUE ) as $key => $value ){
	$attributes		= array(
		'value'		=> $key,
		'class'		=> 'project status'.$key,
		'selected'	=> ( $key == 0 ? 'selected' : NULL )
	);
	$optStatus[]	= HtmlTag::create( 'option', $value, $attributes );
}
$optStatus		= join( '', $optStatus );

/*  --  PRIORITIES  --  */
$optPriority	= [];
foreach( $words['priorities'] as $key => $value ){
	$attributes		= array(
		'value'		=> $key,
		'class'		=> 'project priority'.$key,
		'selected'	=> ( $key == 0 ? 'selected' : NULL )
	);
	$optPriority[]	= HtmlTag::create( 'option', $value, $attributes );
}
$optPriority		= join( '', $optPriority );

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'not-icon-arrow-left icon-list'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'icon-ok icon-white'] );

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.'&nbsp;'.$w->buttonCancel, [
	'href'	=> './'.( $from ?: 'manage/project' ),
	'class'	=> 'btn btn-small',
] );
if( $from && str_ends_with( $from, 'add' ) )
	$buttonCancel	= "";

$buttonSave		= HtmlTag::create( 'button', $iconSave.'&nbsp;'.$w->buttonSave, [
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary'
] );

$panelAdd		= '
<div class="content-panel content-panel-form">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form name="" action="./manage/project/add" method="post">
			<input type="hidden" name="from" value="'.$from.'"/>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title" class="mandatory">'.$w->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12 max mandatory" required="required"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_description">'.$w->labelDescription.'</label>
					<textarea name="description" id="input_description" rows="6" class="span12 max CodeMirror-auto"></textarea>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span3">
					<label for="input_status" class="mandatory">'.$w->labelStatus.'</label>
					<select name="status" id="input_status" class="span12 max">'.$optStatus.'</select>
				</div>
				<div class="span3">
					<label for="input_priority" class="not-mandatory">'.$w->labelPriority.'</label>
					<select name="priority" id="input_priority" class="span12 max">'.$optPriority.'</select>
				</div>
				<div class="span6">
					<label for="input_url">'.$w->labelUrl.'</label>
					<input type="text" name="url" id="input_url" class="span12 max"/>
				</div>
			</div>
			<div class="buttonbar">
				'.$buttonCancel.'
				'.$buttonSave.'
			</div>
		</form>
	</div>
</div>';

return '
<div class="row-fluid">
	<div class="span8">
		'.$panelAdd.'
	</div>
	<div class="span4">
	</div>
</div>';
