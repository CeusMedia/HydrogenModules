<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

$optStatus	= $words['states-thread'];
$optStatus	= HtmlElements::Options( $optStatus, (int) $request->get( 'status' ) );

$optType	= $words['types'];
$optType	= HtmlElements::Options( $optType, (int) $request->get( 'type' ) );

$optTopic	= [];
foreach( $topics as $entry )
	$optTopic[$entry->topicId]	= $entry->title;
$optTopic	= HtmlElements::Options( $optTopic, (int) $entry->topicId );


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
					<textarea name="content" id="input_content" class="span12 TinyMCE" data-tinymce-mode="minimal" rows="8"></textarea>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" value="1" class="btn btn-primary btn-small"><i class="fa fa-fw fa-check"></i> '.$words['topic-add']['buttonSave'].'</button>
			</div>
		</form>
	</div>
</div>';
