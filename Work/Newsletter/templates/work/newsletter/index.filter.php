<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w		= (object) $words->index_filter;

$iconFilter	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-search'] ).'&nbsp;';
$iconReset	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-search-minus'] ).'&nbsp;';


$optStatus	= array_merge( ['' => '- egal -'], $words->states );
$optStatus	= HtmlElements::Options( $optStatus, $filterStatus );

$optLimit	= [5, 10, 15, 20, 25, 50, 100];
$optLimit	= array_combine( $optLimit, $optLimit );
$optLimit	= HtmlElements::Options( $optLimit, $filterLimit );

if( !$inlineFilter )
	return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./work/newsletter/filter" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_status">'.$w->labelStatus.'</label>
					<select name="status" id="input_status">'.$optStatus.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<div class="btn-group">
					<button type="submit" name="filter" class="btn btn-small btn-info" disabled="disabled">'.$iconFilter.$w->buttonFilter.'</button>
					<a href="./work/newsletter/group/filter/reset" class="btn btn-small btn-inverse" disabled="disabled">'.$iconReset.$w->buttonReset.'</a>
				</div>
			</div>
		</form>
	</div>
</div>';

if( isset( $newsletters ) && is_array( $newsletters ) && count( $newsletters ) < 3 )
	return "";

return '
<form action="./work/newsletter/filter" method="post">
	<div class="row-fluid">
		<div class="span2">
			<label for="input_title">Titel</label>
			<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $filterTitle, ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
		<div class="span2">
			<label for="input_status">Zustand</label>
			<select name="status" id="input_status" class="span12" onchange="this.form.submit()">'.$optStatus.'</select>
		</div>
		<div class="span2 offset3">
			<label for="input_limit">pro Seite</label>
			<select name="limit" id="input_limit" class="span6" onchange="this.form.submit()">'.$optLimit.'</select>
		</div>
		<div class="span3" data-style="text-align: right">
			<label>&nbsp;</label>
			<div class="btn-group">
				<button type="submit" name="filter" class="btn btn-small btn-info">'.$iconFilter.$w->buttonFilter.'</button>
				<a href="./work/newsletter/filter/reset" class="btn btn-small btn-inverse">'.$iconReset.$w->buttonReset.'</a>
			</div>
		</div>
	</div>
</form>
<hr/>';
