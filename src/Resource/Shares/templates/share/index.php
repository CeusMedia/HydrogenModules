<?php
//if( $show ){}

/** @var string $uuid */
/** @var string $captchaImage */
/** @var int $captchaLength */
/** @var array<string,array<string,string>> $words */

$w	= (object) $words['index'];

$panel	= '
<div class="row-fluid">
	<div class="span4 offset4">
		<br/>
		<br/>
		<br/>
		<form action="./share/'.$uuid.'" method="post">
			<div class="panel">
				<div class="panel-head">
					<h3>'.$w->heading.'</h3>
				</div>
				<div class="panel-body">
					<label for="input_captcha">'.$w->labelCaptcha.' <small><a href="./share/'.$uuid.'">'.$w->linkReload.'</a></small></label>
					<div class="row-fluid">
						<div class="span12" style="border: 1px solid rgb(239, 239, 239); background-color: rgb(247, 247, 247); border-radius: 4px; text-align: center; margin: 4px 0;">
							<img src="data:image/jpeg;base64,'.base64_encode( $captchaImage ).'" title="CAPTCHA" class="img-captcha">
						</div>
					</div>
					<br/>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_captcha">'.$w->labelInput.'</label>
							<input type="text" name="captcha" id="input_captcha" value="" class="span12"/>
						</div>
						<div style="display: none;">
							<input type="text" name="trap" value=""/>
						</div>
					</div>
				</div>
				<div class="panel-foot">
					<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-fw fa-check"></i> '.$w->buttonSave.'</button>
				</div>
			</div>
		</form>
	</div>
</div>
<script>
let captchaLength = '.( (int) $captchaLength ).';
jQuery(document).ready(function(){
	if(captchaLength > 0){
		jQuery("#input_captcha").on("keyup", function(){
			let button = jQuery(".panel-foot button");
			button.attr("disabled", "disabled");
			if(jQuery(this).val().length === captchaLength){
				button.removeAttr("disabled");
			}
		}).trigger("keyup");
	}
});
</script>';

return $panel;
