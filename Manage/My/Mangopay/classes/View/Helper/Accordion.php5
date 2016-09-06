<?php

class View_Helper_Accordion{

	protected $id			= NULL;
	protected $open			= array();
	protected $singleOpen	= FALSE;
	protected $parts		= array();

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
			$ids	= array( $ids );
		if( $this->singleOpen )
			$ids	= array_slice( $ids, 0, 1 );
		$this->open	= $ids;
	}

	public function render(){
		$groups		= array();
		foreach( $this->parts as $part ){
			$class		= 'accordion-toggle '.( $part->class ? $part->class : '' );
			$link		= UI_HTML_Tag::create( 'a', $part->title, array(
				'href'			=> $_SERVER['REQUEST_URI'].'#'.$part->id,
				'class'			=> $class,
				'data-toggle'	=> 'collapse',
				'data-parent'	=> $this->singleOpen ? '#'.$this->id : NULL,
				'data-target'	=> '#'.$part->id
			) );
			$content	= UI_HTML_Tag::create( 'div', $part->content, array( 'class' => 'accordion-inner' ) );
			$isOpen		= in_array( $part->id, $this->open );
			$heading	= UI_HTML_Tag::create( 'div', $link, array( 'class' => 'accordion-heading' ) );
			$body		= UI_HTML_Tag::create( 'div', $content, array( 'class' => 'accordion-body collapse'.( $isOpen ? ' in' : '' ), 'id' => $part->id ) );
			$groups[]	= UI_HTML_Tag::create( 'div', $heading.$body, array( 'class' => 'accordion-group' ) );
		}
		return UI_HTML_Tag::create( 'div', $groups, array(
			'class'		=> 'accordion',
			'id'		=> $this->id,
		) );
	}
}
?>
