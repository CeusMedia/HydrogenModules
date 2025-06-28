<?php

use CeusMedia\Bootstrap\Button\Group as BootstrapButtonGroup;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Shop_CartPositions_PositionQuantityButtons
{
	protected Environment $env;
	protected ?Entity_Shop_Order_Position $position	= NULL;
	protected ?string $forwardPath					= NULL;
	protected array $words;

	/**
	 *	@param		Environment				$env
	 */
	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->words	= $this->env->getLanguage()->getWords( 'shop' );
	}

	/**
	 *	@return		string
	 */
	public function render(): string
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

		$urlIncrease	= './shop/changePositionQuantity/'.$this->position->bridgeId.'/'.$this->position->articleId.'/1/plus';
		$urlDecrease	= './shop/changePositionQuantity/'.$this->position->bridgeId.'/'.$this->position->articleId.'/1/minus';
		$urlRemove		= './shop/removeArticle/'.$this->position->articleId;

		if( NULL !== $this->forwardPath ){
			$urlIncrease	.= '?forwardTo='.urlencode( $this->forwardPath );
			$urlDecrease	.= '?forwardTo='.urlencode( $this->forwardPath );
			$urlRemove		.= '?forwardTo='.urlencode( $this->forwardPath );
		}

		$buttonPlus		= HtmlTag::create( 'a', $iconPlus, [
			'href'		=> $urlIncrease,
			'class'		=> 'btn btn-mini btn-success',
			'id'		=> 'btn-shop-cart-plus',
			'title'		=> $w->altIncrease,
		] );
		$buttonMinus	= HtmlTag::create( 'a', $iconMinus, [
			'href'		=> $urlDecrease,
			'class'		=> 'btn btn-mini btn-warning',
			'id'		=> 'btn-shop-cart-minus',
			'title'		=> $w->altDecrease,
		] );
		$buttonRemove	= HtmlTag::create( 'a', $iconRemove, [
			'href'		=> $urlRemove,
			'class'		=> 'btn btn-mini btn-danger',
			'id'		=> 'btn-shop-cart-remove',
			'title'		=> $w->altRemove,
		] );
		if( isset( $this->position->article->single ) && $this->position->article->single )
			return $buttonRemove;

		$group	= new BootstrapButtonGroup( [$buttonPlus, $buttonMinus, $buttonRemove] );
		return $group->render();
	}

	/**
	 *	@param		string		$forwardPath
	 *	@return		self
	 */
	public function setForwardPath( string $forwardPath ): self
	{
		$this->forwardPath	= $forwardPath;
		return $this;
	}

	/**
	 *	@param		Entity_Shop_Order_Position		$position
	 *	@return		self
	 */
	public function setPosition( Entity_Shop_Order_Position $position ): self
	{
		$this->position	= $position;
		return $this;
	}
}