<?php

use CeusMedia\Common\UI\HTML\Tabs as HtmlTabs;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

class View_Helper_Module_Details extends Abstraction
{
	/** @var		Logic_Module		$logic			Module logic instance */
	protected $logic;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment			$env		Environment object
	 *	@param		Logic_Module		$logic		Module logic instance
	 *	@return		void
	 */
	public function __construct( Environment $env/*, Logic_Module $logic*/ ){
		$this->setEnv( $env );
//		$this->logic	= $logic;
	}

	public function render( $module, $modules, $view ){
		$moduleId	= $module->id;

//		$module->neededModules		= $this->logic->model->getNeededModulesWithStatus( $moduleId );
//		$module->neededByModules	= $this->logic->model->getNeedingModulesWithStatus( $moduleId );
//		$module->supportedModules	= $this->logic->model->getSupportedModulesWithStatus( $moduleId );
//		$module->supportedByModules	= $this->logic->model->getSupportingModulesWithStatus( $moduleId );

//		$words	= $this->getWords( 'view', 'admin/module/viewer' );
		$words	= $this->env->getLanguage()->getWords( 'admin/module/viewer' );
//		print_m( $words );
//		die;

		HtmlTabs::$version	= 4;
		$tabs	= new HtmlTabs();
		$activeTab	= 0;

		$mapTabs	= [
			'resources'		=> 'tabResources',
			'config'		=> 'tabConfiguration',
			'database'		=> 'tabDatabase',
		//	'links'			=> 'tabLinks',
			'relations'		=> 'tabRelations',
//			'instances'		=> 'tabInstances',
		];

		$nr			= 0;
		$disabled	= [];
		foreach( $mapTabs as $key => $tabLabel ){
			$count		= 0;
			$template	= 'templates/admin/module/details/'.$key.'.php';
			$content	= file_exists( $template ) ? require_once( $template ) : 'Template "'.$template.'" missing.';
			$label		= $words['details'][$tabLabel];
			$label		.= $count ? ' <small>('.$count.')</small>' : '';
			if( $key != 'general' && !$count ){
				$disabled[]	= $nr;
				if( $activeTab == $nr )
					$activeTab++;
			}
			$tabs->addTab( $label, $content );
			$nr++;
		}

		$options	= [
			'active'	=> $activeTab,
			'disabled'	=> $disabled
		];
		$this->env->page->js->addScript( '$(document).ready(function(){'.$tabs->buildScript( '#tabs-module', $options ).'});' );
		return $tabs->buildTabs( 'tabs-module' );
	}
}
?>
