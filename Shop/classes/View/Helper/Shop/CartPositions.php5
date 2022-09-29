<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Shop_CartPositions
{
	const DISPLAY_UNKNOWN			= 0;
	const DISPLAY_BROWSER			= 1;
	const DISPLAY_MAIL				= 2;

	const DISPLAYS					= [
		self::DISPLAY_UNKNOWN,
		self::DISPLAY_BROWSER,
		self::DISPLAY_MAIL,
	];

	const OUTPUT_UNKNOWN			= 0;
	const OUTPUT_TEXT				= 1;
	const OUTPUT_HTML				= 2;
	const OUTPUT_HTML_LIST			= 3;

	const OUTPUTS					= [
		self::OUTPUT_UNKNOWN,
		self::OUTPUT_TEXT,
		self::OUTPUT_HTML,
		self::OUTPUT_HTML_LIST,
	];

	protected $bridge;
	protected $changeable;
	protected $forwardPath;
	protected $deliveryAddress;
	protected $env;
	protected $positions;
	protected $display				= self::DISPLAY_BROWSER;
	protected $output				= self::OUTPUT_HTML;

	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->config	= $this->env->getConfig()->getAll( 'module.shop.', TRUE );
		$this->words	= $this->env->getLanguage()->getWords( 'shop' );
		$this->bridge	= new Logic_ShopBridge( $this->env );
	}

	public function render(){
		if( !$this->positions )
			return;
		switch( $this->output ){
			case self::OUTPUT_HTML:
				return $this->renderAsHtml();
			case self::OUTPUT_HTML_LIST:
				return $this->renderAsHtmlList();
			case self::OUTPUT_TEXT:
				return $this->renderAsText();
		}
	}

	public function setChangeable( bool $isChangeable = TRUE ): self
	{
		$this->changeable	= $isChangeable;
		return $this;
	}

	public function setForwardPath( string $forwardPath ): self
	{
		$this->forwardPath		= $forwardPath;
		return $this;
	}

	public function setDeliveryAddress( $address ): self
	{
		$this->deliveryAddress	= $address;
		return $this;
	}

	public function setDisplay( $display ){
		if( !in_array( (int) $display, array( self::DISPLAY_BROWSER, self::DISPLAY_MAIL	) ) )
			throw new InvalidArgumentException( 'Invalid display format' );
		$this->display		= $display;
		return $this;
	}

	public function setOutput( int $format ): self
	{
		$formats	= [self::OUTPUT_HTML, self::OUTPUT_TEXT, self::OUTPUT_HTML_LIST];
		if( !in_array( (int) $format, $formats ) )
			throw new InvalidArgumentException( 'Invalid output format' );
		$this->output		= (int) $format;
		return $this;
	}

	public function setPositions( array $positions ): self
	{
		$this->positions		= $positions;
		foreach( $positions as $nr => $position ){
			if( !isset( $position->article ) ){
				$source		= $this->bridge->getBridgeObject( (int) $position->bridgeId );
				$article	= $source->get( $position->articleId, $position->quantity );
				$positions[$nr]->article	= $article;
			}
		}
		return $this;
	}

	//  --  PROTECTED  --  //

	protected function formatPrice( float $price, bool $spaceBeforeCurrency = TRUE, bool $asHtml = TRUE ): string
	{
		$helper		= new View_Helper_Shop( $this->env );
		return $helper->formatPrice( $price, $spaceBeforeCurrency, $asHtml );
	}

	protected function renderAsHtmlList(){
		$words		= (object) $this->words['panel-cart'];
		$wordsCart	= (object) $this->words['cart'];
		$rows		= [];
		$totalPrice	= 0;
		$totalTax	= 0;
		$taxes		= [];
		$allSingle	= TRUE;
		foreach( $this->positions as $nr => $position ){
			$isSingle		= isset( $position->article->single ) && $position->article->single;
			$allSingle		= $allSingle && $isSingle;

			if( !isset( $taxes[$position->article->tax->rate] ) )
				$taxes[$position->article->tax->rate]	= 0;
			$taxes[$position->article->tax->rate]	+= $position->article->tax->all;
			$price1			= $this->formatPrice( $position->article->price->one );
			$priceX			= $this->formatPrice( $position->article->price->all );
			$totalPrice		+= $position->article->price->all;
			$totalTax		+= $position->article->tax->all;
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
				$buttons		= $this->renderPositionQuantityButtons( $position );
				$cellQuantity	= $isSingle ? $buttons : $quantity.'<br/>'.$buttons;
			}

			$cells			= array(
				HtmlTag::create( 'td', $imageLinked, ['class' => 'column-cart-picture position-image position-thumbnail'] ),
				HtmlTag::create( 'td', HtmlTag::create( 'div', array(
					HtmlTag::create( 'div', $titleCut.$description ),
					HtmlTag::create( 'div', array(
						HtmlTag::create( 'div', $wordsCart->headQuantity.': '.$cellQuantity, ['style' => 'float: left; width: 50%; text-align: left'] ),
						HtmlTag::create( 'div', '<span class="hidden-phone">'.$wordsCart->headPrice.':</span> '.$cellPrice.'<br/><small class="muted">zzgl. MwSt 19%</small>', ['style' => 'float: left; width: 50%; text-align: right'] ),
					), array( 'class' => 'row-fluid', 'style' => 'border-top: 1px solid rgba(127, 127, 127, 0.25)' ) ),
				) ), ['colspan' => 2] ),
			);
			$rows[]	= HtmlTag::create( 'tr', $cells );
		}
		$colgroup		= HtmlElements::ColumnGroup( '25%', '40%', '35%' );
		$tbody			= HtmlTag::create( 'tbody', $rows );

		//  @todo add shipping
		$priceShipping	= 0;
		if( $this->env->getModules()->has( 'Shop_Shipping' ) ){
			$logicShipping	= new Logic_Shop_Shipping( $this->env );
			if( $this->deliveryAddress ){
				$priceShipping	= $logicShipping->getPriceFromCountryCodeAndWeight(
					$this->deliveryAddress->country,
					$totalWeight
				);
				$rows[]	= HtmlTag::create( 'tr', array(
					HtmlTag::create( 'td', '&nbsp;' ),
					HtmlTag::create( 'td', $words->labelShipping, ['class' => 'autocut'] ),
					HtmlTag::create( 'td', '&nbsp;', ['class' => 'column-cart-quantity'] ),
					HtmlTag::create( 'td', $this->formatPrice( $priceShipping ), ['class' => 'price'] )
				) );
			}
		}
		$priceTotal		= $totalPrice + $priceShipping;

		$priceTax		= $this->formatPrice( $totalTax );
		$taxMode		= $this->config->get( 'tax.included' ) ? $words->taxInclusive : $words->taxExclusive;
		$rows	= [];
		foreach( $taxes as $rate => $amount ){
			$amount	= $this->formatPrice( $amount );
			$rows[]	= HtmlTag::create( 'tr', array(
				HtmlTag::create( 'td', sprintf( $taxMode.' '.$words->labelTax.' %s%%', $rate ), ['class' => 'autocut', 'colspan' => 2] ),
				HtmlTag::create( 'td', $amount, ['class' => 'price'] )
			), ['class' => 'tax'] );
		}

		$priceTotal		= $totalPrice + $priceShipping;
		$priceTotal		+= ( $this->config->get( 'tax.included' ) ? 0 : $totalTax );
		$rows[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $words->labelTotal, ['class' => 'autocut', 'colspan' => 2] ),
			HtmlTag::create( 'td', $this->formatPrice( $priceTotal ), ['class' => 'price'] )
		), ['class' => 'total'] );
		$tfoot			= HtmlTag::create( 'tfoot', $rows );

		$tableAttr		= ['class' => 'table not-table-hover not-table-striped table-fixed articleList table-borderless'];
		if( $allSingle ){
//			$colgroup		= HtmlElements::ColumnGroup( '7%', '', '140' );
			$tableAttr['class']	.= ' articleList-allSingle';
		}
		if( !$allSingle || $this->display === self::DISPLAY_MAIL )
			$tableAttr['class']	.= ' table-bordered';

		$tablePositions	= HtmlTag::create( 'table', $colgroup.$tbody.$tfoot, $tableAttr );
		return $tablePositions;

	}

	protected function renderAsHtml(): string
	{
		$words		= (object) $this->words['panel-cart'];
		$wordsCart	= (object) $this->words['cart'];
		$rows			= [];
		$totalPrice		= 0;
		$totalTax		= 0;
		$totalWeight	= 0;
		$taxes			= [];
		$allSingle		= TRUE;
		foreach( $this->positions as $nr => $position ){
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
			if( $this->changeable ){
				$buttons		= $this->renderPositionQuantityButtons( $position );
				$cellQuantity	= $isSingle ? $buttons : $quantity.'&nbsp; &nbsp;'.$buttons;
			}

			$cells			= array(
				HtmlTag::create( 'td', $imageLinked, ['class' => 'column-cart-picture position-image position-thumbnail'] ),
				HtmlTag::create( 'td', $titleCut.$description ),
				HtmlTag::create( 'td', $cellQuantity, ['class' => 'column-cart-quantity'] ),
				HtmlTag::create( 'td', $cellPrice, ['class' => 'column-cart-price'] ),
			);
			$rows[]	= HtmlTag::create( 'tr', $cells );
		}
		$colgroup		= HtmlElements::ColumnGroup( '7%', '', '140', '140' );
		$thead			= HtmlTag::create( 'thead', HtmlTag::create( 'tr', array(
				HtmlTag::create( 'th', $wordsCart->headPicture, ['class' => 'column-cart-picture th-center'] ),
				HtmlTag::create( 'th', $wordsCart->headLabel, ['class' => 'column-cart-label'] ),
				HtmlTag::create( 'th', $wordsCart->headQuantity, ['class' => 'column-cart-quantity th-center'] ),
				HtmlTag::create( 'th', $wordsCart->headPrice, ['class' => 'column-cart-price th-right'] ),
		) ) );
		$tbody			= HtmlTag::create( 'tbody', $rows );

		$priceShipping	= 0;
		$priceTax		= $this->formatPrice( $totalTax );
		$taxMode		= $this->config->get( 'tax.included' ) ? $words->taxInclusive : $words->taxExclusive;
		$rows	= [];
		foreach( $taxes as $rate => $amount ){
			$amount	= $this->formatPrice( $amount );
			$rows[]	= HtmlTag::create( 'tr', array(
				HtmlTag::create( 'td', '&nbsp;' ),
				HtmlTag::create( 'td', sprintf( $taxMode.' '.$words->labelTax.' %s%%', $rate ), ['class' => 'autocut'] ),
				HtmlTag::create( 'td', '&nbsp;', ['class' => 'column-cart-quantity'] ),
				HtmlTag::create( 'td', $amount, ['class' => 'price'] )
			), ['class' => 'tax'] );
		}
		$priceShipping	= 0;
		if( $this->env->getModules()->has( 'Shop_Shipping' ) ){
			$logicShipping	= new Logic_Shop_Shipping( $this->env );
			if( $this->deliveryAddress ){
				$priceShipping	= $logicShipping->getPriceFromCountryCodeAndWeight(
					$this->deliveryAddress->country,
					$totalWeight
				);
				$rows[]	= HtmlTag::create( 'tr', array(
					HtmlTag::create( 'td', '&nbsp;' ),
					HtmlTag::create( 'td', $words->labelShipping, ['class' => 'autocut'] ),
					HtmlTag::create( 'td', '&nbsp;', ['class' => 'column-cart-quantity'] ),
					HtmlTag::create( 'td', $this->formatPrice( $priceShipping ), ['class' => 'price'] )
				) );
			}
		}
		$priceTotal		= $totalPrice + $priceShipping;
		$priceTotal		+= ( $this->config->get( 'tax.included' ) ? 0 : $totalTax );
		$rows[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', '&nbsp;' ),
			HtmlTag::create( 'td', $words->labelTotal, ['class' => 'autocut'] ),
			HtmlTag::create( 'td', '&nbsp;', ['class' => 'column-cart-quantity'] ),
			HtmlTag::create( 'td', $this->formatPrice( $priceTotal ), ['class' => 'price'] )
		), ['class' => 'total'] );

		$tfoot			= HtmlTag::create( 'tfoot', $rows );
		$tableAttr		= ['class' => 'table table-hover table-striped table-fixed articleList'];
		if( 0 && $allSingle ){
//			$colgroup		= HtmlElements::ColumnGroup( '7%', '', '140' );
			$tableAttr['class']	.= ' articleList-allSingle';
		}
		if( !$allSingle || $this->display === self::DISPLAY_MAIL )
			$tableAttr['class']	.= ' table-bordered';

		$tablePositions	= HtmlTag::create( 'table', $colgroup.$thead.$tbody.$tfoot, $tableAttr );
		return $tablePositions;
	}

	protected function renderAsText(): string
	{
		$words		= (object) $this->words['panel-cart'];
		$helperText	= new View_Helper_Mail_Text();
		$list		= [];
		$list[]		= $helperText->line( "=", 78 );

		$list[]	= join( ' ', array(
			$helperText->fit( $this->words['cart']['headLabel'], 60 ),
			$helperText->fit( $this->words['cart']['headQuantity'], 6, 0 ),
			$helperText->fit( $this->words['cart']['headPrice'], 10, 0 ),
		) );
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
			$list[]	= join( ' ', array(
				$helperText->fit( $position->article->title, 60 ),
				$helperText->fit( $position->quantity, 6, 0 ),
				$helperText->fit( $this->formatPrice( $position->article->price->all, TRUE, FALSE ), 10, 0 ),
			) );
		}
		$list[]	= $helperText->line( "-", 78 );

		$list[]	= join( ' ', array(
			$helperText->fit( $words->labelAmount, 60 ),
			$helperText->fit( "", 6, 0 ),
			$helperText->fit( $this->formatPrice( $totalPrice, TRUE, FALSE ), 10, 0 ),
		) );

		$taxMode	= $this->config->get( 'tax.included' ) ? $words->taxInclusive : $words->taxExclusive;
		$list[]	= join( ' ', array(
			$helperText->fit( $taxMode.' '.$this->config->get( 'tax.percent' )."% ".$words->labelTax, 60 ),
			$helperText->fit( "", 6, 0 ),
			$helperText->fit( $this->formatPrice( $totalTax, TRUE, FALSE ), 10, 0 ),
		) );

		if( $this->env->getModules()->has( 'Shop_Shipping' ) ){
			$logicShipping	= new Logic_Shop_Shipping( $this->env );
			if( $this->deliveryAddress ){
				$priceShipping	= $logicShipping->getPriceFromCountryCodeAndWeight(
					$this->deliveryAddress->country,
					$totalWeight
				);
				$totalPrice	+= $priceShipping;
				$list[]	= join( ' ', array(
					$helperText->fit( $words->labelShipping, 60 ),
					$helperText->fit( "", 6, 0 ),
					$helperText->fit( $this->formatPrice( $priceShipping, TRUE, FALSE ), 10, 0 ),
				) );
			}
		}

		$list[]	= $helperText->line( "-", 78 );
		if( !$this->config->get( 'tax.included' ) )
			$totalPrice	+= $totalTax;
		$list[]	= join( ' ', array(
			$helperText->fit( $words->labelTotal, 60 ),
			$helperText->fit( "", 6, 0 ),
			$helperText->fit( $this->formatPrice( $totalPrice, TRUE, FALSE ), 10, 0 ),
		) );
		$list[]	= $helperText->line( "=", 78 );
		return join( "\n", $list );
	}

	protected function renderPositionQuantityButtons( $position ): string
	{
		$w				= (object) $this->words['cart'];
		$iconPlus		= "&plus;";
		$iconMinus		= "&minus;";
		$iconRemove		= "&times;";
		if( $this->env->getModules()->has( 'UI_Font_FontAwesome' ) ){
			$iconPlus		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );
			$iconMinus		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-minus'] );
			$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-times'] );
		}

		$urlIncrease	= './shop/changePositionQuantity/'.$position->bridgeId.'/'.$position->articleId.'/1/plus';
		$urlDecrease	= './shop/changePositionQuantity/'.$position->bridgeId.'/'.$position->articleId.'/1/minus';
		$urlRemove		= './shop/removeArticle/'.$position->articleId;

		if( $this->forwardPath ){
			$urlIncrease	.= '?forwardTo='.urlencode( $this->forwardPath );
			$urlDecrease	.= '?forwardTo='.urlencode( $this->forwardPath );
			$urlRemove		.= '?forwardTo='.urlencode( $this->forwardPath );
		}

		$buttonPlus		= HtmlTag::create( 'a', $iconPlus, array(
			'href'		=> $urlIncrease,
			'class'		=> 'btn btn-mini btn-success',
			'id'		=> 'btn-shop-cart-plus',
			'title'		=> $w->altIncrease,
		) );
		$buttonMinus	= HtmlTag::create( 'a', $iconMinus, array(
			'href'		=> $urlDecrease,
			'class'		=> 'btn btn-mini btn-warning',
			'id'		=> 'btn-shop-cart-minus',
			'title'		=> $w->altDecrease,
		) );
		$buttonRemove	= HtmlTag::create( 'a', $iconRemove, array(
			'href'		=> $urlRemove,
			'class'		=> 'btn btn-mini btn-danger',
			'id'		=> 'btn-shop-cart-remove',
			'title'		=> $w->altRemove,
		) );
		if( isset( $position->article->single ) && $position->article->single )
			return $buttonRemove;
		$buttons		= [$buttonPlus, $buttonMinus, $buttonRemove];
		return new \CeusMedia\Bootstrap\Button\Group( $buttons );
	}
}
