<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Mangopay_List_BankAccounts_Big extends View_Helper_Mangopay_Abstract
{
	protected array $bankAccounts		= [];
	protected ?bool $allowAdd			= NULL;
	protected ?string $from				= NULL;
	protected ?string $link				= NULL;

	public function allowAdd( bool $allow ): self
	{
		$this->allowAdd	= $allow;
		return $this;
	}

	public function render(): string
	{
		$logoBank	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-bank fa-4x'] );
		$logoAdd	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus fa-4x'] );

		$list	= [];
		foreach( $this->bankAccounts as $item ){
			if( !$item->Active )
				continue;
			$number	= HtmlTag::create( 'tt', $item->Details->BIC );
			$title	= HtmlTag::create( 'div', $item->OwnerName, ['class' => 'bankaccount-title'] );
			$label	= $logoBank.$title.$number;

			$url	= sprintf( $this->link, $item->Id );
			$url	.= strlen( trim( $this->from ) ) ? '?from='.$this->from : '';

			$list[]	= HtmlTag::create( 'div', $label, [
				'class'		=> 'bankaccount-list-item',
				'onclick'	=> 'document.location.href="'.$url.'";',
			] );
		}
		if( $this->allowAdd ){
			$number	= HtmlTag::create( 'div', 'Konto hinzufügen' );
			$item	= $logoAdd.$number;
			$urlAdd	= 'manage/my/mangopay/bank/add';
			$urlAdd	.= strlen( trim( $this->from ) ) ? '?from='.$this->from : '';
			$list[]	= HtmlTag::create( 'div', $item, [
				'class'		=> 'bankaccount-list-item',
				'onclick'	=> 'document.location.href="'.$urlAdd.'";',
			] );
		}
		$list	= HtmlTag::create( 'div', $list );
		return $list;
	}

	public function setBankAccounts( array $bankAccounts ): self
	{
		$this->bankAccounts	= $bankAccounts;
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
