<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Image_Slider
{
	protected Environment $env;

	protected Dictionary $config;
	protected Model_Image_Slider $modelSlider;
	protected Model_Image_Slide $modelSlide;
	protected string $basePath;
	protected string $selectorPrefix		= 'imageSlider-';

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment		$env			Environment object
	 */
	public function __construct( Environment $env )
	{
		$this->env			= $env;
		$this->modelSlider	= new Model_Image_Slider( $env );
		$this->modelSlide	= new Model_Image_Slide( $env );

		$config	= $this->env->getConfig();
		$path	= $config->get( 'path.images' ) ?: 'images/';
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
	 *	@param		int|string		$sliderId		ID of slider to render
	 *	@return		string
	 *	@todo		reactivate scaling after fixing it
	 */
	public function render( int|string $sliderId ): string
	{
		$slider		= $this->modelSlider->get( $sliderId );
		if( !$slider )
			throw new InvalidArgumentException( 'Invalid slider ID: '.$sliderId );
		$this->modelSlider->edit( $sliderId, ['views' => $slider->views + 1] );
		$conditions	= [
			'sliderId'	=> $sliderId,
			'status'	=> 1,
		];
		$slider->slides	= $this->modelSlide->getAll( $conditions, ['rank' => 'ASC'] );
		if( $slider->randomOrder )
			shuffle( $slider->slides );

		$config	= [
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
		];
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
		return HtmlTag::create( 'div', $images, $attr );
	}

	public static function renderStatic( Environment $env, int|string $sliderId ): string
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
	public function setBasePath( string $path ): self
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
	public function setSelectorPrefix( string $prefix ): self
	{
		$this->selectorPrefix	= $prefix;
		return $this;
	}

	//  --  PROTECTED  --  //

	protected function renderButtons( object $slider ): string
	{
		if( !$slider->showButtons || count( $slider->slides ) < 2 )
			return '';
		$buttonPrev	= HtmlTag::create( 'button', '‹', [
			'type'	=> 'button',
			'class'	=> $this->selectorPrefix.'button-prev'
		] );
		$buttonNext	= HtmlTag::create( 'button', '›', [
			'type'	=> 'button',
			'class'	=> $this->selectorPrefix.'button-next'
		] );
		return $buttonPrev.$buttonNext;
	}

	protected function renderDots( object $slider ): string
	{
		if( !$slider->showDots || count( $slider->slides ) < 2 )
			return '';
		$dots		= [];
		$number		= 0;
		foreach( $slider->slides as $slide ){
			$attr	= [
				'id'		=> $this->selectorPrefix.$slider->sliderId.'-dot-'.$number,
				'class'		=> $this->selectorPrefix.'dot',
				'data-nr'	=> $number,
			];
			if( $slide->title )
				$attr['title']	= $slide->title;
			if( !$number )
				$attr['class']	= $attr['class'].' active';
			$dots[]	= HtmlTag::create( 'div', NULL, $attr );
			$number	+= 1;
		}
		$attr		= [
			'id'	=> $this->selectorPrefix.$slider->sliderId.'-dots',
			'class'	=> $this->selectorPrefix.'dots',
		];
		return HtmlTag::create( 'div', join( $dots ), $attr );
	}

	protected function renderSlides( object $slider ): string
	{
		$list		= [];
		$number		= 0;
		$width		= $slider->width;
		$height		= $slider->height;
		foreach( $slider->slides as $slide ){
			$imageFile	= $this->basePath.$slider->path.$slide->source;
			$attr	= ['src' => $imageFile];
			if( $slide->title ){
				$attr['title']		= $slide->title;
				$attr['alt']		= $slide->title;
				$attr['data-link']	= $slide->link;
			}
			$image	= HtmlTag::create( 'img', NULL, $attr );
			if( $slide->link && strlen( trim( $slide->link ) ) ){
				$attr	= [
					'href'	=> $slide->link,
					'title'	=> $slide->title
				];
				$image	= HtmlTag::create( 'a', $image, $attr );
			}
			$attr	= [
				'id'	=> $slider->sliderId.'-slide-'.$number,
				'class'	=> $this->selectorPrefix.'slide',
			];
			$content	= "";
			if( trim( $slide->content ) )
				$content	= HtmlTag::create( 'div', $slide->content, [
					'class'	=> $this->selectorPrefix.'slide-content'
				] );
			$item	= HtmlTag::create( 'div', $image.$content, $attr );
			$list[]	= $item;
			$number	+= 1;
		}
		$list	= join( $list );
		$label	= "";
		if( $slider->showTitle ){
			$slide	= $slider->slides[0];
			$title	= $slide->title;
			if( $slide->link && strlen( trim( $slide->link ) ) )
				$title	= HtmlTag::create( 'a', $title, ['href' => $slide->link] );
			$attr	= ['class' => $this->selectorPrefix.'label'];
			$label	= HtmlTag::create( 'div', $title, $attr );
			$attr	= ['class' => $this->selectorPrefix.'layer'];
			$list	.= HtmlTag::create( 'div', $label, $attr );
		}
		$attr	= ['class' => $this->selectorPrefix.'slides'];
		return HtmlTag::create( 'div', $list, $attr );
	}
}
