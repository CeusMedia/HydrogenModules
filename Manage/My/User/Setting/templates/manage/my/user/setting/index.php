<?php

$helper	= new View_Helper_MyUserConfig( $env );
$table	= $helper->renderTable( $settings, 60, 40 );

if( !$table )
	return '';
return '
<style>
tr.changed {
	background-color: #EFEFEF; 
	}
legend.config {
	background-image: url(http://img.int1a.net/famfamfam/silk/wrench.png);
	}
table td {
	vertical-align: text-bottom;
	}
table td.label,
table td.input {
	border-top: 1px solid #DFDFDF;
	}
table td.label {
	font-size: 0.9em;
	}
table td.input {
/*	text-align: right;
*/	font-size: 1em;
	}
table span.button-reset {
	float: right;
	}
span.suffix {
	margin-left: 0.5em;
	}
</style>
<div class="column-left-70">
	<fieldset>
		<legend class="icon config">Einstellungen</legend>
		<form name="" action="./manage/my/user/setting/update" method="post">
			'.$table.'
			<div class="buttonbar">
				'.UI_HTML_Elements::Button( 'save', 'speichern', 'button save' ).'
			</div>
		</form>
	</fieldset>
</div>';
?>