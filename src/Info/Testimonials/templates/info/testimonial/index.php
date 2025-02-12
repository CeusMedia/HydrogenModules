<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var View_Info_Testimonial $view */
/** @var array<object> $testimonials */

$list	= '<div class="muted"><em>Keine vorhanden.</em></div><br/>';
if( $testimonials ){
	$list	= [];
	foreach( $testimonials as $entry ){
		$author	= $entry->username.',&nbsp;'.date( 'd.m.Y', $entry->timestamp );
		$quote	= HtmlTag::create( 'blockquote', [
			HtmlTag::create( 'a', "&bdquo;".$entry->abstract."&ldquo;", [
				'class' 		=> 'testimonial-abstract',
				'href'			=> '#modal-comment-view',
				'role'			=> "button",
				'data-toggle'	=> "modal",
				'onclick'		=> 'showComment('.$entry->testimonialId.')'
			] ),
			HtmlTag::create( 'small', $author, ['class' => 'testimonial-author'] ),
		], ['class' => 'course-comment'] );
		$comment	= HtmlTag::create( 'div', $quote, ['class' => 'testimonial'] );
		$list[]	= HtmlTag::create( 'div', $comment, [
			'class'			=> 'row-fluid course-item',
			'id'			=> 'testimonial-'.$entry->testimonialId,
			'data-author'	=> addslashes( $entry->username ),
			'data-heading'	=> addslashes( $entry->title ),
			'data-content'	=> addslashes( nl2br( $entry->description ) ),
		] );
	}
	$list	= HtmlTag::create( 'div', $list, ['class' => 'testimonial-list'] );
}

extract( $view->populateTexts( ['top', 'bottom', 'list.top', 'list.bottom', 'form.top', 'form.bottom', 'form.info'], 'html/info/testimonial/' ) );

$iconSave	= HtmlTag::create( 'i', '', ['class' => 'icon-ok icon-white'] );

$button 	= HtmlTag::create( 'a', 'Kommentar abgeben', [
	'href'			=> '#modal-comment-add',
	'class'			=> 'btn',
	'onclick'		=> 'selectCourse(0);',
	'role'			=> "button",
	'data-toggle'	=> "modal",
] );

return $textTop.'
<!--<h3>Kundenmeinungen</h3>-->
	'.$textListTop.'
	'.$list.'
	'.$textListBottom.'
'.$textFormTop.'
<div class="row-fluid">
	<div class="span8">
		<div class="content-panel content-panel-form">
			<div class="content-panel-inner">
				<form action="./info/testimonial/addComment" method="post">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_title">Deine Meinung <small class="muted">(Überschrift)</small></label>
							<input type="text" name="title" id="input_title" class="span12" required="required"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_description">Ausfühlicher Kommentar</label>
							<textarea name="description" id="input_description" rows="6" class="span12" required="required"></textarea>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
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
					<div class="buttonbar">
						<button class="btn btn-primary" name="save" value="addComment">'.$iconSave.'&nbsp;absenden</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="span4">
		'.$textFormInfo.'
	</div>
</div>
'.$textFormBottom.'

<div id="modal-comment-view" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
			<div class="offset1 span10 myModalContent">
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<div class="row-fluid">
			<div class="offset1 span10">
				<button class="btn btn-small" data-dismiss="modal" aria-hidden="true">Schließen</button>
			</div>
		</div>
	</div>
</div>

<script>
function showComment(id){
	let item = $("#testimonial-"+id);
	let content = $("<div></div>");
//console.log(item.data());
	content.append($("<h4></h4>").append(item.data("heading")));
	content.append($("<div></div>").html(item.data("content")));
	$("#modal-comment-view .myModalLabel").html("Kundenmeinung von "+item.data("author"));
	$("#modal-comment-view .myModalContent").html(content);
}
</script>
<style>
#layout-content .modal .modal-body h4{
	color: #0C533C;
	}
div.testimonial {
	}
div.testimonial blockquote {
	}
div.testimonial p {
	font-style: italic;
	}
div.testimonial small {
	color: rgba(12, 83, 60, 0.75);0
	}
div.testimonial-list div.course-details {
	padding-bottom: 1.5em;
	}
div.testimonial-list div.course-details a {
	font-size: 0.9em;
	font-weight: normal !important;
	}
div.modal div.modal-header .myModalLabel {
	color: #444 !important;
	}
</style>'.$textBottom;
