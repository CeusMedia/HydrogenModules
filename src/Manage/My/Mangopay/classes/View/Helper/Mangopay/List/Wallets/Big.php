<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Mangopay_List_Wallets_Big extends View_Helper_Mangopay_Abstract{

	protected array $wallets		= [];
	protected ?bool $allowAdd		= NULL;
	protected ?string $from			= NULL;
	protected ?string $link			= NULL;

	public function allowAdd( bool $allow ): self
	{
		$this->allowAdd	= $allow;
		return $this;
	}

	public function render(): string
	{
		$helperWalletLogo	= new View_Helper_Mangopay_Entity_WalletLogo( $this->env );
		$helperWalletLogo->setSize( View_Helper_Mangopay_Entity_WalletLogo::SIZE_LARGE );

		$helperMoney		= new View_Helper_Mangopay_Entity_Money( $this->env );
		$helperMoney->setFormat( View_Helper_Mangopay_Entity_Money::FORMAT_AMOUNT_SPACE_CURRENCY );
		$helperMoney->setNumberFormat( View_Helper_Mangopay_Entity_Money::NUMBER_FORMAT_COMMA );
		$helperMoney->setNodeName( 'div' );
		$list	= [];
		foreach( $this->wallets as $wallet ){
		//	print_m( $card );die;
			$logo		= $helperWalletLogo->setWallet( $wallet->Balance )->render();
			$balance	= $helperMoney->set( $wallet->Balance )->render();
			$title		= HtmlTag::create( 'div', $wallet->Description, ['class' => 'card-title'] );
			$item		= $logo.$title.$balance;
			$url	= sprintf( $this->link, $wallet->Id );
			$url	.= strlen( trim( $this->from ) ) ? '?from='.$this->from : '';
			$list[]	= HtmlTag::create( 'div', $item, [
				'class'		=> 'card-list-item-large',
				'onclick'	=> 'document.location.href="./'.$url.'";',
			] );
		}
		$list	= HtmlTag::create( 'div', $list );
		return $list;
	}

	public function set( array $wallets ): self
	{
		$this->wallets	= $wallets;
		return $this;
	}

	public function setFrom( string $from ): self
	{
		$this->from		= $from;
		return $this;
	}

	public function setLink( string $link ): self
	{
		$this->link		= $link;
		return $this;
	}
}
