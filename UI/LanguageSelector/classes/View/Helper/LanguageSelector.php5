<?php
class View_Helper_LanguageSelector extends CMF_Hydrogen_View_Helper_Abstract{

	protected $mode	= 0;

	const MODE_SELECT		= 0;
	const MODE_DROPDOWN		= 1;

	public function __construct( $env ){
		$this->setEnv( $env );
		$this->language		= $this->env->getLanguage();
		$this->languages	= $this->language->getLanguages();
		$this->current		= $this->language->getLanguage();
		$this->words		= $this->language->getWords( 'language' );
		$this->labels		= $this->words['languages'];

		$path				= $this->env->getRequest()->get( '__path' );			//  @todo use path key from App or Env
		$this->path			= rtrim( $path ? $path : "./", "/" )."/";
	}

	public function render(){
		if( count( $this->languages ) < 2 )
			return "";
		if( $this->mode === self::MODE_DROPDOWN )
			return $this->renderDropDown();
		return $this->renderSelect();
	}

	protected function renderDropDown(){
		$list	= array();
		foreach( $this->languages as $entry ){
			if( isset( $this->labels[$entry] ) ){
				$icon	= UI_HTML_Tag::create( 'i', '', array(
					'class'	=> ( $entry == $this->current ) ? 'icon-ok' : 'icon-empty',
				) );
				$link	= UI_HTML_Tag::create( 'a', $icon.'&nbsp'.$this->labels[$entry], array(
					'href'	=> $this->path.'?switchLanguageTo='.$entry,
					'class'	=> 'language-selector-link active',
				) );
				$list[]	= UI_HTML_Tag::create( 'li', $link );
			}
		}
		$listMenu		= UI_HTML_Tag::create( 'ul', $list, array(
			'class'		=> 'dropdown-menu pull-right'
		) );

		$label			= $this->words['selector']['label'];
		$caret			= UI_HTML_Tag::create( 'span', '', array( 'class' => 'caret' ) );
		$buttonToggle	= UI_HTML_Tag::create( 'a', $label.'&nbsp;&nbsp;'.$caret, array(
			'class'			=> "btn btn-small dropdown-toggle language-selector-button",
			'data-toggle'	=> "dropdown",
			'href'			=> "#",
		) );
		$component		= UI_HTML_Tag::create( 'div', $buttonToggle.$listMenu, array(
			'class'		=> 'btn-group',
			'id'		=> 'language-selector',
		) );
		return $component;
	}

	protected function renderSelect(){
		$options	= array();
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

	public function setMode( $mode = 0 ){
		$this->mode	= (int) $mode;
	}

	static public function renderStatic( $env ){
		$helper = new View_Helper_LanguageSelector( $env );
		$config	= $env->getConfig()->getAll( 'module.ui_languageselector.', TRUE );

		$helper->setMode( View_Helper_LanguageSelector::MODE_SELECT );
		if( $config->get( 'mode' ) === "dropdown" )
			$helper->setMode( View_Helper_LanguageSelector::MODE_DROPDOWN );

		return $helper->render();
	}
}
