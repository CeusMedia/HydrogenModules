<?php
$w			= (object) $words['index-filter'];

$iconFilter	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-zoom-in icon-white' ) );
$iconReset	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-zoom-out' ) );

$buttonFilter	= UI_HTML_Tag::create( 'button', $iconFilter.'&nbsp;'.$w->buttonFilter, array(
	'type'	=> 'submit',
	'name'	=> 'filter',
	'class'	=> 'btn btn-primary',
) );

$buttonReset	= UI_HTML_Tag::create( 'a', $iconReset.'&nbsp;'.$w->buttonReset, array(
	'href'	=> './manage/relocation/filter/reset',
	'class'	=> 'btn btn-small',
) );

$optStatus	= UI_HTML_Elements::Options( $words['states'], $filterStatus );

return '
		<div class="content-panel">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				<form action="./manage/relocation/filter" method="post">
					<div class="row-fluid">
						<div class="span10">
							<label for="input_title">'.$w->labelTitle.'</label>
							<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $filterTitle, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
						<div class="span2">
							<label for="input_id">'.$w->labelId.'</label>
							<input type="text" name="id" id="input_id" class="span12" value="'.htmlentities( $filterId, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="">'.$w->labelStatus.'</label>
							<select name="status[]" id="input_status" multiple="multiple" class="span12">
								'.$optStatus.'
							</select>
						</div>
					</div>
					<div class="buttonbar">
						'.$buttonFilter.'
						'.$buttonReset.'
					</div>
				</form>
			</div>
		</div>
';
