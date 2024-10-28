<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Language;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

class View_Helper_LanguageSelector extends Abstraction
{
	protected string $dropdownAlign	= 'right';
	protected int $mode				= 0;
	protected Language $language;
	protected array $languages;
	protected string $current;
	protected array $words;
	protected array $labels;
	protected string $path;

	public const MODE_SELECT		= 0;
	public const MODE_DROPDOWN		= 1;

	public function __construct( Environment $env )
	{
		$this->setEnv( $env );
		$this->language			= $this->env->getLanguage();
		$this->languages		= $this->language->getLanguages();
		$this->current			= $this->language->getLanguage();
		$this->words			= $this->language->getWords( 'language' );
		$this->labels			= $this->words['languages'];
		$this->mode				= (int) ( $this->env->getConfig()->get( 'module.ui_languageselector.mode' ) !== "select" );
//		$this->dropdownAlign	= $this->env->getConfig()->get( 'module.ui_languageselector.mode.dropdown.align' );
		$path					= $this->env->getRequest()->get( '__path' );			//  @todo use path key from App or Env
		$this->path				= rtrim( $path ?: "./", "/" )."/";
	}

	public function getMode(): int
	{
		return $this->mode;
	}

	public function render(): string
	{
		if( count( $this->languages ) < 2 )
			return "";
		if( $this->mode === self::MODE_DROPDOWN )
			return $this->renderDropDown();
		return $this->renderSelect();
	}

	public static function renderStatic( Environment $env ): string
	{
		$helper = new View_Helper_LanguageSelector( $env );
		$config	= $env->getConfig()->getAll( 'module.ui_languageselector.', TRUE );

		$helper->setMode( View_Helper_LanguageSelector::MODE_SELECT );
		if( $config->get( 'mode' ) === "dropdown" )
			$helper->setMode( View_Helper_LanguageSelector::MODE_DROPDOWN );

		return $helper->render();
	}

	public function setDropdownAlign( string $align = 'right' ): self
	{
		if( !in_array( $align, ['left', 'right'] ) )
			throw new InvalidArgumentException( 'Align must be left or right' );
		$this->dropdownAlign	= $align;
		return $this;
	}

	public function setMode( int $mode = 0 ): self
	{
		$this->mode	= $mode;
		return $this;
	}

	protected function renderDropDown(): string
	{
		$list	= [];
		foreach( $this->languages as $entry ){
			if( isset( $this->labels[$entry] ) ){
				$icon	= HtmlTag::create( 'i', '', [
					'class'	=> ( $entry == $this->current ) ? 'icon-ok' : 'icon-empty',
				] );
				$link	= HtmlTag::create( 'a', '<%?OPTIONALICON%>'.$icon.'&nbsp;'.$this->labels[$entry], [
					'href'	=> $this->path.'?switchLanguageTo='.$entry,
					'class'	=> 'language-selector-link active',
				] );
				$payload	= ["label" => $link, "language" => $entry];
				$this->env->getCaptain()->callHook('LanguageSelector', 'queryLanguageDecorator', $this, $payload );
				$link		= str_replace( '<%?OPTIONALICON%>', '', $payload['label'] );
				$list[]		= HtmlTag::create( 'li', $link );
			}
		}
		$listMenu		= HtmlTag::create( 'ul', $list, [
			'class'		=> 'dropdown-menu pull-'.$this->dropdownAlign,
		] );

		$label			= '<%?OPTIONALICON%>'.$this->words['selector']['label'];
		$payload 		= ["label" => $label, "language" => $this->language->GetLanguage()];
		$this->env->getCaptain()->callHook('LanguageSelector','queryLanguageDecorator', $this, $payload );
		$label			= str_replace( '<%?OPTIONALICON%>', '', $payload['label'] );
/* TODO Move to own Module and add support here for themeable icons
		$flagimgpath		= "themes/common/img/".$this->language->GetLanguage().".png";
		$flagimg		= HtmlTag::create( 'img' , '', ['src' => $flagimgpath , 'style' =>'height:1em'] );
		$flagimg		.= '&nbsp;'
*/
		$caret			= HtmlTag::create( 'span', '', ['class' => 'caret'] );
		$buttonToggle	= HtmlTag::create( 'a', $label.'&nbsp;&nbsp;'.$caret, [
			'class'			=> "btn btn-small dropdown-toggle language-selector-button",
			'data-toggle'	=> "dropdown",
			'href'			=> "#",
		] );
		$component		= HtmlTag::create( 'div', [$buttonToggle, $listMenu], [
			'class'		=> 'btn-group',
			'id'		=> 'language-selector',
		] );
		return $component;
	}

	protected function renderSelect(): string
	{
		$options	= [];
		foreach( $this->languages as $entry )
			if( isset( $this->labels[$entry] ) )
				$options[$entry]	= $this->labels[$entry];
		$options	= HtmlElements::Options( $options, $this->current );

		$uri	= $this->path.'?switchLanguageTo=';
		return HtmlTag::create( 'select', $options, [
			'onchange'	=> "document.location.href='".$uri."'+this.value;",
			'class'		=> 'span12',
			'id'		=> 'language-selector-input',
		] );
	}
}
