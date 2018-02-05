<?php
if( $show ){}

$panel	= '
<div class="row-fluid">
	<div class="span4 offset4">
		<br/>
		<br/>
		<br/>
		<form action="./work/accounting/invoice/share/'.$uuid.'" method="post">
			<div class="panel">
				<div class="panel-head">
					<h3>Sicherheitsabfrage</h3>
				</div>
				<div class="panel-body">
					<label for="input_captcha">Sicherheitscode <small><a href="./work/accounting/invoice/share/'.$uuid.'">Nicht lesbar? Neu laden</a></small></label>
					<div class="row-fluid">
						<div class="span12" style="border: 1px solid rgb(239, 239, 239); background-color: rgb(247, 247, 247); border-radius: 4px; text-align: center; margin: 4px 0;">
							<img src="data:image/jpeg;base64,'.base64_encode( $captchaImage ).'" title="CAPTCHA" class="img-captcha">
						</div>
					</div>
					<br/>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_captcha">Bitte hier eingeben</label>
							<input type="text" name="captcha" id="input_captcha" value="" class="span12"/>
						</div>
						<div style="display: none;">
							<input type="text" name="trap" value=""/>
						</div>
					</div>
				</div>
				<div class="panel-foot">
					<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-fw fa-check"></i> weiter</button>
				</div>
			</div>
		</form>
	</div>
</div>
<script>
var captchaLength = '.( (int) $captchaLength ).';
jQuery(document).ready(function(){
	if(captchaLength > 0){
		jQuery("#input_captcha").bind("keyup", function(){
			var button = jQuery(".panel-foot button");
			button.attr("disabled", "disabled");
			if(jQuery(this).val().length === captchaLength){
				button.removeAttr("disabled");
			}
		}).trigger("keyup");
	}
});
</script>';

return $panel;
