<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

/** @var WebEnvironment $env */
/** @var array $words */
/** @var View $view */
/** @var object $project */
/** @var array $projects */

$w		= (object) $words['filter'];

$term	= $env->getSession()->get( 'filter_notes_term' );
$tags	= $env->getSession()->get( 'filter_notes_tags' );
if( !is_array( $tags ) )
	$tags	= [];

$iconAdd	= '<i class="icon-plus icon-white"></i>';
$iconRemove	= '<i class="icon-remove icon-white"></i>';

$optOrder	= ['' => '-', 'modifiedAt' => 'letzte Änderung', 'createdAt' => 'Erstellungsdatum'];
$optOrder	= HtmlElements::Options( $optOrder, $filterOrder );

/*
$not	= [];
foreach( $tags as $tag )
	$not[]	= $tag->tagId;

$tags	= $logicNote->getTopTags( 3, 0, $filterProjectId, $not );

$tagsMore	= "";
if( $tags ){
	$list	= [];
	foreach( $tags as $tag ){
		$url	= './work/note/addSearchTag/'.$tag->tagId;
		$label	= '<div class="item-tag-label">'.$tag->content.'</div>';
		$count	= '<span class="badge badge-info"><small>'.$tag->relations.'</small></span> ';
		$button	= '<a href="'.$url.'" class="btn btn-small btn-mini btn-success">'.$iconAdd.'</a>';
		$tray	= '<div class="item-tag-tray">'.$count.$button.'</div>';
		$list[]	= '<li class="item-tag-extended border-top">'.$label.$tray.'</li>';
	}
	$tagsMore	= '<ul class="tags-list">'.join( $list ).'</ul>';
	$tagsMore	= '<h4>'.$w->labelTagsSuggested.'</h4>'.$tagsMore;
}

$tags	= $session->get( 'filter_notes_tags' );
$tagsSearch	= "";
if( $tags ){
	$list	= [];
	foreach( $tags as $tag ){
		$url	= './work/note/forgetTag/'.$tag->tagId;
		$label	= '<div class="item-tag-label">'.$tag->content.'</div>';
//		$count	= '<div class="number-indicator">'.$tag->relevance.'</div>';
		$button	= '<a href="'.$url.'" class="btn btn-small btn-mini btn-danger">'.$iconRemove.'</a>';
		$tray	= '<div class="item-tag-tray">'.$button.'</div>';
		$list[]	= '<li class="item-tag-extended border-top">'.$label.$tray.'</li>';
	}
	$tagsSearch	= '<ul class="tags-list">'.join( $list ).'</ul>';
	$tagsSearch	= '<h4>'.$w->labelTagsActive.'</h4>'.$label.$tagsSearch;
}
*/
$optAuthor		= $words['filter-author'];
$optAuthor		= HtmlElements::Options( $optAuthor, $filterAuthor );

$optPublic		= $words['filter-public'];
$optPublic		= HtmlElements::Options( $optPublic, $filterPublic );

$optProject		= ['' => '- alle -', '0' => '- ohne Projektbezug -'];
foreach( $projects as $project )
	$optProject[$project->projectId]	= $project->title;
$optProject		= HtmlElements::Options( $optProject, $filterProjectId );

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
					<input type="text" id="input_filter_query" tabindex="1" name="filter_query" value="'.$term.'" class="span12" autocomplete="off" onchange="this.form.submit();"/>
					<div style="clear: left"></div>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_filter_projectId">'.$w->labelProjectId.'</label>
					<select id="input_filter_projectId" name="filter_projectId" class="span12" onchange="this.form.submit();">'.$optProject.'</select>
				</div>
			</div>
			<div class="row-fluid">
<!--				<div class="span6">
					<label for="input_filter_public">'.$w->labelPublic.'</label>
					<select id="input_filter_public" name="filter_public" class="span12" onchange="this.form.submit();">'.$optPublic.'</select>
				</div>-->
				<div class="span6">
					<label for="input_filter_author">'.$w->labelAuthor.'</label>
					<select id="input_filter_author" name="filter_author" class="span12" onchange="this.form.submit();">'.$optAuthor.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span3">
					<label for="input_filter_limit">'.$w->labelLimit.'</label>
					<input type="text" id="input_filter_limit" name="filter_limit" class="span12" onchange="this.form.submit();" value="'.$filterLimit.'"/>
				</div>
				<div class="span9">
					<label for="input_filter_order">'.$w->labelOrder.'</label>
					<select id="input_filter_order" name="filter_order" class="span12" onchange="this.form.submit();">'.$optOrder.'</select>
				</div>
			</div>
		</form>
		'./*$tagsSearch.*/'
		'./*$tagsMore.*/'
	</div>
</div>';
