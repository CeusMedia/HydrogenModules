<?php
$w	= (object) $words['edit-tags'];

$iconSave	= HTML::Icon( 'ok', TRUE );
$iconRemove	= HTML::Icon( 'trash', TRUE );

$list		= '<div><em><small class="muted">(Noch nicht implementiert.)</small></em></div>';
if( $branch->tags ){
	$list	= array();
	foreach( $branch->tags as $tag ){
		$label	= $tag->label;
		$button	= UI_HTML_Tag::create( 'a', $iconRemove, array(
			'href'	=> './manage/company/branch/removeTag/'.$tag->branchTagId,
			'class'	=> 'btn btn-mini not-btn-inverse btn-danger'
		) );
		$button 	= UI_HTML_Tag::create( 'span', $button, array( 'class' => 'pull-right' ) );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $tag->label, array( 'class' => 'cell-tag-label' ) ),
			UI_HTML_Tag::create( 'td', $button, array( 'class' => 'cell-tag-remove' ) ),
		) );
	}
	$list	= UI_HTML_Tag::create( 'table', $list, array( 'class' => 'table table-striped not-table-condensed' ) );
}

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$list.'
		<hr/>
		<form action="./manage/company/branch/addTag/'.$branch->branchId.'" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_tags">'.$w->labelTags.' <small class="muted">'.$w->labelTags_suffix.'</small></label>
					<input type="text" name="tags" id="input_tags"/>
				</div>
			</div>
			<div class="buttonbar">
				<div class="btn-toolbar">
					<button type="submit" name="save" class="btn btn-primary btn-small">'.$iconSave.'&nbsp;'.$w->buttonSave.'</button>
				</div>
			</div>
		</form>
	</div>
</div>';
?>
