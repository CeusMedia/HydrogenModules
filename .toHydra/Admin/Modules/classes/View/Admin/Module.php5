<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

class View_Admin_Module extends View{

	public function index(){
	}

	public function install(){
	}

	static public function formatLabel( $title ){
		if( is_object( $title ) && !empty( $title->title ) )
			$title	= $title->title;
		$parts	= explode( ': ', $title );
		$name	= '<span class="module-label-name">'.array_pop( $parts ).'</span>';
		foreach( $parts as $nr => $part )
			$parts[$nr]	= '<span class="module-label-prefix">'.$part.'</span>';
		array_push( $parts, $name );
		$parts	= implode( '<span class="module-label-separator">:</span>', $parts );
		return '<div class="module-label">'.$parts.'</div>';
	}

	protected function renderModuleButton( $module, $url, $class = 'module not-button' ){
		$class		.= ' module-'.( empty( $module->isInstalled ) ? 'not-' : '' ).'installed';
		$image		= '';
		if( !empty( $module->icon ) )
			$image	= HtmlElements::Image( $module->icon, htmlentities( $module->title, ENT_QUOTES, 'UTF-8' ) );
		$icon	= HtmlTag::create( 'div', $image, array( 'class' => 'module-icon' ) );
		$title	= HtmlTag::create( 'div', self::formatLabel( $module->title ), array( 'class' => 'module-title' ) );
		$desc	= explode( '<br />', nl2br( $module->description ) );
		$desc	= array_shift( $desc );
		$desc	= HtmlTag::create( 'div', $desc, array( 'class' => 'module-desc' ) );
		$click	= 'document.location.href=\''.$url.$module->id.'\';';
		return HtmlTag::create( 'div', $icon.$title.$desc, array( 'class' => $class, 'onclick' => $click ) );
	}

	/**
	 *	Renders module overview containing large module buttons, sectioned by categories.
	 *	The URL of each module button relates the view action of the current controller.
	 *	@access		protected
	 *	@param		array		$modules		Map of all modules
	 *	@param		array		$categories		Map of all category labels
	 *	@param		array		$filters		Map of filters to apply on overview, example: array( 'type' => array( Model_Module::TYPE_CUSTOM ) )
	 *	@return		string		Rendered HTML of section modules.
	 */
	protected function renderModuleSections( $modules, $categories, $filters = [] ){
		$listSections	= [];
		foreach( $categories as $categoryId => $category ){
			$listModules	= [];
			$url			= './'.$this->controller.'/view/';
			foreach( $modules as $module ){
				if( $module->category != $categoryId )
						continue;
				if( array_key_exists( 'source', $filters ) )
					if( !in_array( $module->source, $filters['source'] ) )
						continue;
				if( array_key_exists( 'type', $filters ) )
					if( !in_array( $module->type, $filters['type'] ) )
						continue;
				$image		= '';
				if( !empty( $module->icon ) )
					$image	= HtmlElements::Image( $module->icon, htmlentities( $module->title, ENT_QUOTES, 'UTF-8' ) );
				$listModules[]	= $this->renderModuleButton( $module, $url );
			}
			$listModules	= join( $listModules );
			if( $listModules )
				$listSections[]	= '<fieldset><legend>'.$category.'</legend>'.$listModules.'</fieldset>';
		}
		return join( $listSections );
	}

	public function renderRelatedModulesList( $allModules, $relatedModules, $url = NULL, $listClass = NULL ){
		$words	= $this->env->getLanguage()->getWords( 'admin/module' );
		$list	= [];
		foreach( $relatedModules as $relatedModuleId => $status ){
			$alt	= $words['status-alt'][$status];
			$label	= $relatedModuleId;
			if( isset( $allModules[$relatedModuleId] ) ){
				$relatedModule	= $allModules[$relatedModuleId];
				$desc	= explode( '<br />', nl2br( $relatedModule->description ) );
				$attr	= array( 'title' => htmlentities( array_shift( $desc ), ENT_QUOTES, 'UTF-8' ) );
				$label	= HtmlTag::create( 'acronym', $relatedModule->title, $attr );
				if( $url ){
					$attr['href']	= $url.$relatedModuleId;
					$label	= HtmlTag::create( 'a', $relatedModule->title, $attr );
				}
			}
			$class	= 'icon module module-status-'.$status;
			$label	= HtmlTag::create( 'span', $label, array( 'class' => $class, 'title' => $alt ) );
			$list[]	= HtmlElements::ListItem( $label, 1 );
		}
		return HtmlElements::unorderedList( $list, 1, array( 'class' => $listClass ) );
	}

	public function showRelationGraph(){
		$tempFile	= tempnam( sys_get_temp_dir(), 'CMF' );
		try{
			$graph	= $this->getData( 'graph' );
			@exec( "dot -V", $results, $code );
			if( $code == 127 )
				throw new RuntimeException( 'Missing graphViz' );
			FS_File_Writer::save( $tempFile, $graph );
			exec( 'dot -O -Tpng '.$tempFile );
			unlink( $tempFile );
		}
		catch( Exception $e ){
			new UI_Image_Error( $e->getMessage() );
			exit;
		}
		$tempFile	.= '.png';
		$image		= FS_File_Reader::load( $tempFile );
		@unlink( $tempFile );
		header( 'Content-type: image/png' );
		print( $image );
		exit;
	}

	public function view(){
	}
}
?>
