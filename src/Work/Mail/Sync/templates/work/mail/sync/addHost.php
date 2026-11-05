<?php

/** @var array<string,array<string,string>> $words */

$w	= (object) $words['addHost'];

return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				<form action="work/mail/sync/addHost" method="post">
					<div class="row-fluid">
						<div class="span3">
							<label for="input_host"><small class="muted">'.$w->labelHost_prefix.'</small> '.$w->labelHost.'</label>
							<input type="text" name="host" id="input_host" class="span12"/>
						</div>
						<div class="span2">
							<label for="ip"><small class="muted">'.$w->labelIp_prefix.'</small> '.$w->labelIp.'</label>
							<input type="text" name="ip" id="input_ip" class="span12"/>
						</div>
						<div class="span1">
							<label>'.$w->labelSsl.'</label>
							<input type="checkbox" name="ssl" id="input_ssl" checked="checked"/>
						</div>
						<div class="span1">
							<label for="port">'.$w->labelPort.'</label>
							<input type="text" name="port" id="input_port" class="span12" value="993"/>
						</div>
						<div class="span2">
							<label for="port">'.$w->labelAuth.'</label>
							<select name="auth" class="span12" value="1">
								<option value="0">PLAIN</option>
								<option value="1" selected="selected">LOGIN</option>
								<option value="2">CRAM-MD5</option>
							</select>
						</div>
					</div>
					<div class="buttonbar">
						<a href="./work/mail/sync" class="btn"><i class="fa fa-fw fa-arrow-left"></i>&nbsp;'.$w->buttonCancel.'</a>
						<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-fw fa-check"></i>&nbsp;'.$w->buttonSave.'</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>';
