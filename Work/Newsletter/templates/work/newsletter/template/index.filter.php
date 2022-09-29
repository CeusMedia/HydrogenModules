<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w		= (object) $words->index_filter;

$iconFilter	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-search'] ).'&nbsp;';
$iconReset	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-search-minus'] ).'&nbsp;';

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<p><small class="muted">Noch keine Filter vorhanden.</small></p>
		<div class="buttonbar">
			<div class="btn-group">
				<button type="submit" name="filter" class="btn btn-small btn-info" disabled="disabled">'.$iconFilter.$w->buttonFilter.'</button>
				<a href="./work/newsletter/group/filter/reset" class="btn btn-small btn-inverse" disabled="disabled">'.$iconReset.$w->buttonReset.'</a>
			</div>
		</div>
	</div>
</div>';
