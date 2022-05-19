<?php
class View_Helper_LanguageSelector extends CMF_Hydrogen_View_Helper_Abstract
{
	protected $dropdownAlign	= 'right';
	protected $mode				= 0;
	protected $language;
	protected $languages;
	protected $current;
	protected $words;
	protected $labels;
	protected $path;

	const MODE_SELECT			= 0;
	const MODE_DROPDOWN			= 1;

	public function __construct( CMF_Hydrogen_Environment $env )
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
		$this->path				= rtrim( $path ? $path : "./", "/" )."/";
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

	public static function renderStatic( CMF_Hydrogen_Environment $env ): string
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
		if( !in_array( $align, array( 'left', 'right' ) ) )
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
				$icon	= UI_HTML_Tag::create( 'i', '', array(
					'class'	=> ( $entry == $this->current ) ? 'icon-ok' : 'icon-empty',
				) );
				$link	= UI_HTML_Tag::create( 'a', '<%?OPTIONALICON%>'.$icon.'&nbsp;'.$this->labels[$entry], array(
					'href'	=> $this->path.'?switchLanguageTo='.$entry,
					'class'	=> 'language-selector-link active',
				) );
				$eventpayload = (object)( array( "label" => $link, "language" => $entry) );
				$this->env->getCaptain()->callHook('LanguageSelector', 'queryLanguageDecorator', $this, $eventpayload );
				$link   = $eventpayload->label;
				$list[]	= UI_HTML_Tag::create( 'li', $link );
			}
		}
		$listMenu		= UI_HTML_Tag::create( 'ul', $list, array(
			'class'		=> 'dropdown-menu pull-'.$this->dropdownAlign,
		) );

		$label			= '<%?OPTIONALICON%>'.$this->words['selector']['label'];
		$eventpayload 		= (object)( array("label" => $label, "language" => $this->language->GetLanguage() ) );
		$this->env->getCaptain()->callHook('LanguageSelector','queryLanguageDecorator', $this, $eventpayload);
		$label			= $eventpayload->label;
/* TODO Move to own Module and add support here for themeable icons
		$flagimgpath		= "themes/common/img/".$this->language->GetLanguage().".png";
		$flagimg		= UI_HTML_Tag::create( 'img' , '', array( 'src' => $flagimgpath , 'style' =>'height:1em') );
		$flagimg		.= '&nbsp;'
*/
		$caret			= UI_HTML_Tag::create( 'span', '', array( 'class' => 'caret' ) );
		$buttonToggle	= UI_HTML_Tag::create( 'a', $label.'&nbsp;&nbsp;'.$caret, array(
			'class'			=> "btn btn-small dropdown-toggle language-selector-button",
			'data-toggle'	=> "dropdown",
			'href'			=> "#",
		) );
		$component		= UI_HTML_Tag::create( 'div', array(
			$buttonToggle,
			$listMenu
		), array(
			'class'		=> 'btn-group',
			'id'		=> 'language-selector',
		) );
		return $component;
	}

	protected function renderSelect(): string
	{
		$options	= [];
		foreach( $this->languages as $entry )
			if( isset( $this->labels[$entry] ) )
				$options[$entry]	= $this->labels[$entry];
		$options	= UI_HTML_Elements::Options( $options, $this->current );

		$uri	= $this->path.'?switchLanguageTo=';
		$select	= UI_HTML_Tag::create( 'select', $options, array(
			'onchange'	=> "document.location.href='".$uri."'+this.value;",
			'class'		=> 'span12',
			'id'		=> 'language-selector-input',
		) );
		return $select;
	}
}
