<?php
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Navigation_Bootstrap_Tabs extends CMF_Hydrogen_View_Helper_Abstract
{
	public $classList			= 'nav nav-tabs';
	public $classItem			= '';
	public $classItemActive		= 'active';
	public $classItemDisabled	= 'disabled';
	public $classLink			= '';
	public $classLinkActive		= '';
	public $classLinkDisabled	= '';

	protected $tabs				= [];
	protected $current			= 0;
	protected $basePath			= '';

	public function __construct( Environment $env, string $basePath = './' )
	{
		$this->setEnv( $env );
		$this->setBasePath( $basePath );
	}

	public function registerTab( string $url, string $label, int $priority = 5, bool $disabled = NULL ): self
	{
		$this->tabs[]	= (object) array(
			'url'		=> $url,
			'label'		=> $label,
			'priority'	=> $priority,
			'disabled'	=> $disabled,
		);
		return $this;
	}

	public function render(): string
	{
		return $this->renderTabs( $this->current );
	}

	public function renderTabs( $current = 0 ): string
	{
		$list	= [];																			//  prepare empty list
		foreach( $this->tabs as $nr => $tab ){														//  iterate registered tabs
			$link	= [];
			$item	= [];
			$link['class']	= $this->classLink ? $this->classLink : NULL;
			$item['class']	= $this->classItem ? $this->classItem : NULL;
			$isActive	= $nr === $current || ( $tab->url === $current ) || !$nr && !$current;		//  is tab active ?
			if( $tab->disabled ){																	//  if tab is disabled
				$item['class']	.= $this->classItemDisabled ? ' '.$this->classItemDisabled : '';	//
				$link['class']	.= $this->classLinkDisabled ? ' '.$this->classLinkDisabled : '';	//
			}
			else{
				$link['href']	= $this->basePath.$tab->url;										//
				if( $isActive ){																	//
					$item['class']	.= $this->classItemActive ? ' '.$this->classItemActive : '';	//
					$link['class']	.= $this->classLinkActive ? ' '.$this->classLinkActive : '';	//
				}
			}
			$link['class']	.= ' nav-link';
			$item['class']	.= ' nav-item';
			$link		= UI_HTML_Tag::create( 'a', $tab->label, $link );							//  render tab link
			$key		= (float) $tab->priority.'.'.str_pad( $nr, 2, '0', STR_PAD_LEFT );			//  generate order key
			$list[$key]	= UI_HTML_Tag::create( 'li', $link, $item );								//  enlist tab
		}
		ksort( $list );
		if( count( $list ) > 1 )																	//  more than 1 tab
			return UI_HTML_Tag::create( 'ul', $list, array(											//  return rendered tab list
				'class'			=> $this->classList,
				'data-toggle'	=> $tab->url[0] == '#' ? 'tab' : NULL,
			) );
		return '';
	}

	public function setBasePath( string $path ): self
	{
		$this->basePath	= $path;
		return $this;
	}

	public function setCurrent( $current ): self
	{
		$this->current		= $current;
		return $this;
	}
}
