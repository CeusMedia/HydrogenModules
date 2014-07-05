<?php
class View_Helper_Navigation_Bootstrap_Tabs extends CMF_Hydrogen_View_Helper_Abstract{

	protected $tabs	= array();
	public $classList			= 'nav nav-tabs';
	public $classItem			= '';
	public $classItemActive		= 'active';
	public $classItemDisabled	= 'disabled';
	public $classLink			= '';
	public $classLinkActive		= '';
	public $classLinkDisabled	= '';
	protected $basePath			= '';

	public function __construct( $env, $basePath = './' ){
		$this->setEnv( $env );
		$this->setBasePath( $basePath );
	}

	public function registerTab( $url, $label, $priority = 5, $disabled = NULL ){
		$this->tabs[]	= (object) array(
			'url'		=> $url,
			'label'		=> $label,
			'priority'	=> $priority,
			'disabled'	=> $disabled,
		);
	}

	public function renderTabs( $current = 0 ){
		$list	= array();																			//  prepare empty list
		foreach( $this->tabs as $nr => $tab ){														//  iterate registered tabs
			$link	= array();
			$item	= array();
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
			$link		= UI_HTML_Tag::create( 'a', $tab->label, $link );							//  render tab link
			$key		= (float) $tab->priority.'.'.str_pad( $nr, 2, '0', STR_PAD_LEFT );			//  generate order key
			$list[$key]	= UI_HTML_Tag::create( 'li', $link, $item );								//  enlist tab
		}
		if( count( $list ) > 1 )																	//  more than 1 tab
			return UI_HTML_Tag::create( 'ul', $list, array( 'class' => $this->classList ) );		//  return rendered tab list
	}

	public function setBasePath( $path ){
		$this->basePath	= $path;
	}
}
?>
