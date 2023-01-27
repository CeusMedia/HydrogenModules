<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Mangopay_List_Cards_Big extends View_Helper_Mangopay_Abstract
{
	protected array $cards			= [];
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
		$helperCardLogo		= new View_Helper_Mangopay_Entity_CardProviderLogo( $this->env );
		$helperCardLogo->setSize( View_Helper_Mangopay_Entity_CardProviderLogo::SIZE_LARGE );
		$helperCardNumber	= new View_Helper_Mangopay_Entity_CardNumber( $this->env );
		$list	= [];
		foreach( $this->cards as $card ){
		//	print_m( $card );die;
			$logo	= $helperCardLogo->setProvider( $card->CardProvider )->render();
			$number	= $helperCardNumber->set( $card->Alias )->render();
			$title	= HtmlTag::create( 'div', $card->Tag, ['class' => 'card-title'] );
			$item	= $logo.$number.$title;
			$url	= sprintf( $this->link, $card->Id );
			$url	.= strlen( trim( $this->from ) ) ? '?from='.$this->from : '';
			$list[]	= HtmlTag::create( 'div', $item, [
				'class'		=> 'card-list-item-large',
				'onclick'	=> 'document.location.href="./'.$url.'";',
			] );
		}
		if( $this->allowAdd ){
			$logo	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus fa-4x'] );
			$number	= HtmlTag::create( 'div', 'Karte hinzufügen' );
			$item	= $logo.$number;
			$urlAdd	= 'manage/my/mangopay/card/registration';
			$urlAdd	.= strlen( trim( $this->from ) ) ? '?from='.$this->from : '';
			$list[]	= HtmlTag::create( 'div', $item, [
				'class'		=> 'card-list-item-large',
				'onclick'	=> 'document.location.href="./'.$urlAdd.'";',
			] );
		}
		$list	= HtmlTag::create( 'div', $list );
		return $list;
	}

	public function setCards( array $cards ): self
	{
		$this->cards	= $cards;
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
