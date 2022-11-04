<?php

$panelEdit	= '
<div class="content-panel">
	<h3>Update User</h3>
	<div class="content-panel-inner">
		<form action="./manage/my/mangopay/user/edit" method="post">
			<div class="row-fluid">
				<div class="span2">
					<label for="input_firstname">Firstname</label>
					<input type="text" name="firstname" id="input_firstname" class="span12" value="'.htmlentities( $user->FirstName, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span3">
					<label for="input_lastname">Lastname</label>
					<input type="text" name="lastname" id="input_lastname" class="span12" value="'.htmlentities( $user->LastName, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span3">
					<label for="input_birthday">Birthday</label>
					<input type="date" name="birthday" id="input_birthday" class="span12" value="'.date( 'Y-m-d', $user->Birthday ).'"/>
				</div>
				<div class="span4">
					<label for="input_email">E-Mail</label>
					<input type="email" name="email" id="input_email" class="span12" value="'.htmlentities( $user->Email, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<h4>Address</h4>
			<div class="row-fluid">
				<div class="span3">
					<label for="input_postalCode">Country</label>
					<input type="text" name="country" id="input_country" class="span12" value="'.htmlentities( $user->Address->Country, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span3">
					<label for="input_region">Region</label>
					<input type="text" name="region" id="input_region" class="span12" value="'.htmlentities( $user->Address->Region, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span2">
					<label for="input_postalCode">Postal Code</label>
					<input type="text" name="postalCode" id="input_postalCode" class="span12" value="'.htmlentities( $user->Address->PostalCode, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span4">
					<label for="input_city">City</label>
					<input type="text" name="city" id="input_city" class="span12" value="'.htmlentities( $user->Address->City, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					<label for="input_addressLine1">Address Line 1</label>
					<input type="text" name="addressLine1" id="input_addressLine1" class="span12" value="'.htmlentities( $user->Address->AddressLine1, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span6">
					<label for="input_addressLine2">Address Line 2</label>
					<input type="text" name="addressLine2" id="input_addressLine2" class="span12" value="'.htmlentities( $user->Address->AddressLine2, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./manage/my/mangopay/user" class="btn"><i class="fa fa-fw fa-arrow-left"></i> zurück</a>
				<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-fw fa-check"></i> speichern</button>
			</div>
		</form>
	</div>
</div>';

return $panelEdit;
