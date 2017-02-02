<?php
$optStatus	= $words['states-thread'];
$optStatus	= UI_HTML_Elements::Options( $optStatus, (int) $request->get( 'status' ) );

$optType	= $words['types'];
$optType	= UI_HTML_Elements::Options( $optType, (int) $request->get( 'type' ) );

$optTopic	= array();
foreach( $topics as $entry )
	$optTopic[$entry->topicId]	= $entry->title;
$optTopic	= UI_HTML_Elements::Options( $optTopic, (int) $entry->topicId );


if( !in_array( 'addThread', $rights ) )
	return '';
return '
<div class="content-panel">
	<h4>'.sprintf( $words['topic-add']['heading'], $topic->title ).'</h4>
	<div class="content-panel-inner">
		<form action="./info/forum/addThread" method="post">
			<input type="hidden" name="topicId" value="'.$topic->topicId.'">
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
			<div class="buttonbar">
				<button type="submit" name="save" value="1" class="btn btn-success btn-small"><i class="icon-ok icon-white"></i> '.$words['topic-add']['buttonSave'].'</button>
			</div>
		</form>
	</div>
</div>';
?>
