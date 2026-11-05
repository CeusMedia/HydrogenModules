<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

/** @var Dictionary $request */
/** @var array $rights */
/** @var array<string,array<string,string>> $words */

$optStatus	= $words['states-thread'];
$optStatus	= HtmlElements::Options( $optStatus, (int) $request->get( 'status' ) );

$optType	= $words['types'];
$optType	= HtmlElements::Options( $optType, (int) $request->get( 'type' ) );

$w	= (object) $words['index-add'];

if( !in_array( 'addTopic', $rights ) )
	return '';
return '
<div class="content-panel">
	<h4>'.$w->heading.'</h4>
	<div class="content-panel-inner">
		<form action="./info/forum/addTopic" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title">'.$w->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12" value="" required="required"/>
				</div>
			</div>
<!--			<div class="row-fluid">
				<div class="span6">
					<label for="input_type">Type</label>
					<select name="type" id="input_type" class="span12">'.$optType.'</select>
				</div>
				<div class="span6">
					<label for="input_status">Title</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>-->
			<div class="buttonbar">
				<button type="submit" name="save" value="1" class="btn btn-primary btn-small"><i class="fa fa-fw fa-check"></i> '.$w->buttonSave.'</button>
			</div>
		</form>
	</div>
</div>';
