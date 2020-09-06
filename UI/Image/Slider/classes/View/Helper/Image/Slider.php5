<?php
class View_Helper_Image_Slider
{
	public $config;
	public $slides;
	public $options;
	protected $env;
	protected $modelSlider;
	protected $modelSlide;
	protected $basePath;
	protected $selectorPrefix		= 'imageSlider-';

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment		$env			Environment object
	 */
	public function __construct( CMF_Hydrogen_Environment $env )
	{
		$this->env			= $env;
		$this->modelSlider	= new Model_Image_Slider( $env );
		$this->modelSlide	= new Model_Image_Slide( $env );

		$config	= $this->env->getConfig();
		$path	= $config->get( 'path.images' ) ? $config->get( 'path.images' ) : 'images/';
		if( $this->env->getModules()->has( 'Resource_Frontend' ) ){
			$frontend	= Logic_Frontend::getInstance( $this->env );
			$path		= $frontend->getPath( 'images' );
		}
		$path	.= $config->get( 'module.ui_image_slider.path' );
		$this->setBasePath( $path );
	}

	/**
	 *	Returns rendered HTML for specified slider.
	 *	@access		public
	 *	@param		integer		$sliderId		ID of slider to render
	 *	@return		string
	 *	@todo		reactivate scaling after fixing it
	 */
	public function render( $sliderId )
	{
		$slider		= $this->modelSlider->get( $sliderId );
		if( !$slider )
			throw new InvalidArgumentException( 'Invalid slider ID: '.$sliderId );
		$this->modelSlider->edit( $sliderId, array( 'views' => $slider->views + 1 ) );
		$conditions	= array(
			'sliderId'	=> $sliderId,
			'status'	=> 1,
		);
		$slider->slides	= $this->modelSlide->getAll( $conditions, array( 'rank' => 'ASC' ) );
		if( $slider->randomOrder )
			shuffle( $slider->slides );

		$config	= array(
			'durationShow'	=> $slider->durationShow,
			'durationSlide'	=> $slider->durationSlide,
			'animation'		=> $slider->animation,
			'easing'		=> $slider->easing,
			'width'			=> $slider->width,
			'height'		=> $slider->height,
			'showDots'		=> $slider->showDots,
			'showButtons'	=> 1 || $slider->showButtons,
			'showTitle'		=> $slider->showTitle,
			'scaleToFit'	=> $slider->scaleToFit,
		);
		$script	= 'UI.Image.Slider.init('.$slider->sliderId.', '.  json_encode( $config ).');';
		$this->env->getPage()->js->addScriptOnReady( $script );

		$images		= $this->renderSlides( $slider );
		$images		.= $this->renderButtons( $slider );
		$images		.= $this->renderDots( $slider );
		$attr		= array(
			'id'			=> $this->selectorPrefix.$sliderId,
			'class'			=> $this->selectorPrefix.'container',
			'style'			=> 'width: '.$slider->width.'px; height: '.$slider->height.'px',
			'data-ratio'	=> round( $slider->height / $slider->width, 8 )
		);
		return UI_HTML_Tag::create( 'div', $images, $attr );
	}

	static public function renderStatic( CMF_Hydrogen_Environment $env, $sliderId )
	{
		$instance	= new self( $env );
		return $instance->render( $sliderId );
	}

	/**
	 *	Set absolute base path to all slider images.
	 *	@access		public
	 *	@param		string		$path		Absolute base path to all slider images
	 *	@return		self
	 */
	public function setBasePath( $path ): self
	{
		$this->basePath	= $path;
		if( !file_exists( $path ) )
			mkdir( $path );
		return $this;
	}

	/**
	 *	Set prefix for CSS classes and IDs used as selector by JavaScript and CSS.
	 *	@access		public
	 *	@param		string		$prefix		Prefix for CSS classes and IDs used as selector by JavaScript and CSS
	 *	@return		self
	 */
	public function setSelectorPrefix( $prefix ): self
	{
		$this->selectorPrefix	= $prefix;
		return $this;
	}

	//  --  PROTECTED  --  //

	protected function renderButtons( $slider )
	{
		if( !$slider->showButtons || count( $slider->slides ) < 2 )
			return;
		$buttonPrev	= UI_HTML_Tag::create( 'button', '‹', array(
			'type'	=> 'button',
			'class'	=> $this->selectorPrefix.'button-prev'
		) );
		$buttonNext	= UI_HTML_Tag::create( 'button', '›', array(
			'type'	=> 'button',
			'class'	=> $this->selectorPrefix.'button-next'
		) );
		return $buttonPrev.$buttonNext;
	}

	protected function renderDots( $slider )
	{
		if( !$slider->showDots || count( $slider->slides ) < 2 )
			return;
		$dots		= array();
		$number		= 0;
		foreach( $slider->slides as $slide ){
			$attr	= array(
				'id'		=> $this->selectorPrefix.$slider->sliderId.'-dot-'.$number,
				'class'		=> $this->selectorPrefix.'dot',
				'data-nr'	=> $number,
			);
			if( $slide->title )
				$attr['title']	= $slide->title;
			if( !$number )
				$attr['class']	= $attr['class'].' active';
			$dots[]	= UI_HTML_Tag::create( 'div', NULL, $attr );
			$number	+= 1;
		}
		$attr		= array(
			'id'	=> $this->selectorPrefix.$slider->sliderId.'-dots',
			'class'	=> $this->selectorPrefix.'dots',
		);
		return UI_HTML_Tag::create( 'div', join( $dots ), $attr );
	}

	protected function renderSlides( $slider )
	{
		$list		= array();
		$number		= 0;
		$width		= $slider->width;
		$height		= $slider->height;
		foreach( $slider->slides as $slide ){
			$imageFile	= $this->basePath.$slider->path.$slide->source;
			$attr	= array( 'src' => $imageFile );
			if( $slide->title ){
				$attr['title']		= $slide->title;
				$attr['alt']		= $slide->title;
				$attr['data-link']	= $slide->link;
			}
			$image	= UI_HTML_Tag::create( 'img', NULL, $attr );
			if( $slide->link && strlen( trim( $slide->link ) ) ){
				$attr	= array(
					'href'	=> $slide->link,
					'title'	=> $slide->title
				);
				$image	= UI_HTML_Tag::create( 'a', $image, $attr );
			}
			$attr	= array(
				'id'	=> $slider->sliderId.'-slide-'.$number,
				'class'	=> $this->selectorPrefix.'slide',
			);
			$content	= "";
			if( trim( $slide->content ) )
				$content	= UI_HTML_Tag::create( 'div', $slide->content, array(
					'class'	=> $this->selectorPrefix.'slide-content'
				) );
			$item	= UI_HTML_Tag::create( 'div', $image.$content, $attr );
			$list[]	= $item;
			$number	+= 1;
		}
		$list	= join( $list );
		$label	= "";
		if( $slider->showTitle ){
			$title	= $slider->slides[0]->title;
			if( $slide->link && strlen( trim( $slide->link ) ) )
				$title	= UI_HTML_Tag::create( 'a', $title, array( 'href' => $slide->link ) );
			$attr	= array( 'class' => $this->selectorPrefix.'label' );
			$label	= UI_HTML_Tag::create( 'div', $title, $attr );
			$attr	= array( 'class' => $this->selectorPrefix.'layer' );
			$list	.= UI_HTML_Tag::create( 'div', $label, $attr );
		}
		$attr	= array( 'class' => $this->selectorPrefix.'slides' );
		return UI_HTML_Tag::create( 'div', $list, $attr );
	}
}
