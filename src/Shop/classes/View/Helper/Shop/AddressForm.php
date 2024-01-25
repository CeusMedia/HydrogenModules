<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Shop_AddressForm
{
	protected Environment $env;
	protected Model_Address $model;
	protected array $words;
	protected string $defaultCountryCode	= 'DE';
	protected ?object $address				= NULL;
	protected ?string $heading				= NULL;
	protected ?string $textTop				= NULL;
	protected $type							= 0;

	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->words	= $this->env->getLanguage()->getWords( 'address' );
	}

	public function render(): string
	{
		$addressId	= $this->address->addressId ?? 0;
		$w			= (object) $this->words['form'];
		$d			= $this->address;

		if( empty( $d->country ) || !array_key_exists( $d->country, $this->words['countries'] ) )
			$d->country	= $this->defaultCountryCode;

		$optCountry	= HtmlElements::Options( $this->words['countries'], $d->country );

		return '
<div class="content-panel">
	<h3>'.$this->heading.'</h3>
	<div class="content-panel-inner">
		'.$this->textTop.'
		<form action="./shop/customer/address/'.$addressId.'/'.$this->type.'" method="post">
			<!--	HACK: Force autocomplete to be off for newer Chrome versions.
					DESC: Chrome has new AutoFill feature whichs need autocomplete="false" which breaks other browsers.
					LINK: https://stackoverflow.com/a/33766566
					CODE: https://bugs.chromium.org/p/chromium/issues/detail?id=468153#hc41
		 	-->
			<div style="display: none;">
				<input type="text" id="PreventChromeAutocomplete" name="PreventChromeAutocomplete" autocomplete="address-level4" />
			</div>
			<div class="row-fluid">
				<div class="span5">
					<h4>'.$w->headingCustomerPersona.'</h4>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_institution">'.$w->labelInstitution.'</label>
							<input type="text" name="institution" id="input_institution" class="span12" value="'.htmlentities( $d->institution ?? '', ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span5">
							<label for="input_firstname" class="required mandatory">'.$w->labelFirstname.'</label>
							<input type="text" name="firstname" id="input_firstname" class="span12" required="required" value="'.htmlentities( $d->firstname ?? '', ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
						<div class="span7">
							<label for="input_lastname"" class="required mandatory">'.$w->labelLastname.'</label>
							<input type="text" name="surname" id="input_surname" class="span12" required="required" value="'.htmlentities( $d->surname ?? '', ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span8">
							<label for="input_email" class="required mandatory">'.$w->labelEmail.'</label>
							<input type="text" name="email" id="input_email" class="span12" required="required" value="'.htmlentities( $d->email ?? '', ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
						<div class="span4">
							<label for="input_phone">'.$w->labelPhone.'</label>
							<input type="text" name="phone" id="input_phone" class="span12" value="'.htmlentities( $d->phone ?? '', ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
				</div>
				<div class="span5 offset1">
					<h4>'.$w->headingCustomerAddress.'</h4>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_address" class="required mandatory">'.$w->labelAddress.'</label>
							<input type="text" name="street" id="input_street" class="span12" required="required" value="'.htmlentities( $d->street ?? '', ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span3">
							<label for="input_postcode" class="required mandatory">'.$w->labelPostcode.'</label>
							<input type="text" name="postcode" id="input_postcode" class="span12" required="required" value="'.htmlentities( $d->postcode ?? '', ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
						<div class="span9">
							<label for="input_city" class="required mandatory">'.$w->labelCity.'</label>
							<input type="text" name="city" id="input_city" class="span12" required="required" value="'.htmlentities( $d->city ?? '', ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span6">
							<label for="input_country" class="required mandatory">'.$w->labelCountry.'</label>
							<select name="country" id="input_country" class="span12" required="required">'.$optCountry.'</select>
						</div>
						<div class="span6">
							<label for="input_region">'.$w->labelRegion.'</label>
							<input type="text" name="region" id="input_region" class="span12" value="'.htmlentities( $d->region ?? '', ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
				</div>
			</div>
			<br/>
			<div class="buttonbar well well-small">
				'.new \CeusMedia\Bootstrap\Button\Link( './shop/cart', $w->buttonToCart, 'not-pull-right', 'fa fa-fw fa-arrow-left' ).'
				'.new \CeusMedia\Bootstrap\Button\Submit( "save", $w->buttonToConditions, 'btn-success not-pull-right', 'fa fa-fw fa-arrow-right' ).'
			</div>
		</form>
	</div>
</div>
<script>
jQuery(document).ready(function(){
	jQuery(".typeahead").each(function(){
		jQuery(this).typeahead({
			source: '.json_encode( array_values( $this->words['countries'] ) ).',
			items: 4
		});
	});
});
</script>';
	}

	public function setAddress( $addressOrId ): self
	{
		if( is_object( $addressOrId ) )
			$this->address	= $addressOrId;
		else if( preg_match( '/^[0-9]+$/', $addressOrId ) )
			$this->address	= $this->model->get( $addressOrId );
		if( !$this->address )
			throw new InvalidArgumentException( 'Neither address nor valid address ID given' );
		return $this;
	}

	public function setHeading( string $heading ): self
	{
		$this->heading		= $heading;
		return $this;
	}

	public function setTextTop( string $text ): self
	{
		$this->textTop	= $text;
		return $this;
	}

	public function setType( $type ): self
	{
		$this->type		= $type;
		return $this;
	}

	public function setDefaultCountryCode( string $countryCode ): self
	{
		if( !array_key_exists( $countryCode, $this->words['countries'] ) )
			throw new DomainException( 'Invalid country code: '.$countryCode );
		$this->defaultCountryCode	= $countryCode;
		return $this;
	}
}
