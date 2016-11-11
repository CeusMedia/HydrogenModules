<?php
$optType	= array( 'CSV' => 'CSV' );
$optType	= UI_HTML_Elements::Options( $optType, key( $optType ) );

$optStatus	= array(
	-1	=> 'ungültig',
	2	=> 'gültig',
);
$optStatus	= UI_HTML_Elements::Options( $optStatus, array( -1, 2 ) );

$optGroup	= array();
foreach( $groups as $group )
	$optGroup[$group->mailGroupId]	= $group->title;
$optGroup	= UI_HTML_Elements::Options( $optGroup, $groups ? $groups[0]->mailGroupId : NULL );

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
						<label>Exportiere nur</label>
						<div class="span12">
							<label for="input_status_1" class="radio">
								<input type="radio" name="status" id="input_status_1" value="2" checked="checked"/>&nbsp;gültig
							</label>
							<label for="input_status_0" class="radio">
								<input type="radio" name="status" id="input_status_0" value="-1"/>&nbsp;ungültig
							</label>
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
