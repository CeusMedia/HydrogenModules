<?php

$w		= (object) $words['filter'];

$term	= $this->env->session->get( 'filter_notes_term' );
$tags	= $this->env->session->get( 'filter_notes_tags' );

$iconAdd	= '<img src="http://img.int1a.net/famfamfam/silk/magnifier_zoom_in.png" title="'.$w->buttonTagEnableAlt.'"/>';
$iconRemove	= '<img src="http://img.int1a.net/famfamfam/silk/magnifier_zoom_out.png" title="'.$w->buttonTagForgetAlt.'"/>';

$not	= array();
foreach( $tags as $tag )
	$not[]	= $tag->tagId;

$logic	= new Logic_Note( $this->env );
$tags	= $logic->getTopTags( 3, 0, $not );

$tagsMore	= "";
if( $tags ){
	$list	= array();
	foreach( $tags as $tag ){
		$url	= './work/note/addSearchTag/'.$tag->tagId;
		$label	= '<div class="item-tag-label">'.$tag->content.'</div>';
		$count	= '<div class="number-indicator">'.$tag->relations.'</div>';
		$button	= '<button type="button" class="button tiny remove" onclick="document.location=\''.$url.'\';">'.$iconAdd.'</button>';
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
		$button	= '<button type="button" class="button tiny remove" onclick="document.location=\''.$url.'\';">'.$iconRemove.'</button>';
		$tray	= '<div class="item-tag-tray">'.$button.'</div>';
		$list[]	= '<li class="item-tag-extended border-top">'.$label.$tray.'</li>';
	}
	$label		= '<label>'.$w->labelTagsActive.'</label><br/>';
	$tagsSearch	= '<ul class="tags-list">'.join( $list ).'</ul>';
	$tagsSearch	= '<li>'.$label.$tagsSearch.'</li>';
}
$buttonAdd		= UI_HTML_Elements::LinkButton( './work/note/add', 'neue Notiz', 'button icon add' );

$panelFilter	= '
<form id="form_note_filter" action="./work/note" method="post">
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
			'.$tagsSearch.'
			'.$tagsMore.'
		</ul>
	</fieldset>
</form>
'.$buttonAdd;
return $panelFilter;
?>