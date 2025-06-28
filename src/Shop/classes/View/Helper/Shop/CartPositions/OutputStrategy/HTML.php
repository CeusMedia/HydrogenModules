<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Shop_CartPositions_OutputStrategy_HTML extends View_Helper_Shop_CartPositions_OutputStrategy_Abstract
{
	public function render(): string
	{
		$words		= (object) $this->words['panel-cart'];
		$wordsCart	= (object) $this->words['cart'];

		$bodyRows		= [];
		[
			$totalPrice,
			$totalTax,
			$totalWeight,
			$taxes,
			$allSingle
		] = $this->enqueuePositionHtmlRowsAndReturnPrice( $bodyRows, $words );

		$footRows	= [];
		$priceShipping	= $this->enqueueShippingHtmlRowAndReturnPrice( $footRows, $taxes, $words, $totalWeight );
		$pricePayment	= $this->enqueuePaymentHtmlRowAndReturnPrice( $footRows, $taxes, $words, $totalPrice + $priceShipping );
		$priceTaxes		= $this->enqueueTaxesHtmlRowAndReturnPrice( $footRows, $taxes, $words, $totalPrice + $priceShipping );

		$this->enqueueTotalPriceHtmlRowAndReturnPrice( $footRows, $words, $totalPrice + $priceShipping + $pricePayment, $priceTaxes );

		$colgroup		= HtmlElements::ColumnGroup( '7%', '', '140', '140' );
		$tableAttr		= ['class' => 'table table-hover table-striped table-fixed articleList'];
		if( 0 && $allSingle ){
//			$colgroup		= HtmlElements::ColumnGroup( '7%', '', '140' );
			$tableAttr['class']	.= ' articleList-allSingle';
		}
		if( self::DISPLAY_MAIL === $this->display || !$allSingle )
			$tableAttr['class']	.= ' table-bordered';

		$thead			= HtmlTag::create( 'thead', HtmlTag::create( 'tr', [
			HtmlTag::create( 'th', $wordsCart->headPicture, ['class' => 'column-cart-picture th-center'] ),
			HtmlTag::create( 'th', $wordsCart->headLabel, ['class' => 'column-cart-label'] ),
			HtmlTag::create( 'th', $wordsCart->headQuantity, ['class' => 'column-cart-quantity th-center'] ),
			HtmlTag::create( 'th', $wordsCart->headPrice, ['class' => 'column-cart-price th-right'] ),
		] ) );
		return HtmlTag::create( 'table', [
			$colgroup,
			$thead,
			HtmlTag::create( 'tbody', $bodyRows ),
			HtmlTag::create( 'tfoot', $footRows )
		], $tableAttr );

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



	//  --  PRIVATE  --  //


	private function enqueuePaymentHtmlRowAndReturnPrice( array & $rows, array & $taxes, object $words, float $totalPrice ): float
	{
		$pricePayment	= .0;
		if( $this->env->getModules()->has( 'Shop_Payment' ) ){
			$logicPayment	= new Logic_Shop_Payment( $this->env );
			if( NULL !== $this->paymentsBackends )
				$logicPayment->setBackends( $this->paymentsBackends );
			if( $this->paymentBackend && $this->deliveryAddress ){
				$pricePayment	= $logicPayment->getPrice(
					$totalPrice,
					$this->paymentBackend,
					$this->deliveryAddress->country
				) ?? .0;
				if( $pricePayment > 0 )
					$rows[]	= HtmlTag::create( 'tr', [
						HtmlTag::create( 'td', '&nbsp;' ),
						HtmlTag::create( 'td', $words->labelPayment, ['class' => 'autocut'] ),
						HtmlTag::create( 'td', '&nbsp;', ['class' => 'column-cart-quantity'] ),
						HtmlTag::create( 'td', $this->formatPrice( $pricePayment ), ['class' => 'price'] )
					] );
			}
		}
		return $pricePayment;
	}

	/**
	 * @param array $rows
	 * @param object $words
	 * @return array[totalPrice: float, totalTax: float, totalWeight: float, taxes: array, allSingle: bool]
	 */
	private function enqueuePositionHtmlRowsAndReturnPrice( array & $rows, object $words ): array
	{
		$totalPrice		= 0;
		$totalTax		= 0;
		$totalWeight	= 0;
		$taxes			= [];
		$allSingle		= TRUE;
		foreach( $this->positions as $position ){
			$isSingle		= isset( $position->article->single ) && $position->article->single;
			$allSingle		= $allSingle && $isSingle;
//print_m( $position );die;
			if( !isset( $taxes[$position->article->tax->rate] ) )
				$taxes[$position->article->tax->rate]	= 0;
			$taxes[$position->article->tax->rate]	+= $position->article->tax->all;
			$price1			= $this->formatPrice( $position->article->price->one );
			$priceX			= $this->formatPrice( $position->article->price->all );
			$totalPrice		+= $position->article->price->all;
			$totalTax		+= $position->article->tax->all;
			$totalWeight	+= $position->article->weight->all;
			$title			= $position->article->title; //htmlspecialchars( $position->article->title, ENT_QUOTES, 'UTF-8' );
			$titleLinked	= HtmlTag::create( 'a', $title, ['href' => $position->article->link] );
			$titleCut		= HtmlTag::create( 'div', $titleLinked, ['class' => 'autocut article-title'] );
			$description	= $position->article->description;
			$description	= HtmlTag::create( 'div', $description, ['class' => 'autocut article-description'] );
			$image			= HtmlTag::create( 'img', NULL, ['src' => $position->article->picture->absolute] );
			$imageLinked	= HtmlTag::create( 'a', $image, ['href' => $position->article->link] );

			$priceCalc		= HtmlTag::create( 'small', $position->quantity.' x '.$price1, ['class'=> "muted"] );
			$priceTotal		= HtmlTag::create( 'big', HtmlTag::create( 'strong', $priceX ) );
			$cellPrice		= $position->quantity > 1 ? $priceTotal.'<br/>'.$priceCalc : $priceTotal;

			$quantity		= HtmlTag::create( 'big', $position->quantity );
			$cellQuantity	= $isSingle ? '' : $quantity;
			if( self::DISPLAY_MAIL !== $this->display && $this->changeable ){
				$helperButtons	= new View_Helper_Shop_CartPositions_PositionQuantityButtons( $this->env );
				$helperButtons->setPosition( $position );
				$helperButtons->setForwardPath( $this->forwardPath );
				$buttons		= $helperButtons->render();
				$cellQuantity	= $isSingle ? $buttons : $quantity.'&nbsp; &nbsp;'.$buttons;
			}

			$rows[]	= HtmlTag::create( 'tr', [
				HtmlTag::create( 'td', $imageLinked, ['class' => 'column-cart-picture position-image position-thumbnail'] ),
				HtmlTag::create( 'td', $titleCut.$description ),
				HtmlTag::create( 'td', $cellQuantity, ['class' => 'column-cart-quantity'] ),
				HtmlTag::create( 'td', $cellPrice, ['class' => 'column-cart-price'] ),
			] );
		}
		return [$totalPrice, $totalTax, $totalWeight, $taxes, $allSingle];
	}

	private function enqueueShippingHtmlRowAndReturnPrice( array & $rows, array & $taxes, object $words, float $totalWeight ): float
	{
		$priceShipping	= .0;
		if( $this->env->getModules()->has( 'Resource_Shop_Shipping' ) ){
			$logicShipping	= new Logic_Shop_Shipping( $this->env );
			if( $this->deliveryAddress ){
				$priceShipping	= $logicShipping->getPriceFromCountryCodeAndWeight(
					$this->deliveryAddress->country,
					$totalWeight
				);
				$rows[]	= HtmlTag::create( 'tr', [
					HtmlTag::create( 'td', '&nbsp;' ),
					HtmlTag::create( 'td', $words->labelShipping, ['class' => 'autocut'] ),
					HtmlTag::create( 'td', '&nbsp;', ['class' => 'column-cart-quantity'] ),
					HtmlTag::create( 'td', $this->formatPrice( $priceShipping ), ['class' => 'price'] )
				] );
			}
		}
		return $priceShipping;
	}

	private function enqueueTaxesHtmlRowAndReturnPrice( array & $rows, array $taxes, object $words, float $totalTax ): float
	{
		$taxMode		= $this->config->get( 'tax.included' ) ? $words->taxInclusive : $words->taxExclusive;
		foreach( $taxes as $rate => $amount ){
			$amount	= $this->formatPrice( $amount );
			$rows[]	= HtmlTag::create( 'tr', [
				HtmlTag::create( 'td', '&nbsp;' ),
				HtmlTag::create( 'td', sprintf( $taxMode.' '.$words->labelTax.' %s%%', $rate ), ['class' => 'autocut'] ),
				HtmlTag::create( 'td', '&nbsp;', ['class' => 'column-cart-quantity'] ),
				HtmlTag::create( 'td', $amount, ['class' => 'price'] )
			], ['class' => 'tax'] );
		}
		return $totalTax;
	}

	private function enqueueTotalPriceHtmlRowAndReturnPrice( array & $rows, object $words, float $totalPrice, float $totalTax ): float
	{
		$priceTotal		= $totalPrice;
		$priceTotal		+= ( $this->config->get( 'tax.included' ) ? 0 : $totalTax );
		$rows[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', '&nbsp;' ),
			HtmlTag::create( 'td', $words->labelTotal, ['class' => 'autocut'] ),
			HtmlTag::create( 'td', '&nbsp;', ['class' => 'column-cart-quantity'] ),
			HtmlTag::create( 'td', $this->formatPrice( $priceTotal ), ['class' => 'price'] )
		], ['class' => 'total'] );
		return $priceTotal;
	}
}
