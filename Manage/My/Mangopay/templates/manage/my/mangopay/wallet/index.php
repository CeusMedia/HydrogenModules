<?php
class View_Helper_Mangopay_List_Wallet_Big{

	protected $allowAdd;
	protected $wallets;
	protected $from;
	protected $env;
	protected $link;

	public function __toString(){
		return $this->render();
	}

	public function __construct( $env ){
		$this->env		= $env;
	}

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
		$list	= array();
		foreach( $this->wallets as $wallet ){
		//	print_m( $card );die;
			$logo		= $helperWalletLogo->setWallet( $wallet->Balance )->render();
			$balance	= $helperMoney->set( $wallet->Balance )->render();
			$title		= UI_HTML_Tag::create( 'div', $balance, array( 'class' => 'card-title' ) );
			$item		= $logo.$title;
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

$panel	= new View_Helper_Mangopay_List_Wallet_Big( $env );
$panel->set( $wallets );
$panel->setLink( 'manage/my/mangopay/wallet/view/%s' );
//$panel->setFrom( $from );
$panel->allowAdd( TRUE );
return '<h2>Portmoney</h2>'.$panel;


$helper	= new View_Helper_Panel_Mangopay_Wallets( $env );
return $helper->setData( $wallets )->render();
