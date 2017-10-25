<?php
class View_Helper_Mangopay_List_Wallets_Big extends View_Helper_Mangopay_Abstract{

	protected $allowAdd;
	protected $wallets;
	protected $from;
	protected $link;

	public function allowAdd( $allow ){
		$this->allowAdd	= $allow;
		return $this;
	}

	public function render(){
		$helperWalletLogo	= new View_Helper_Mangopay_Entity_WalletLogo( $this->env );
		$helperWalletLogo->setSize( View_Helper_Mangopay_Entity_WalletLogo::SIZE_LARGE );

		$helperMoney		= new View_Helper_Mangopay_Entity_Money( $this->env );
		$helperMoney->setFormat( View_Helper_Mangopay_Entity_Money::FORMAT_AMOUNT_SPACE_CURRENCY );
		$helperMoney->setNumberFormat( View_Helper_Mangopay_Entity_Money::NUMBER_FORMAT_COMMA );
		$helperMoney->setNodeName( 'div' );
		$list	= array();
		foreach( $this->wallets as $wallet ){
		//	print_m( $card );die;
			$logo		= $helperWalletLogo->setWallet( $wallet->Balance )->render();
			$balance	= $helperMoney->set( $wallet->Balance )->render();
			$title		= UI_HTML_Tag::create( 'div', $wallet->Description, array( 'class' => 'card-title' ) );
			$item		= $logo.$title.$balance;
			$url	= sprintf( $this->link, $wallet->Id );
			$url	.= strlen( trim( $this->from ) ) ? '?from='.$this->from : '';
			$list[]	= UI_HTML_Tag::create( 'div', $item, array(
				'class'		=> 'card-list-item-large',
				'onclick'	=> 'document.location.href="./'.$url.'";',
			) );
		}
		$list	= UI_HTML_Tag::create( 'div', $list );
		return $list;
	}

	public function set( $wallets ){
		$this->wallets	= $wallets;
		return $this;
	}

	public function setFrom( $from ){
		$this->from		= $from;
		return $this;
	}

	public function setLink( $link ){
		$this->link		= $link;
		return $this;
	}
}
