<?php


class View_Helper_Shop_CartPositions_OutputStrategy_Text extends View_Helper_Shop_CartPositions_OutputStrategy_Abstract
{
	public function render(): string
	{
		$words		= (object) $this->words['panel-cart'];
		$helperText	= new View_Helper_Mail_Text();
		$list		= [];
		$list[]		= $helperText->line( "=", 78 );

		$list[]	= join( ' ', [
			$helperText->fit( $this->words['cart']['headLabel'], 60 ),
			$helperText->fit( $this->words['cart']['headQuantity'], 6, 0 ),
			$helperText->fit( $this->words['cart']['headPrice'], 10, 0 ),
		] );
		$list[]	= $helperText->line( "-", 78 );

		$totalCount		= 0;
		$totalPrice		= 0;
		$totalTax		= 0;
		$totalWeight	= 0;
		foreach( $this->positions as $position ){
			$totalCount		+= $position->quantity;
			$totalPrice		+= $position->article->price->all;
			$totalTax		+= $position->article->tax->all;
			$totalWeight	+= $position->article->weight->all;
			$list[]	= join( ' ', [
				$helperText->fit( $position->article->title, 60 ),
				$helperText->fit( $position->quantity, 6, 0 ),
				$helperText->fit( $this->formatPrice( $position->article->price->all, TRUE, FALSE ), 10, 0 ),
			] );
		}
		$list[]	= $helperText->line( "-", 78 );

		$list[]	= join( ' ', [
			$helperText->fit( $words->labelAmount, 60 ),
			$helperText->fit( "", 6, 0 ),
			$helperText->fit( $this->formatPrice( $totalPrice, TRUE, FALSE ), 10, 0 ),
		] );

		$taxMode	= $this->config->get( 'tax.included' ) ? $words->taxInclusive : $words->taxExclusive;
		$list[]	= join( ' ', [
			$helperText->fit( $taxMode.' '.$this->config->get( 'tax.percent' )."% ".$words->labelTax, 60 ),
			$helperText->fit( "", 6, 0 ),
			$helperText->fit( $this->formatPrice( $totalTax, TRUE, FALSE ), 10, 0 ),
		] );

		if( $this->env->getModules()->has( 'Shop_Shipping' ) ){
			$logicShipping	= new Logic_Shop_Shipping( $this->env );
			if( $this->deliveryAddress ){
				$priceShipping	= $logicShipping->getPriceFromCountryCodeAndWeight(
					$this->deliveryAddress->country,
					$totalWeight
				);
				$totalPrice	+= $priceShipping;
				$list[]	= join( ' ', [
					$helperText->fit( $words->labelShipping, 60 ),
					$helperText->fit( "", 6, 0 ),
					$helperText->fit( $this->formatPrice( $priceShipping, TRUE, FALSE ), 10, 0 ),
				] );
			}
		}

		$pricePayment	= 0;
		if( $this->env->getModules()->has( 'Shop_Payment' ) ){
			$logicPayment	= new Logic_Shop_Payment( $this->env );
			if( $this->deliveryAddress ){
				$pricePayment	= $logicPayment->getPrice(
					$totalPrice,
					$this->paymentBackend,
					$this->deliveryAddress->country
				);
				$totalPrice	+= $pricePayment;
				$list[]	= join( ' ', [
					$helperText->fit( $words->labelPayment, 60 ),
					$helperText->fit( "", 6, 0 ),
					$helperText->fit( $this->formatPrice( $pricePayment ?? 0, TRUE, FALSE ), 10, 0 ),
				] );
			}
		}

		$list[]	= $helperText->line( "-", 78 );
		if( !$this->config->get( 'tax.included' ) )
			$totalPrice	+= $totalTax;
		$list[]	= join( ' ', [
			$helperText->fit( $words->labelTotal, 60 ),
			$helperText->fit( "", 6, 0 ),
			$helperText->fit( $this->formatPrice( $totalPrice, TRUE, FALSE ), 10, 0 ),
		] );
		$list[]	= $helperText->line( "=", 78 );
		return join( "\n", $list );
	}

	//  --  PROTECTED  --  //


	/**
	 *	Render price.
	 *	@param		float		$price
	 *	@param		bool		$spaceBeforeCurrency
	 *	@param		bool		$asHtml
	 *	@return		string
	 */
	protected function formatPrice( float $price, bool $spaceBeforeCurrency = TRUE, bool $asHtml = TRUE ): string
	{
		$helper		= new View_Helper_Shop( $this->env );
		return $helper->formatPrice( $price, $spaceBeforeCurrency, $asHtml );
	}
}
