<?php
$wLogin		= (object) $words['customer-login'];
$wRegister	= (object) $words['customer-register'];
return '<div class="row-fluid">
	<div class="span4 offset1">
		<div class="content-panel">
			<h3>'.$wLogin->heading.'</h3>
			<div class="content-panel-inner">
				<p>'.$wLogin->textTop.'</p>
				<form action="./shop/login" method="post">
					<label for="input_login">'.$wLogin->labelLogin.'</label>
					<input type="text" name="login" id="input_login" class="span10" value="'.htmlentities( $login, ENT_QUOTES, 'UTF-8' ).'"/>
					<label for="input_password">'.$wLogin->labelPassword.'</label>
					<input type="password" name="password" id="input_password" class="span10"/>
					<div class="buttonbar">
						<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-fw fa-sign-in"></i> '.$wLogin->buttonLogin.'</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="span4 offset1">
		<div class="content-panel">
			<h3>'.$wRegister->heading.'</h3>
			<div class="content-panel-inner">
				<p>'.$wRegister->textTop.'</p>
						<a href="./auth/register?from=shop/customer" class="btn btn-primary"><i class="fa fa-fw fa-pencil"></i> '.$wRegister->buttonRegister.'</a>
				</form>
			</div>
		</div>
	</div>
</div>';

?>
