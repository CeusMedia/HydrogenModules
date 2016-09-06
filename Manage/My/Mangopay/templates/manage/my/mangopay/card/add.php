<?php

return '
<div class="content-panel">
	<h3>Register a new Credit Card</h3>
	<div class="content-panel-inner">
		<form action="'.$registration->CardRegistrationURL.'" method="post">
			<input type="hidden" name="data" value="'.$registration->PreregistrationData.'" />
			<input type="hidden" name="accessKeyRef" value="'.$registration->AccessKey.'" />
			<input type="hidden" name="returnURL" value="'.$returnUrl.'" />
			<div class="row-fluid">
				<div class="span12">
					<label for="cardNumber">Card Number</label>
					<input type="text" name="cardNumber" value="" />
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					<label for="cardExpirationDate">Expiration Date</label>
					<input type="text" name="cardExpirationDate" value="" />
				</div>
				<div class="span6">
					<label for="cardCvx">CVV</label>
					<input type="text" name="cardCvx" value="" />
				</div>
			</div>
			<div class="buttonbar">
				<a href="./manage/my/mangopay/card" class="btn btn-small"><b class="fa fa-arrow-left"></b> zurück</a>
				<button type="submit" name="save" value="register" class="btn btn-primary"><b class="fa fa-check"></b> registrieren</button>
			</div>
		</form>
	</div>
</div>';
?>
