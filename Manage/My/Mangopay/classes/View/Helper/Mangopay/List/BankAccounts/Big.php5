<?php
class View_Helper_Mangopay_List_BankAccounts_Big extends View_Helper_Mangopay_Abstract{

	protected $allowAdd;
	protected $bankAccounts;
	protected $from;
	protected $link;

	public function allowAdd( $allow ){
		$this->allowAdd	= $allow;
		return $this;
	}

	public function render(){
		$logoBank	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-bank fa-4x' ) );
		$logoAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus fa-4x' ) );

		$list	= [];
		foreach( $this->bankAccounts as $item ){
			if( !$item->Active )
				continue;
			$number	= UI_HTML_Tag::create( 'tt', $item->Details->BIC );
			$title	= UI_HTML_Tag::create( 'div', $item->OwnerName, array( 'class' => 'bankaccount-title' ) );
			$label	= $logoBank.$title.$number;

			$url	= sprintf( $this->link, $item->Id );
			$url	.= strlen( trim( $this->from ) ) ? '?from='.$this->from : '';

			$list[]	= UI_HTML_Tag::create( 'div', $label, array(
				'class'		=> 'bankaccount-list-item',
				'onclick'	=> 'document.location.href="'.$url.'";',
			) );
		}
		if( $this->allowAdd ){
			$number	= UI_HTML_Tag::create( 'div', 'Konto hinzufügen' );
			$item	= $logoAdd.$number;
			$urlAdd	= 'manage/my/mangopay/bank/add';
			$urlAdd	.= strlen( trim( $this->from ) ) ? '?from='.$this->from : '';
			$list[]	= UI_HTML_Tag::create( 'div', $item, array(
				'class'		=> 'bankaccount-list-item',
				'onclick'	=> 'document.location.href="'.$urlAdd.'";',
			) );
		}
		$list	= UI_HTML_Tag::create( 'div', $list );
		return $list;
	}

	public function setBankAccounts( $bankAccounts ){
		$this->bankAccounts	= $bankAccounts;
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
?>
