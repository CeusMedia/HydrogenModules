<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var array<string,array<string,string>> $words */
/** @var object $customer */
/** @var int|string $customerId */

$heads	= '';

$optSize	= $words['sizes'];
$optSize	= HtmlElements::Options( $optSize, $customer->size );

$optType	= $words['types'];
$optType	= HtmlElements::Options( $optType, $customer->type );

$optCountry	= $words['countries'];
$optCountry	= HtmlElements::Options( $optCountry, $customer->country );

$w	= (object) $words['edit'];

/*$view->registerTab( '', 'Daten' );
if( $useMap )
	$view->registerTab( 'map/'.$customerId, 'Karte' );
if( $useRatings )
	$view->registerTab( 'rating/'.$customerId, 'Bewertung' );
if( $useProjects )
	$view->registerTab( 'project/'.$customerId, 'Projekte' );
*/

$tabs		= View_Manage_Customer::renderTabs( $env, $customerId );

return '
<h3><span class="muted">Kunde</span> '.$customer->title.'</h3>
'.$tabs.'
<div class="content-panel">
	<div class="content-panel-inner">
		<form action="./manage/customer/edit/'.$customerId.'" method="post">
			<div class="row-fluid">
				<div class="span6">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_title">'.$w->labelTitle.'</label>
							<input type="text" id="input_title" name="title" class="span12" value="'.htmlentities( $customer->title, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_url">'.$w->labelUrl.'</label>
							<input type="text" id="input_url" name="url" class="span12" value="'.htmlentities( $customer->url, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span6">
							<label for="input_type">'.$w->labelType.'</label>
							<select id="input_type" name="type" class="span12">'.$optType.'</select>
						</div>
						<div class="span6">
							<label for="input_size">'.$w->labelSize.'</label>
							<select id="input_size" name="size" class="span12">'.$optSize.'</select>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_description">'.$w->labelDescription.'</label>
							<textarea id="input_description" name="description" class="span12 CodeMirror-auto" rows="6">'.htmlentities( $customer->description, ENT_QUOTES, 'UTF-8' ).'</textarea>
						</div>
					</div>
				</div>
				<div class="span6">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_contact">'.$w->labelContact.'</label>
							<input type="text" id="input_contact" name="contact" class="span12" value="'.htmlentities( $customer->contact, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_email">'.$w->labelEmail.'</label>
							<input type="text" id="input_email" name="email" class="span12" value="'.htmlentities( $customer->email, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span6">
							<label for="input_phone">'.$w->labelPhone.'</label>
							<input type="text" id="input_phone" name="phone" class="span12" value="'.htmlentities( $customer->phone, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
						<div class="span6">
							<label for="input_fax">'.$w->labelFax.'</label>
							<input type="text" id="input_fax" name="fax" class="span12" value="'.htmlentities( $customer->fax, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span10">
							<label for="input_street">'.$w->labelStreet.'</label>
							<input type="text" id="input_street" name="street" class="span12" value="'.htmlentities( $customer->street, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
						<div class="span2">
							<label for="input_nr">'.$w->labelNr.'</label>
							<input type="text" id="input_nr" name="nr" class="span12" value="'.htmlentities( $customer->nr, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span9">
							<label for="input_city">'.$w->labelCity.'</label>
							<input type="text" id="input_city" name="city" class="span12" value="'.htmlentities( $customer->city, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
						<div class="span3">
							<label for="input_postcode">'.$w->labelPostcode.'</label>
							<input type="text" id="input_postcode" name="postcode" class="span12" value="'.htmlentities( $customer->postcode, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_country">'.$w->labelCountry.'</label>
							<select id="input_country" name="country" class="span12">'.$optCountry.'</select>
						</div>
					</div>
				</div>
			</div>
			<div class="buttonbar">
				<a class="btn not-btn-small" href="./manage/customer"><i class="icon-arrow-left"></i> '.$w->buttonCancel.'</a>
				<button type="submit" class="btn not-btn-small btn-success" name="save"><i class="icon-ok icon-white"></i> '.$w->buttonSave.'</button>
				<button type="button" class="btn not-btn-small btn-danger" onclick="document.location.href=\'./manage/customer/remove/'.$customer->customerId.'\';"><i class="icon-plus icon-white"></i> '.$w->buttonRemove.'</button>
			</div>
		</form>
	</div>
</div>';
