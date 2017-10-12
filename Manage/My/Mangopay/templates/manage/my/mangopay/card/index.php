<?php
class View_Helper_Mangopay_List_Cards_Big{

	protected $allowAdd;
	protected $cards;
	protected $env;
	protected $from;
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
		$helperCardLogo		= new View_Helper_Mangopay_Entity_CardProviderLogo( $this->env );
		$helperCardLogo->setSize( View_Helper_Mangopay_Entity_CardProviderLogo::SIZE_LARGE );
		$helperCardNumber	= new View_Helper_Mangopay_Entity_CardNumber( $this->env );
		$list	= array();
		foreach( $this->cards as $card ){
		//	print_m( $card );die;
			$logo	= $helperCardLogo->setProvider( $card->CardProvider )->render();
			$number	= $helperCardNumber->set( $card->Alias )->render();
			$title	= UI_HTML_Tag::create( 'div', $card->Tag, array( 'class' => 'card-title' ) );
			$item	= $logo.$number.$title;
			$url	= sprintf( $this->link, $card->Id );
			$url	.= strlen( trim( $this->from ) ) ? '?from='.$this->from : '';
			$list[]	= UI_HTML_Tag::create( 'div', $item, array(
				'class'		=> 'card-list-item-large',
				'onclick'	=> 'document.location.href="./'.$url.'";',
			) );
		}
		if( $this->allowAdd ){
			$logo	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus fa-4x' ) );
			$number	= UI_HTML_Tag::create( 'div', 'Karte hinzufügen' );
			$item	= $logo.$number;
			$urlAdd	= 'manage/my/mangopay/card/registration';
			$urlAdd	.= strlen( trim( $this->from ) ) ? '?from='.$this->from : '';
			$list[]	= UI_HTML_Tag::create( 'div', $item, array(
				'class'		=> 'card-list-item-large',
				'onclick'	=> 'document.location.href="./'.$urlAdd.'";',
			) );
		}
		$list	= UI_HTML_Tag::create( 'div', $list );
		return $list;
	}

	public function setCards( $cards ){
		$this->cards	= $cards;
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

$panel	= new View_Helper_Mangopay_List_Cards_Big( $env );
$panel->setCards( $cards );
$panel->setLink( 'manage/my/mangopay/card/view/%s' );
$panel->setFrom( $from );
$panel->allowAdd( TRUE );
return '<h2>Kreditkarten</h2>'.$panel;
