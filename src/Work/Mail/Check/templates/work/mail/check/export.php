<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env */
/** @var object[] $groups */
/** @var array<string,array<string,string>> $words */

$w	= (object) $words['export'];

$optType	= ['CSV' => 'CSV'];
$optType	= HtmlElements::Options( $optType, key( $optType ) );

$optGroup	= [];
foreach( $groups as $group )
	$optGroup[$group->mailCheckGroupId]	= $group->title;
$optGroup	= HtmlElements::Options( $optGroup, $groups ? $groups[0]->mailCheckGroupId : NULL );

$statuses	= $words['export-statuses'];

$optStatus	= array_map( function ($label) {return $label;}, $statuses );
$optStatus	= HtmlElements::Options( $optStatus, [2] );
$tabs		= View_Work_Mail_Check::renderTabs( $env, 'export' );

return $tabs.'
<div class="row-fluid">
	<div class="span6">
		<div class="content-panel">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				<form action="./work/mail/check/export" method="post" enctype="multipart/form-data">
					<div class="row-fluid">
						<div class="span8">
							<label for="input_groupId">'.$w->labelGroup.'</label>
							<select name="groupId" id="input_groupId" class="span12">'.$optGroup.'</select>
						</div>
						<div class="span4">
							<label for="input_type">'.$w->labelType.'</label>
							<select name="type" id="input_type" class="span12">'.$optType.'</select>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_status">'.$w->labelStatus.'</label>
							<select name="status[]" id="input_status" class="span12" multiple="multiple" size="3">'.$optStatus.'</select>
						</div>
					</div>
					<div class="buttonbar">
						<button type="submit" name="save" class="btn btn-primary"><i class="icon-download icon-white"></i>&nbsp;'.$w->buttonDownload.'</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>';
