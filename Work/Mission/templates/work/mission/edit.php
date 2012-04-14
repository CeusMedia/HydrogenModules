<?php

$w	= (object) $words['edit'];

$priorities		= $words['priorities'];
unset( $priorities[0] );
$optPriority	= UI_HTML_Elements::Options( $priorities, $mission->priority );
$optStatus		= UI_HTML_Elements::Options( $words['states'], $mission->status );

$panelEdit	= '
<form action="./work/mission/edit/'.$mission->missionId.'" method="post">
	<fieldset>
		<legend>'.$w->legend.'</legend>
		<ul class="input">
			<li>
				<label for="input_content">'.$w->labelContent.'</label><br/>
				<input type="text" name="content" id="input_content" class="max" value="'.$mission->content.'"/>
			</li>
			<li>
				<label for="input_reference">'.$w->labelReference.'</label><br/>
				<input type="text" name="reference" id="input_reference" class="max" value="'.$mission->reference.'"/>
			</li>
			<li>
				<div class="column-left-20">
					<label for="input_day">'.$w->labelDay.'</label><br/>
					<input type="text" name="day" id="input_day" value="'.$mission->day.'" autocomplete="off"/>
				</div>
				<div class="column-left-20">
					<label for="input_priority">'.$w->labelPriority.'</label><br/>
					<select name="priority" id="input_priority" class="max">'.$optPriority.'</select>
				</div>
				<div class="column-left-20">
					<label for="input_status">'.$w->labelStatus.'</label><br/>
					<select name="status" id="input_status" class="max">'.$optStatus.'</select>
				</div>
				<div class="column-clear"></div>
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::LinkButton( './work/mission', $w->buttonCancel, 'button cancel' ).'
			'.UI_HTML_Elements::Button( 'edit', $w->buttonSave, 'button edit' ).'
		</div>
	</fieldset>	
</form>
';

$list	= array();
foreach( $words['states'] as $status => $label ){
	$attributes	= array(
		'type'		=> 'button',
		'onclick'	=> 'document.location.href=\'./work/mission/setStatus/'.$mission->missionId.'/'.urlencode( $status ).'\';',
		'disabled'	=> $mission->status == $status ? 'disabled' : NULL,
		'class'		=> 'button',
		'style'		=> 'width: 120px',
	);
	$button	= UI_HTML_Tag::create( 'button', $label, $attributes );
	$list[]	= $button;
}
$states	= join( '<br/>', $list );


$priorities		= $words['priorities'];
unset( $priorities[0] );
$list	= array();
foreach( $priorities as $priority => $label ){
	$attributes	= array(
		'type'		=> 'button',
		'onclick'	=> 'document.location.href=\'./work/mission/setPriority/'.$mission->missionId.'/'.$priority.'\';',
		'disabled'	=> $mission->priority == $priority ? 'disabled' : NULL,
		'class'		=> 'button',
		'style'		=> 'width: 120px',
	);
	$button	= UI_HTML_Tag::create( 'button', $label, $attributes );
	$list[]	= $button;
}
$priorities	= join( '<br/>', $list );

$panelStatus	= '
<fieldset>
	<legend>Priorität und Status</legend>
	<div class="column-left-50">
		<h3>Priorität</h3>
		'.$priorities.'
	</div>
	<div class="column-left-50">
		<h3>Status</h3>
		'.$states.'
	</div>
</fieldset>
';

return '
<div class="column-right-30">
	'.$panelStatus.'
</div>
<div class="column-left-70">
	'.$panelEdit.'
</div>
<div class="column-clear"></div>
';
?>