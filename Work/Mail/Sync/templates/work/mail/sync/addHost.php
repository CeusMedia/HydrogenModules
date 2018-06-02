<?php

return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h3>Add Host</h3>
			<div class="content-panel-inner">
				<form action="work/mail/sync/addHost" method="post">
					<div class="row-fluid">
						<div class="span3">
							<label for="input_host"><small class="muted">entweder</small> Hostname</label>
							<input type="text" name="host" id="input_host" class="span12"/>
						</div>
						<div class="span2">
							<label for="ip"><small class="muted">oder</small> IP-Adresse</label>
							<input type="text" name="ip" id="input_ip" class="span12"/>
						</div>
						<div class="span1">
							<label>SSL</label>
							<input type="checkbox" name="ssl" id="input_ssl" checked="checked"/>
						</div>
						<div class="span1">
							<label for="port">Port</label>
							<input type="text" name="port" id="input_port" class="span12" value="993"/>
						</div>
						<div class="span2">
							<label for="port">Authentification</label>
							<select name="auth" class="span12" value="1">
								<option value="0">PLAIN</option>
								<option value="1" selected="selected">LOGIN</option>
								<option value="2">CRAM-MD5</option>
							</select>
						</div>
					</div>
					<div class="buttonbar">
						<a href="./work/mail/sync" class="btn"><i class="fa fa-fw fa-arrow-left"></i>&nbsp;cancel</a>
						<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-fw fa-check"></i>&nbsp;save</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<script>
jQuery(document).ready(function(){
	jQuery("#input_ssl").on("change", function(){
		jQuery("#input_port").val(143);
		if(jQuery("#input_ssl").is(":checked"))
			jQuery("#input_port").val(993);
	});
});
</script>
';
