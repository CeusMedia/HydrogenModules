<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Shop_CartPositions_OutputStrategy_HtmlList extends View_Helper_Shop_CartPositions_OutputStrategy_Abstract
{
	public function render(): string
	{
		$words			= (object) $this->words['panel-cart'];

		$bodyRows		= [];
		[
			$totalPrice,
			$totalTax,
			$totalWeight,
			$taxes,
			$allSingle
		] = $this->enqueuePositionHtmlRowsAndReturnPrice( $bodyRows, $words );

		$footRows		= [];
		$priceShipping	= $this->enqueueShippingHtmlRowAndReturnPrice( $footRows, $taxes, $words, $totalWeight );
		$pricePayment	= $this->enqueuePaymentHtmlRowAndReturnPrice( $footRows, $taxes, $words, $totalPrice );

		$priceTax		= $this->formatPrice( $totalTax );
		$taxMode		= $this->config->get( 'tax.included' ) ? $words->taxInclusive : $words->taxExclusive;
		foreach( $taxes as $rate => $amount ){
			$amount	= $this->formatPrice( $amount );
			$footRows[]	= HtmlTag::create( 'tr', [
				HtmlTag::create( 'td', sprintf( $taxMode.' '.$words->labelTax.' %s%%', $rate ), ['class' => 'autocut', 'colspan' => 2] ),
				HtmlTag::create( 'td', $amount, ['class' => 'price'] )
			], ['class' => 'tax'] );
		}

		$priceTotal		= $totalPrice + $priceShipping + $pricePayment;
		$priceTotal		+= ( $this->config->get( 'tax.included' ) ? 0 : $totalTax );
		$footRows[]		= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $words->labelTotal, ['class' => 'autocut', 'colspan' => 2] ),
			HtmlTag::create( 'td', $this->formatPrice( $priceTotal ), ['class' => 'price'] )
		], ['class' => 'total'] );

		$colgroup		= HtmlElements::ColumnGroup( '25%', '40%', '35%' );
		$tableAttr		= ['class' => 'table not-table-hover not-table-striped table-fixed articleList table-borderless'];
		if( $allSingle ){
//			$colgroup		= HtmlElements::ColumnGroup( '7%', '', '140' );
			$tableAttr['class']	.= ' articleList-allSingle';
		}
		if( self::DISPLAY_MAIL === $this->display || !$allSingle )
			$tableAttr['class']	.= ' table-bordered';

		return HtmlTag::create( 'table', [
			$colgroup,
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


	/**
	 *	@param		array		$rows
	 *	@param		array		$taxes
	 *	@param		object		$words
	 *	@param		float		$totalPrice
	 *	@return		float
	 */
	private function enqueuePaymentHtmlRowAndReturnPrice( array & $rows, array & $taxes, object $words, float $totalPrice ): float
	{
		$pricePayment	= .0;
		if( $this->env->getModules()->has( 'Shop_Payment' ) ){
			$logicPayment	= new Logic_Shop_Payment( $this->env );
			if( $this->deliveryAddress ){
				$pricePayment	= $logicPayment->getPrice(
					$this->deliveryAddress->country,
					$this->paymentBackend,
					$this->deliveryAddress->country
				);
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
	 * @todo implement
	 */
	private function enqueuePositionHtmlRowsAndReturnPrice( array & $rows, object $words ): array
	{
		$totalPrice		= 0;
		$totalTax		= 0;
		$totalWeight	= 0;
		$taxes			= [];
		$allSingle		= TRUE;

		$wordsCart		= (object) $this->words['cart'];

		foreach( $this->positions as $position ){
			$isSingle		= isset( $position->article->single ) && $position->article->single;
			$allSingle		= $allSingle && $isSingle;

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
			if( $this->changeable ){
				$helperButtons	= new View_Helper_Shop_CartPositions_PositionQuantityButtons( $this->env );
				$helperButtons->setPosition( $position );
				$helperButtons->setForwardPath( $this->forwardPath );
				$buttons		= $helperButtons->render();
				$cellQuantity	= $isSingle ? $buttons : $quantity.'<br/>'.$buttons;
			}

			$cells	= [
				HtmlTag::create( 'td', $imageLinked, ['class' => 'column-cart-picture position-image position-thumbnail'] ),
				HtmlTag::create( 'td', HtmlTag::create( 'div', [
					HtmlTag::create( 'div', $titleCut.$description ),
					HtmlTag::create( 'div', [
						HtmlTag::create( 'div', $wordsCart->headQuantity.': '.$cellQuantity, ['style' => 'float: left; width: 50%; text-align: left'] ),
						HtmlTag::create( 'div', '<span class="hidden-phone">'.$wordsCart->headPrice.':</span> '.$cellPrice.'<br/><small class="muted">zzgl. MwSt 19%</small>', ['style' => 'float: left; width: 50%; text-align: right'] ),
					], [
						'class'	=> 'row-fluid',
						'style'	=> 'border-top: 1px solid rgba(127, 127, 127, 0.25)'
					] ),
				] ), ['colspan' => 2] ),
			];
			$rows[]	= HtmlTag::create( 'tr', $cells );
		}

		return [$totalPrice, $totalTax, $totalWeight, $taxes, $allSingle];
	}

	/**
	 *	@param		array		$rows
	 *	@param		array		$taxes
	 *	@param		object		$words
	 *	@param		float		$totalWeight
	 *	@return		float
	 */
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

	/**
	 * @todo implement
	 */
	private function enqueueTaxesHtmlRowAndReturnPrice( array & $rows, array & $taxes, object $words, float $totalTax ): float
	{
		return $totalTax;
	}

	/**
	 * @todo implement
	 */
	private function enqueueTotalPriceHtmlRowAndReturnPrice( array & $rows, array & $taxes, object $words, float $totalPrice, float $totalTax ): float
	{
		return $totalPrice;
	}
}
