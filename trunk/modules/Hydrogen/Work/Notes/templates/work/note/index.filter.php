<?php

$w		= (object) $words['filter'];

$term	= $env->session->get( 'filter_notes_term' );
$tags	= $env->session->get( 'filter_notes_tags' );
if( !is_array( $tags ) )
	$tags	= array();

$iconAdd	= '<img src="http://img.int1a.net/famfamfam/silk/magnifier_zoom_in.png" title="'.$w->buttonTagEnableAlt.'"/>';
$iconRemove	= '<img src="http://img.int1a.net/famfamfam/silk/magnifier_zoom_out.png" title="'.$w->buttonTagForgetAlt.'"/>';
$iconAdd	= '<span></span>';
$iconRemove	= '<span></span>';


$not	= array();
foreach( $tags as $tag )
	$not[]	= $tag->tagId;

$logic	= new Logic_Note( $env );
$tags	= $logic->getTopTags( 3, 0, $not );

$tagsMore	= "";
if( $tags ){
	$list	= array();
	foreach( $tags as $tag ){
		$url	= './work/note/addSearchTag/'.$tag->tagId;
		$label	= '<div class="item-tag-label">'.$tag->content.'</div>';
		$count	= '<div class="number-indicator">'.$tag->relations.'</div>';
		$button	= '<button type="button" class="button tiny tag-add" onclick="document.location=\''.$url.'\';">'.$iconAdd.'</button>';
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
		$button	= '<button type="button" class="button tiny tag-remove" onclick="document.location=\''.$url.'\';">'.$iconRemove.'</button>';
		$tray	= '<div class="item-tag-tray">'.$button.'</div>';
		$list[]	= '<li class="item-tag-extended border-top">'.$label.$tray.'</li>';
	}
	$label		= '<label>'.$w->labelTagsActive.'</label><br/>';
	$tagsSearch	= '<ul class="tags-list">'.join( $list ).'</ul>';
	$tagsSearch	= '<li>'.$label.$tagsSearch.'</li>';
}
$buttonAdd		= UI_HTML_Elements::LinkButton( './work/note/add', 'neue Notiz', 'button icon add' );

$optAuthor		= $words['filter-author'];
$optAuthor		= UI_HTML_Elements::Options( $optAuthor, $filterAuthor );

$optPublic		= $words['filter-public'];
$optPublic		= UI_HTML_Elements::Options( $optPublic, $filterPublic );

$optProject		= array( '' => '- alle -' );
foreach( $projects as $project )
	$optProject[$project->projectId]	= $project->title;
$optProject		= UI_HTML_Elements::Options( $optProject, $filterProjectId );

$panelFilter	= '
<form id="form_note_filter" action="./work/note/filter" method="post">
	<input type="hidden" name="offset" value="0"/>
	<fieldset>
		<legend class="icon filter">'.$w->legend.'</legend>
		<ul class="input">
			<li>
				<label>'.$w->labelQuery.'</label><br/>
				<div style="position: relative; display: none;" id="reset-button-container">
					<img id="reset-button-trigger" src="themes/custom/img/clearSearch.png" style="position: absolute; right: 3%; top: 9px; cursor: pointer"/>
				</div>
				<input id="input_filter_query" tabindex="1" name="filter_query" value="'.$term.'" class="max" autocomplete="off"/>
				<div style="clear: left"></div>
			</li>
			<li>
				<label for="input_filter_public">'.$w->labelPublic.'</label><br/>
				<select id="input_filter_public" name="filter_public" class="max" onchange="this.form.submit();">'.$optPublic.'</select>
			</li>
			<li>
				<label for="input_filter_author">'.$w->labelAuthor.'</label><br/>
				<select id="input_filter_author" name="filter_author" class="max" onchange="this.form.submit();">'.$optAuthor.'</select>
			</li>
			<li>
				<label for="input_filter_projectId">'.$w->labelProjectId.'</label><br/>
				<select id="input_filter_projectId" name="filter_projectId" class="max" onchange="this.form.submit();">'.$optProject.'</select>
			</li>
			'.$tagsSearch.'
			'.$tagsMore.'
		</ul>
	</fieldset>
</form>
'.$buttonAdd;
return $panelFilter;
?>
