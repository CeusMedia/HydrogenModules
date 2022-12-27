<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Accordion{

	protected $id			= NULL;
	protected $open			= [];
	protected $singleOpen	= FALSE;
	protected $parts		= [];

	public function __construct( $id ){
		$this->setId( $id );
	}

	public function add( $id, $title, $content, $class = NULL ){
		$this->parts[]	= (object) array(
			'id'		=> $id,
			'title'		=> $title,
			'content'	=> $content,
			'class'		=> $class,
		);
	}

	public function setSingleOpen( $openOnlyOneAtATime ){
		$this->singleOpen	= (bool) $openOnlyOneAtATime;
	}

	public function setId( $id ){
		$this->id	= $id;
	}

	public function setOpen( $ids ){
		if( !is_array( $ids ) )
			$ids	= [$ids];
		if( $this->singleOpen )
			$ids	= array_slice( $ids, 0, 1 );
		$this->open	= $ids;
	}

	public function render(){
		$groups		= [];
		foreach( $this->parts as $part ){
			$class		= 'accordion-toggle '.( $part->class ? $part->class : '' );
			$link		= HtmlTag::create( 'a', $part->title, array(
				'href'			=> $_SERVER['REQUEST_URI'].'#'.$part->id,
				'class'			=> $class,
				'data-toggle'	=> 'collapse',
				'data-parent'	=> $this->singleOpen ? '#'.$this->id : NULL,
				'data-target'	=> '#'.$part->id
			) );
			$content	= HtmlTag::create( 'div', $part->content, ['class' => 'accordion-inner'] );
			$isOpen		= in_array( $part->id, $this->open );
			$heading	= HtmlTag::create( 'div', $link, ['class' => 'accordion-heading'] );
			$body		= HtmlTag::create( 'div', $content, ['class' => 'accordion-body collapse'.( $isOpen ? ' in' : '' ), 'id' => $part->id] );
			$groups[]	= HtmlTag::create( 'div', $heading.$body, ['class' => 'accordion-group'] );
		}
		return HtmlTag::create( 'div', $groups, array(
			'class'		=> 'accordion',
			'id'		=> $this->id,
		) );
	}
}
