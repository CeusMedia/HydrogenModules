<?php
$optStatus	= $words['states-thread'];
$optStatus	= UI_HTML_Elements::Options( $optStatus, (int) $request->get( 'status' ) );

$optType	= $words['types'];
$optType	= UI_HTML_Elements::Options( $optType, (int) $request->get( 'type' ) );

if( !in_array( 'addThread', $rights ) )
	return '';
return '
<h4>Neues Thema</h4>
<form action="./info/forum/addThread" method="post">
	<div class="row-fluid">
		<div class="span12">
			<label for="input_title">Title</label>
			<input type="text" name="title" id="input_title" class="span12" value="" required="required"/>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span6">
			<label for="input_type">Type</label>
			<select name="type" id="input_type" class="span12">'.$optType.'</select>
		</div>
		<div class="span6">
			<label for="input_status">Title</label>
			<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
		</div>
	</div>
	<div class="buttonbar">
		<button type="submit" name="save" value="1" class="btn btn-success btn-small"><i class="icon-ok icon-white"></i> speichern</button>
	</div>
</form>';
?>