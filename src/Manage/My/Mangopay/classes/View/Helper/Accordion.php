<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Accordion
{
	protected array $parts			= [];
	protected array $open			= [];
	protected bool $singleOpen		= FALSE;
	protected int|string|NULL $id	= NULL;

	public function __construct( string $id )
	{
		$this->setId( $id );
	}

	public function add( string $id, string $title, string $content, ?string $class = NULL ): self
	{
		$this->parts[]	= (object) [
			'id'		=> $id,
			'title'		=> $title,
			'content'	=> $content,
			'class'		=> $class,
		];
		return $this;
	}

	public function render(): string
	{
		$groups		= [];
		foreach( $this->parts as $part ){
			$class		= 'accordion-toggle '.( $part->class ?: '' );
			$link		= HtmlTag::create( 'a', $part->title, [
				'href'			=> $_SERVER['REQUEST_URI'].'#'.$part->id,
				'class'			=> $class,
				'data-toggle'	=> 'collapse',
				'data-parent'	=> $this->singleOpen ? '#'.$this->id : NULL,
				'data-target'	=> '#'.$part->id
			] );
			$content	= HtmlTag::create( 'div', $part->content, ['class' => 'accordion-inner'] );
			$isOpen		= in_array( $part->id, $this->open );
			$heading	= HtmlTag::create( 'div', $link, ['class' => 'accordion-heading'] );
			$body		= HtmlTag::create( 'div', $content, ['class' => 'accordion-body collapse'.( $isOpen ? ' in' : '' ), 'id' => $part->id] );
			$groups[]	= HtmlTag::create( 'div', $heading.$body, ['class' => 'accordion-group'] );
		}
		return HtmlTag::create( 'div', $groups, [
			'class'		=> 'accordion',
			'id'		=> $this->id,
		] );
	}

	public function setId( int|string $id ): self
	{
		$this->id	= $id;
		return $this;
	}

	public function setOpen( $ids ): self
	{
		if( !is_array( $ids ) )
			$ids	= [$ids];
		if( $this->singleOpen )
			$ids	= array_slice( $ids, 0, 1 );
		$this->open	= $ids;
		return $this;
	}

	public function setSingleOpen( bool $openOnlyOneAtATime ): self
	{
		$this->singleOpen	= $openOnlyOneAtATime;
		return $this;
	}
}
