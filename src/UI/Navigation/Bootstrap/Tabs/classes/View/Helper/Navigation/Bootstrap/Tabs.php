<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

class View_Helper_Navigation_Bootstrap_Tabs extends Abstraction
{
	public string $classList			= 'nav nav-tabs';
	public string $classItem			= '';
	public string $classItemActive		= 'active';
	public string $classItemDisabled	= 'disabled';
	public string $classLink			= '';
	public string $classLinkActive		= '';
	public string $classLinkDisabled	= '';

	protected array $tabs				= [];
	protected $current			= 0;
	protected string $basePath			= '';

	public function __construct( Environment $env, string $basePath = './' )
	{
		$this->setEnv( $env );
		$this->setBasePath( $basePath );
	}

	public function registerTab( string $url, string $label, int $priority = 5, bool $disabled = NULL ): self
	{
		$this->tabs[]	= (object) [
			'url'		=> $url,
			'label'		=> $label,
			'priority'	=> $priority,
			'disabled'	=> $disabled,
		];
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
			$link['class']	= $this->classLink ?: NULL;
			$item['class']	= $this->classItem ?: NULL;
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
			$link		= HtmlTag::create( 'a', $tab->label, $link );							//  render tab link
			$key		= (float) $tab->priority.'.'.str_pad( $nr, 2, '0', STR_PAD_LEFT );			//  generate order key
			$list[$key]	= HtmlTag::create( 'li', $link, $item );								//  enlist tab
		}
		ksort( $list );
		if( count( $list ) > 1 )																	//  more than 1 tab
			return HtmlTag::create( 'ul', $list, [											//  return rendered tab list
				'class'			=> $this->classList,
				'data-toggle'	=> $tab->url[0] == '#' ? 'tab' : NULL,
			] );
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
