<?php
$optStatus	= $words['states-thread'];
$optStatus	= UI_HTML_Elements::Options( $optStatus, (int) $request->get( 'status' ) );

$optType	= $words['types'];
$optType	= UI_HTML_Elements::Options( $optType, (int) $request->get( 'type' ) );

$optTopic	= array();
foreach( $topics as $topic )
	$optTopic[$topic->topicId]	= $topic->title;
$optTopic	= UI_HTML_Elements::Options( $optTopic, (int) $topic->topicId );


if( !in_array( 'addThread', $rights ) )
	return '';
return '
<h4>'.$words['topic-add']['heading'].'</h4>
<form action="./info/forum/addThread" method="post">
	<div class="row-fluid">
		<div class="span12">
			<label for="input_title">'.$words['topic-add']['labelTitle'].'</label>
			<input type="text" name="title" id="input_title" class="span12" value="" required="required"/>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12">
			<label for="input_content">'.$words['topic-add']['labelContent'].'</label>
			<textarea name="content" id="input_content" class="span12" rows="8" required="required"></textarea>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12">
			<label for="input_topicId">'.$words['topic-add']['labelTopicId'].'</label>
			<select name="topicId" id="input_topicId" class="span12">'.$optTopic.'</select>
		</div>
<!--		<div class="span4">
			<label for="input_type">'.$words['topic-add']['labelType'].'</label>
			<select name="type" id="input_type" class="span12">'.$optType.'</select>
		</div>-->
	</div>
	<div class="buttonbar">
		<button type="submit" name="save" value="1" class="btn btn-success btn-small"><i class="icon-ok icon-white"></i> '.$words['topic-add']['buttonSave'].'</button>
	</div>
</form>';
?>