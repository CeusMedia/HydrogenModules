<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['edit-tags'];

$iconSave	= HTML::Icon( 'ok', TRUE );
$iconRemove	= HTML::Icon( 'trash', TRUE );

$list		= '<div><em><small class="muted">(Noch nicht implementiert.)</small></em></div>';
if( $branch->tags ){
	$list	= [];
	foreach( $branch->tags as $tag ){
		$label	= $tag->label;
		$button	= HtmlTag::create( 'a', $iconRemove, array(
			'href'	=> './manage/company/branch/removeTag/'.$tag->branchTagId,
			'class'	=> 'btn btn-mini not-btn-inverse btn-danger'
		) );
		$button 	= HtmlTag::create( 'span', $button, ['class' => 'pull-right'] );
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $tag->label, ['class' => 'cell-tag-label'] ),
			HtmlTag::create( 'td', $button, ['class' => 'cell-tag-remove'] ),
		) );
	}
	$list	= HtmlTag::create( 'table', $list, ['class' => 'table table-striped not-table-condensed'] );
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
