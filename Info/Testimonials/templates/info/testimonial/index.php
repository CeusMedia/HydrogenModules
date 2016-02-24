<?php

$list	= '<div class="muted"><em>Keine vorhanden.</em></div><br/>';
if( $testimonials ){
	$list	= array();
	foreach( $testimonials as $entry ){
		$author	= $comment->username.',&nbsp;'.date( 'd.m.Y', $comment->timestamp );
		$comments[]	= UI_HTML_Tag::create( 'blockquote', array(
			UI_HTML_Tag::create( 'p', "&bdquo;".$comment->abstract."&ldquo;", array( 'class' => 'course-comment-abstract' ) ),
			UI_HTML_Tag::create( 'small', $author, array( 'class' => 'course-comment-author' ) ),
		), array( 'class' => 'pull-right course-comment' ) );
		$comments	= UI_HTML_Tag::create( 'div', $comments, array( 'class' => 'span4 course-comments' ) );
		$list[]	= UI_HTML_Tag::create( 'div', $comments, array( 'class' => 'row-fluid course-item' ) );
	}
	$list	= UI_HTML_Tag::create( 'div', $list, array( 'class' => 'course-list' ) );
}

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/info/course/' ) );

return $textTop.$list.$textBottom.'
<form action="./info/course/addComment" method="post">
	<div id="modal-comment-add" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-header">
			<br/>
			<div class="row-fluid">
				<div class="offset1 span10">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h3 class="myModalLabel">Dein Kommentar zu diesem Kurs</h3>
				</div>
			</div>
		</div>
		<div class="modal-body">
			<div class="row-fluid">
				<div class="offset1 span10">
					<label for="input_title">Deine Meinung</label>
					<input type="text" name="title" id="input_title" class="span12" required="required"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="offset1 span10">
					<label for="input_description">Ausfühlicher Kommentar <small class="muted">(optional)</small></label>
					<textarea name="description" id="input_description" rows="6" class="span12"></textarea>
				</div>
			</div>
			<div class="row-fluid">
				<div class="offset1 span10">
					<div class="row-fluid">
						<div class="span4">
							<label for="input_username">Dein Name</label>
							<input type="text" name="username" id="input_username" class="span12" required="required"/>
						</div>
						<div class="span8">
							<label for="input_email">E-Mail-Adresse <small class="muted">(optional, wird nicht angezeigt)</small></label>
							<input type="text" name="email" id="input_email" class="span12"/>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<div class="row-fluid">
				<div class="offset1 span10">
					<button class="btn btn-small" data-dismiss="modal" aria-hidden="true">Schließen</button>
					<button class="btn btn-primary" name="save" value="addComment">Abschicken</button>
				</div>
			</div>
		</div>
	</div>
</form>
<script>
function selectCourse(courseId){
	$("#modal-comment-add").find("#input_courseId").val(courseId);
}
</script>
<style>
div.course-comments {
	text-align: right;
	padding-right: 2em;
	font-size: 0.9em;
	}
div.course-comments blockquote {
	border-right: 4px solid rgba(255, 255, 255, 0.5);
	}
div.course-comments p {
	font-style: italic;
	}
div.course-comments small {
	color: rgba(12, 83, 60, 0.75);0
	}
div.course-list div.course-details {
	padding-bottom: 1.5em;
	}
div.course-list div.course-details a {
	font-size: 0.9em;
	font-weight: normal !important;
	}
div.modal div.modal-header .myModalLabel {
	color: #444 !important;
	}
</style>';
?>
