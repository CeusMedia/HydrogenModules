<?php

$w	= (object) $words['panel-search'];

$panelSearch	= '
<div class="content-panel">
	<h3>Search</h3>
	<div class="content-panel-inner">
		<form action="./info/event/filter" method="post">
			<input type="hidden" name="from" value="'.htmlentities( $from, ENT_QUOTES, 'UTF-8').'"/>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_query">'.$w->labelQuery.'<small class="muted">'.$w->labelQuery_suffix.'</small></label>
					<input type="text" name="query" id="input_query" class="span12" value="'.$query.'" placeholder="'.htmlentities( $w->labelQuery_placeholder, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span7">
					<label for="input_location">'.$w->labelLocation.'<small class="muted">'.$w->labelLocation_suffix.'</small></label>
					<input type="text" name="location" id="input_location" class="span12" value="'.$location.'" placeholder="'.htmlentities( $w->labelLocation_placeholder, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span5">
					<label for="input_range">'.$w->labelRange.'<small class="muted">'.$w->labelRange_suffix.'</small></label>
					<input type="text" name="range" id="input_range" class="span12" value="'.$range.'" placeholder="'.htmlentities( $w->labelRange_placeholder, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="buttonbar">
				<div class="btn-toolbar">
					<button type="submit" name="filter" class="btn btn-primary">
						<i class="icon-search icon-white"></i>&nbsp;suchen
					</button>
					<a href="./info/event/filter/reset" class="btn btn-small">
						<i class="icon-zoom-out"></i>&nbsp;zurücksetzen
					</a>
				</div>
			</div>
		</form>
	</div>
</div>
';

$iconFilter		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search' ) );
$iconReset		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

$panelSearch2	= '
<div class="content-panel">
	<div class="content-panel-inner">
		<form action="./info/event/filter" method="post">
			<input type="hidden" name="from" value="'.htmlentities( $from, ENT_QUOTES, 'UTF-8').'"/>
			<div class="row-fluid">
				<div class="span6">
					<label for="input_query">'.$w->labelQuery.'<small class="muted">'.$w->labelQuery_suffix.'</small></label>
					<input type="text" name="query" id="input_query" class="span12" value="'.$query.'" required="required" placeholder="'.htmlentities( $w->labelQuery_placeholder, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span2">
					<label for="input_location">'.$w->labelLocation.'<small class="muted">'.$w->labelLocation_suffix.'</small></label>
					<input type="text" name="location" id="input_location" class="span12" value="'.$location.'" required="required" placeholder="'.htmlentities( $w->labelLocation_placeholder, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span2">
					<label for="input_range">'.$w->labelRange.'<small class="muted">'.$w->labelRange_suffix.'</small></label>
					<input type="text" name="range" id="input_range" class="span12" value="'.$range.'" placeholder="'.htmlentities( $w->labelRange_placeholder, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span2">
					<div class="btn-toolbar">
						<button type="submit" name="filter" class="btn btn-primary btn-large" title="'.$w->buttonFilter_title.'">
							'.$iconFilter.' Suchen
						</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>';

return $panelSearch2.'
<script>
$(document).ready(function(){
	Module_Info_Event.initFilterLocationTypeahead();
});
</script>
';
