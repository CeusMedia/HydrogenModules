<?php

$w		= (object) $words['filter'];

$term	= $env->session->get( 'filter_notes_term' );
$tags	= $env->session->get( 'filter_notes_tags' );
if( !is_array( $tags ) )
	$tags	= array();

$iconAdd	= '<img src="http://img.int1a.net/famfamfam/silk/magnifier_zoom_in.png" title="'.$w->buttonTagEnableAlt.'"/>';
$iconRemove	= '<img src="http://img.int1a.net/famfamfam/silk/magnifier_zoom_out.png" title="'.$w->buttonTagForgetAlt.'"/>';
$iconAdd	= '<i class="icon-plus icon-white"></i>';
$iconRemove	= '<i class="icon-remove icon-white"></i>';

$optOrder	= UI_HTML_Elements::Options( array( 'touchedAt' => 'letzte Änderung' ) );

$not	= array();
foreach( $tags as $tag )
	$not[]	= $tag->tagId;

//$logic	= new Logic_Note( $env );
//$tags	= $logic->getTopTags( 3, 0, $not );
$tags	= array();

$tagsMore	= "";
if( $tags ){
	$list	= array();
	foreach( $tags as $tag ){
		$url	= './work/note/addSearchTag/'.$tag->tagId;
		$label	= '<div class="item-tag-label">'.$tag->content.'</div>';
		$count	= '<div class="number-indicator">'.$tag->relations.'</div>';
		$button	= '<a href="'.$url.'" class="btn btn-small btn-mini btn-success">'.$iconAdd.'</a>';
		$tray	= '<div class="item-tag-tray">'.$count.$button.'</div>';
		$list[]	= '<li class="item-tag-extended border-top">'.$label.$tray.'</li>';
	}
	$label		= '<label>'.$w->labelTagsSuggested.'</label><br/>';
	$tagsMore	= '<ul class="tags-list">'.join( $list ).'</ul>';
	$tagsMore	= '<li>'.$label.$tagsMore.'</li>';
}

$tags	= $session->get( 'filter_notes_tags' );
$tagsSearch	= "";
if( $tags ){
	$list	= array();
	foreach( $tags as $tag ){
		$url	= './work/note/forgetTag/'.$tag->tagId;
		$label	= '<div class="item-tag-label">'.$tag->content.'</div>';
//		$count	= '<div class="number-indicator">'.$tag->relevance.'</div>';
		$button	= '<a href="'.$url.'" class="btn btn-small btn-mini btn-danger">'.$iconRemove.'</a>';
		$tray	= '<div class="item-tag-tray">'.$button.'</div>';
		$list[]	= '<li class="item-tag-extended border-top">'.$label.$tray.'</li>';
	}
	$label		= '<label>'.$w->labelTagsActive.'</label><br/>';
	$tagsSearch	= '<ul class="tags-list">'.join( $list ).'</ul>';
	$tagsSearch	= '<li>'.$label.$tagsSearch.'</li>';
}
$buttonAdd		= UI_HTML_Tag::create( 'a', $iconAdd.' neue Notiz', array( 'href' => './work/note/add', 'class' => 'btn not-btn-small btn-primary' ) );

$optAuthor		= $words['filter-author'];
$optAuthor		= UI_HTML_Elements::Options( $optAuthor, $filterAuthor );

$optPublic		= $words['filter-public'];
$optPublic		= UI_HTML_Elements::Options( $optPublic, $filterPublic );

$optProject		= array( '' => '- alle -' );
foreach( $projects as $project )
	$optProject[$project->projectId]	= $project->title;
$optProject		= UI_HTML_Elements::Options( $optProject, $filterProjectId );

return '
<div class="content-panel content-panel-filter">
	<h3>'.$w->legend.'</h3>
	<div class="content-panel-inner">
		<form id="form_note_filter" action="./work/note/filter" method="post">
			<input type="hidden" name="offset" value="0"/>
			<div class="row-fluid">
				<div class="span12">
					<label>'.$w->labelQuery.'</label>
					<div style="position: relative; display: none;" id="reset-button-container">
						<img id="reset-button-trigger" src="themes/custom/img/clearSearch.png" style="position: absolute; right: 3%; top: 9px; cursor: pointer"/>
					</div>
					<input type="text" id="input_filter_query" tabindex="1" name="filter_query" value="'.$term.'" class="span12" autocomplete="off"/>
					<div style="clear: left"></div>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_filter_public">'.$w->labelPublic.'</label>
					<select id="input_filter_public" name="filter_public" class="span12" onchange="this.form.submit();">'.$optPublic.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_filter_author">'.$w->labelAuthor.'</label>
					<select id="input_filter_author" name="filter_author" class="span12" onchange="this.form.submit();">'.$optAuthor.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_filter_projectId">'.$w->labelProjectId.'</label>
					<select id="input_filter_projectId" name="filter_projectId" class="span12" onchange="this.form.submit();">'.$optProject.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span3">
					<label for="input_filter_limit">'.$w->labelLimit.'</label>
					<input type="text" id="input_filter_limit" name="filter_limit" class="span12" onchange="this.form.submit();" value="'.$limit.'"/>
				</div>
				<div class="span9">
					<label for="input_filter_order">'.$w->labelOrder.'</label>
					<select id="input_filter_order" name="filter_order" class="span12" onchange="this.form.submit();">'.$optOrder.'</select>
				</div>
			</div>
		</form>
		'.$tagsSearch.'
		'.$tagsMore.'
		<div class="buttonbar">
			'.$buttonAdd.'
		</div>
	</div>
</div>';
?>
