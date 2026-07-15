<?php
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

use CeusMedia\HydrogenFramework\View;

class Mail_Work_Newsletter_Theme extends Mail_Abstract
{
	/**
	 *	@return		self
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function generate(): static
	{
		$this->setHtml( $this->renderHtmlBody() );
		return $this;
	}

	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	protected function renderHtmlBody(): string
	{
		$data		= $this->data;
		$themeId	= $data['themeId'];
		$logic		= new Logic_Newsletter( $this->env );
		$path		= $logic->getNewsletterThemesPath();
		$model		= new Model_Newsletter_Theme( $this->env, $path );

		/** @var ?Entity_Newsletter_Theme $theme */
		$theme		= $model->get( $themeId );
		$css		= FileReader::load( $path.$theme->folder.'/template.css' );
		$html		= FileReader::load( $path.$theme->folder.'/template.html' );

		$view		= new View( $this->env );
		$imprint	= $view->loadContentFile( 'html/work/newsletter/template/imprint.txt' );
		$imprint	= preg_replace( "/(https?:\/\/(\S+)\/?)/", '<a href="\\1">\\2</a>', $imprint );
		$imprint	= preg_replace( "/(\S+@\S+)/", '<a href="mailto:\\1">\\1</a>', $imprint );
		$imprint	= preg_replace( "/\n/", "<br/>", $imprint );
		$html		= str_replace( "[#imprint#]", $imprint, $html );
/*		$words		= $this->getWords( 'preview' );
		$words['title']	= sprintf( $words['title'], $theme->title );
		foreach( $words as $key => $value )
			$html	= str_replace( "[#".$key."#]", $value, $html );*/
		$html	= preg_replace( "/\[#.+#\]/", '', $html );

		foreach( explode( ',', $theme->styles ) as $style )
			if( '' !== trim( $style ) )
				$this->page->addStylesheet( (string) $style );
		$this->page->addHead( HtmlTag::create( 'style', $css ) );
		$this->data['mailTemplateId']	= $theme->mailTemplateId;

		return $html;
	}
}
