<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

class View_Helper_Navigation_Bootstrap_Position extends Abstraction
{
	protected $divider			= '&nbsp;/&nbsp;';
	protected $hasPageSupport	= FALSE;
	protected $moduleConfig;
	protected $moduleId			= "UI_Navigation_Bootstrap_Position";
	protected $labelHome		= "Home";

	public function __construct( Environment $env )
	{
		$this->setEnv( $env );
		$this->hasPageSupport	= $this->env->getModules()->has( 'Info_Pages' );
		$moduleConfigKey		= 'module.'.strtolower( $this->moduleId ).'.';
		$this->moduleConfig		= $this->env->getConfig()->getAll( $moduleConfigKey, TRUE );
	}

	public function render(): string
	{
		if( !$this->moduleConfig->get( 'active' ) )
			return '';
		if( !$this->hasPageSupport){
			$this->env->getMessenger()->noteFailure( 'Module "UI:Navigation:Bootstrap:Position" needs module "Info:Pages".' );
			return '';
		}
		$itemList		= [];
		$model			= new Model_Menu( $this->env );
		$pageMap		= $model->getPageMap();
		if( ( $currentPage = $model->getCurrent() ) ){
			$path	= $currentPage->path;
			while( $path && isset( $pageMap[$path] ) && $page = $pageMap[$path] ){
				$item	= (object) [
					'label'		=> $page->label,
					'link'		=> NULL,
					'current'	=> FALSE,
				];
				if( $page->path === $currentPage->path ){
					$item->current = TRUE;
				}
				if( $page->type === "menu" ){
				}
				else if( $page->type === "item" ){
					if( !$item->current )
						$item->link = './'.$page->path;
				}
				array_unshift( $itemList, $item );
				$parts	= explode( "/", $path );
				$path	= implode( "/", array_slice( $parts, 0, count( $parts ) - 1 ) );
			}
			array_unshift( $itemList, (object) [
				'label'		=> $this->labelHome,
				'link'		=> './',
			] );
		}
		else {
			if( !$this->moduleConfig->get( 'showOnHome' ) )
				return '';
			array_unshift( $itemList, (object) [
				'label'		=> $this->labelHome,
				'link'		=> NULL,
			] );
		}
		$barList	= [];
		foreach( $itemList as $entry ){
			$label	= $entry->label;
			if( isset( $entry->link ) )
				$label	= HtmlTag::create( 'a', $label, ['href' => $entry->link] );
			$barList[]	= HtmlTag::create( 'span', $label, [
				'class'	=> 'position-bar-path-list-item',
			'href'	=> '#',
			] );
		}
		$divider	= HtmlTag::create( 'span', $this->divider, ['class' => 'position-bar-path-divider'] );
		$barList	= implode( $divider, $barList );
		$bar		= HtmlTag::create( 'span', $barList, ['class' => 'position-bar-path-list'] );
		$label		= HtmlTag::create( 'span', 'Position', ['class' => 'position-bar-label'] );

		$content	= HtmlTag::create( 'div', $label.$bar, ['class' => 'position-bar'] );
		return $content;
	}

	public function setHomeLabel( string $string ): self
	{
		$this->labelHome	= $string;
		return $this;
	}

	public function setPathDivider( string $string ): self
	{
		$this->divider		= $string;
		return $this;
	}
}
