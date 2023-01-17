<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w			= (object) $words['index-filter'];

$iconFilter	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-search'] );
$iconReset	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-search-minus'] );

$buttonFilter	= HtmlTag::create( 'button', $iconFilter.'&nbsp;'.$w->buttonFilter, [
	'type'	=> 'submit',
	'name'	=> 'filter',
	'class'	=> 'btn btn-info',
] );

$buttonReset	= HtmlTag::create( 'a', $iconReset.'&nbsp;'.$w->buttonReset, [
	'href'	=> './manage/relocation/filter/reset',
	'class'	=> 'btn btn-small btn-inverse',
] );

$optStatus	= HtmlElements::Options( $words['states'], $filterStatus );

$optOrderColumn	= [
	'title'			=> 'Titel',
	'views'			=> 'Klicks',
	'usedAt' 		=> 'Nutzung',
	'relocationId'	=> 'ID',
];
$optOrderColumn	= HtmlElements::Options( $optOrderColumn, $filterOrderColumn );

$optOrderDirection	= [
	'asc' 	=> 'aufsteigend',
	'desc'	=> 'absteigend',
];
$optOrderDirection	= HtmlElements::Options( $optOrderDirection, $filterOrderDirection );

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
					<div class="row-fluid">
						<div class="span6">
							<label for="input_orderColumn">'.$w->labelOrderColumn.'</label>
							<select name="orderColumn" id="input_orderColumn" class="span12">'.$optOrderColumn.'</select>
						</div>
						<div class="span6">
							<label for="input_orderDirection">'.$w->labelOrderDirection.'</label>
							<select name="orderDirection" id="input_orderDirection" class="span12">'.$optOrderDirection.'</select>
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
