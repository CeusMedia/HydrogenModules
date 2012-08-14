<?php
/**
 *	Renders list of linked friend sites. 
 *
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media <http://ceusmedia.de/>
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@since			01.08.2012
 *	@version		$Id$
 */
/**
 *	Renders list of linked friend sites. 
 *
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media <http://ceusmedia.de/>
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@since			01.08.2012
 *	@version		$Id$
 */
class View_Helper_FriendLister extends CMF_Hydrogen_View_Helper_Abstract{
	
	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env		Environment object
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->setEnv( $env );
	}
		
	/**
	 *	Renders and returns list for given XML file.
	 *	@access		public
	 *	@return		string
	 *	@throws		RuntimeException if XML file is not existing
	 */
	public function build(){
		$config		= $this->env->getConfig();														//  
		$c			= new ADT_List_Dictionary( $config->getAll( 'module.ui_friendlister.' ) );		//  
		if( !file_exists( $c->get( 'file' ) ) )														//  
			throw new RuntimeException( 'File "'.$c->get( 'file' ).'" is not existing' );			//  

		$list	= array();																			//  
		$xml	= XML_ElementReader::readFile( $c->get( 'file' ) );									//  
		foreach( $xml->friend as $item ){															//  
			if( $item->hasAttribute( 'disabled' ) )													//  
				if( $item->getAttribute( 'disabled' ) == "yes" )									//  
					continue;																		//  
			$icon	= $this->renderIcon( $item );													//  
			$icon	= UI_HTML_Tag::create( 'span', $icon, array( 'class' => 'user-icon' ) );		//  
			$name	= UI_HTML_Tag::create( 'span', $item->name, array( 'class' => 'user-name' ) );	//  
			$attr	= array(
				'href'	=> (string) $item->link,
				'title'	=> addslashes( (string) $item->title ),
			);
			$link	= UI_HTML_Tag::create( 'a', $icon.$name, $attr );								//  
			$label	= UI_HTML_Tag::create( 'span', $link, array( 'class' => 'user-label' ) );		//  
			$list[]	= UI_HTML_Tag::create( 'li', $label, array( 'class' => 'user-item' ) );			//  
		}
		$c->get( 'shuffle' ) ? shuffle( $list ) : NULL;												//  
		return UI_HTML_Tag::create( 'ul', $list, array( 'class' => $c->get( 'class.list' ) ) );		//  
	}

	/**
	 *	Renders and returns list for given XML file statically.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env		Environment object
	 *	@return		string
	 */
	public function render( CMF_Hydrogen_Environment_Abstract $env ){
		$helper	= new View_Helper_FriendLister( $env );
		return $helper->build();
	}

	/**
	 *	Renders friend icon if possible.
	 *	@access		protected
	 *	@param		XML_Element		$friend		Element of XML file to get icon for
	 *	@return		string|NULL 
	 */
	protected function renderIcon( $friend ){
		$config		= $this->env->getConfig();														//  
		$icon		= $config->get( 'module.ui_friendlister.icon.male' );							//  
		if( $friend->hasAttribute( 'gender' ) && $friend->getAttribute( 'gender' ) == "f" )			//  
			$icon	= $config->get( 'module.ui_friendlister.icon.female' );							//  
		if( strlen( (string) $friend->icon ) )														//  
			$icon	= (string) $friend->icon;														//  
		if( !$icon ) 
			return '&nbsp;';
		$attributes	= array( 'src' => $icon, 'alt' => $friend->name );								//  
		return UI_HTML_Tag::create( 'img', NULL, $attributes );										//  
	}
}
?>