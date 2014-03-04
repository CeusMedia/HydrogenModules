<?php

$w		= (object) $words['edit'];

$iconAdd	= '<i class="icon-plus icon-white"></i>';
$iconRemove	= '<i class="icon-remove icon-white"></i>';

//  --  TAG MANAGEMENT  --  //
$listTags	= "";
if( $note->tags ){
	$list	= array();
	foreach( $note->tags as $tag ){
		$url	= './work/note/removeTag/'.$note->noteId.'/'.$tag->tagId;
		$label	= '<div class="item-tag-label">'.$tag->content.'</div>';
//		$count	= '<div class="number-indicator">'.$tag->relevance.'</div>';
		$button	= '<a href="'.$url.'" class="btn btn-small btn-mini btn-danger" title="entfernen">'.$iconRemove.'</a>';
		$tray	= '<div class="item-tag-tray">'.$button.'</div>';
		$list[]	= '<li class="item-tag-extended border-bottom">'.$label.$tray.'</li>';
	}
	$listTags	= '<ul class="tags-list">'.join( $list ).'</ul>';
}

$listRelatedTags	= "";
if( $relatedTags ){
	$list	= array();
	$relatedTags	= array_slice( $relatedTags, 0, 5 );
	foreach( $relatedTags as $tag ){
		$url	= './work/note/addTag/'.$note->noteId.'/'.$tag->tagId;
		$label	= '<div class="item-tag-label">'.$tag->content.'</div>';
		$count	= '<div class="number-indicator">'.$tag->relevance.'</div>';
		$button	= '<a href="'.$url.'" class="btn btn-small btn-success" title="zuweisen">'.$iconAdd.'</a>';
		$tray	= '<div class="item-tag-tray">'.$count.$button.'</div>';
		$list[]	= '<li class="item-tag-extended border-top">'.$label.$tray.'</li>';
	}
	$listRelatedTags	= '<br/><label>Vorschläge</label><ul class="tags-list">'.join( $list ).'</ul>';
}
$panelTags	= '
<div class="content-panel content-panel-form">
	<h3>Tags</h3>
	<div class="content-panel-inner">
		'.$listTags.'
		<form action="./work/note/addTag/'.$note->noteId.'" method="post">
			<div class="row-fluid">
				<div class="span6">
					<input type="text" name="tag_content" id="input_tag_content" class="span12">
				</div>
				<div class="span3">
					<button type="submit" name="save" class="btn not-btn-small btn-success"><i class="icon-ok icon-white"></i><!-- '.$w->buttonAddTag.'--></button>
				</div>
			</div>
		</form>
		'.$listRelatedTags.'
	</div>
</div>';

//  --  LINK MANAGEMENT  --  //
$listLinks	= '';
if( $note->links ){
	$list	= array();
	foreach( $note->links as $link ){
		$url	= './work/note/removeLink/'.$note->noteId.'/'.$link->noteLinkId;
		$link	= '<a href="'.$link->url.'">'.htmlentities( $link->title, ENT_QUOTES ).'</a>';
		$label	= '<div class="item-link-label">'.$link.'</div>';
//		$count	= '<div class="number-indicator">'.$tag->relevance.'</div>';
		$button	= '<a href="'.$url.'" class="btn btn-small btn-mini btn-danger" title="entfernen">'.$iconRemove.'</a>';
		$tray	= '<div class="item-link-tray">'.$button.'</div>';
		$list[]	= '<li class="item-link-extended border-bottom">'.$label.$tray.'</li>';
	}
	$listLinks	= '<ul class="links-list">'.join( $list ).'</ul>';

#	{
#	$url	= './work/note/removeLink/'.$note->noteId.'/'.$link->linkId;
#	$label	= '<span class="link untitled">'.$link->url.'</span>';
#	if( $link->title )
#		$label	= '<span class="link titled"><acronym title="'.$link->url.'">'.$link->title.'</acronym></span>';
#	$list[]	= '<li class="link action-remove" onclick="document.location=\''.$url.'\';">'.$label.'</li>';
#	}
#	$listLinks	= '<ul class="links-list">'.join( $list ).'</ul>';
}
$panelLinks	= '
<div class="content-panel content-panel-form">
	<h3>Links</h3>
	<div class="content-panel-inner">
		<form action="./work/note/addLink/'.$note->noteId.'" method="post">
			'.$listLinks.'
			<div class="row-fluid">
				<div class="span12">
					<label for="input_link_url" class="mandatory">Link-Adresse</label>
					<input type="text" name="link_url" id="input_link_url" class="span12 mandatory"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_link_title">Link-Titel</label>
					<input type="text" name="link_title" id="input_link_title" class="span12"/>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn not-btn-small btn-success">'.$iconAdd.' '.$w->buttonAddLink.'</button>
			</div>
		</div>
	</form>
</div>';

$optProject	= array( '' => '- keine Zuweisung -' );
foreach( $projects as $project )
	$optProject[$project->projectId]	= $project->title;
$optProject	= UI_HTML_Elements::Options( $optProject, $note->projectId );

$optFormat	= array();
foreach( $words['formats'] as $formatKey => $formatLabel )
	$optFormat[$formatKey]	= $formatLabel;
$optFormat  = UI_HTML_Elements::Options( $optFormat, $note->format );

$panelEdit	= '
<div class="content-panel content-panel-form">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form id="form_edit_note" name="edit_note" action="./work/note/edit/'.$note->noteId.'" method="post">
			<div class="row-fluid">
				<div class="span6">
					<label for="input_note_title" class="mandatory">'.$w->labelTitle.'</label>
					<input type="text" name="note_title" id="input_note_title" class="span12 mandatory" value="'.htmlentities( $note->title, ENT_COMPAT, 'UTF-8' ).'"/>
				</div>
				<div class="span3">
					<label for="input_note_projectId">'.$w->labelProjectId.'</label>
					<select id="input_note_projectId" name="note_projectId" class="span12">'.$optProject.'</select>
				</div>
				<div class="span3">
					<br/>
					<label for="input_note_public">
						<input type="checkbox" id="input_note_public" name="note_public" value="1" '.( $note->public ? 'checked="checked"' : '' ).'/>
						&nbsp;'.$w->labelPublic.'
					</label>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span3">
					<label for="input_note_format">'.$w->labelFormat.'</label>
					<select id="input_note_format" name="note_format" class="span12">'.$optFormat.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_note_content">'.$w->labelContent.'</label>
					<textarea name="note_content" id="input_note_content" rows="12" class="span12 CodeMirror-auto">'.htmlentities( $note->content, ENT_COMPAT, 'UTF-8' ).'</textarea>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./work/note" class="btn not-btn-small"><i class="icon-arrow-left"></i> '.$w->buttonCancel.'</a>
				<a href="./work/note/view/'.$note->noteId.'" class="btn not-btn-small not-btn-info"><i class="icon-eye-open not-icon-white"></i> '.$w->buttonView.'</a>
				<button type="submit" name="save" class="btn not-btn-small btn-success"><i class="icon-ok icon-success icon-white"></i> '.$w->buttonSave.'</button>
				<a href="./work/note/remove/'.$note->noteId.'" class="btn not-btn-small btn-danger" onclick="if(!confirm(\''.$w->buttonRemoveConfirm.'\'))return false;"><i class="icon-remove icon-white"></i> '.$w->buttonRemove.'</a>
			</div>
		</form>
	</div>
</div>';

extract( $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/work/note/edit.' ) );

return $textTop.'
<div class="row-fluid">
	<div class="span8">
		'.$panelEdit.'
	</div>
	<div class="span4">
		'.$panelTags.'
		'.$panelLinks.'
	</div>
</div>
'.$textBottom;
?>
