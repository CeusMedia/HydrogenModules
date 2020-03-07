<?php

$hostMap	= array();
foreach( $hosts as $host )
	$hostMap[$host->mailSyncHostId]	= $host->host ? $host->host : $host->ip;

$optHost1	= UI_HTML_Elements::Options( $hostMap );
$optHost2	= UI_HTML_Elements::Options( $hostMap );

return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h3>Add Sync</h3>
			<div class="content-panel-inner">
				<form action="./work/mail/sync/addSync" method="post">
					<div class="row-fluid">
						<div class="span4">
							<label for="input_sourceUsername">Benutzername</label>
							<input type="text" name="sourceUsername" id="input_sourceUsername" class="span12"/>
						</div>
						<div class="span2">
							<br/>
							<label><input type="checkbox" name="sameUsername" id="input_sameUsername" checked="checked"/> gleich</label>
						</div>
						<div class="span4">
							<label for="input_targetUsername">Benutzername</label>
							<input type="text" name="targetUsername" id="input_targetUsername" readonly="readonly" class="span12"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span4">
							<label for="input_sourcePassword">Passwort</label>
							<input type="text" name="sourcePassword" id="input_sourcePassword" class="span12"/>
						</div>
						<div class="span2">
							<br/>
							<label><input type="checkbox" name="samePassword" id="input_samePassword" checked="checked"/> gleich</label>
						</div>
						<div class="span4">
							<label for="input_targetPassword">Benutzername</label>
							<input type="text" name="targetPassword" id="input_targetPassword" readonly="readonly" class="span12"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span4">
							<label for="input_sourceMailHostId">Quell-Host</label>
							<select name="sourceMailHostId" id="input_sourceMailHostId" class="span12">'.$optHost1.'</select>
						</div>
						<div class="span2">
						</div>
						<div class="span4">
							<label for="input_targetMailHostId">Ziel-Host</label>
							<select name="targetMailHostId" id="input_targetMailHostId" class="span12">'.$optHost2.'</select>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label class="checkbox"><input type="checkbox" name="resync" value="1" checked="checked"/>&nbsp;automatisch synchron halten</label>
						</div>
					</div>
					<div class="buttonbar">
						<a href="./work/mail/sync" class="btn"><i class="fa fa-fw fa-arrow-left"></i>&nbsp;cancel</a>
						<button type="submit" name="save" class="btn btn-primary" id="button_save"><i class="fa fa-fw fa-check"></i>&nbsp;save</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>';
