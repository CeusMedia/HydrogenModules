<?php
$optType	= array( 'CSV' => 'CSV' );
$optType	= UI_HTML_Elements::Options( $optType, key( $optType ) );

$optGroup	= array();
foreach( $groups as $group )
	$optGroup[$group->mailGroupId]	= $group->title;
$optGroup	= UI_HTML_Elements::Options( $optGroup, $groups ? $groups[0]->mailGroupId : NULL );

$statuses	= array(
	-2	=> 'nicht erreichbar',
	-1	=> 'abgelehnt',
	2	=> 'erreichbar',
);

$optStatus	= array();
foreach( $statuses as $key => $label )
	$optStatus[$key]	= $label;
$optStatus	= UI_HTML_Elements::Options( $optStatus, array( 2 ) );


$tabs	= View_Work_Mail_Check::renderTabs( $env, 'export' );

return $tabs.'
<div class="row-fluid">
	<div class="span6">
		<div class="content-panel">
			<h3>Export</h3>
			<div class="content-panel-inner">
				<form action="./work/mail/check/export" method="post" enctype="multipart/form-data">
					<div class="row-fluid">
						<div class="span8">
							<label for="input_groupId">Gruppe</label>
							<select name="groupId" id="input_groupId" class="span12">'.$optGroup.'</select>
						</div>
						<div class="span4">
							<label for="input_type">Dateiformat</label>
							<select name="type" id="input_type" class="span12">'.$optType.'</select>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_status">Zustand</label>
							<select name="status[]" id="input_status" class="span12" multiple="multiple" size="3">'.$optStatus.'</select>
						</div>
					</div>
					<div class="buttonbar">
						<button type="submit" name="save" class="btn btn-primary"><i class="icon-download icon-white"></i>&nbsp;runterladen</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>';
?>
