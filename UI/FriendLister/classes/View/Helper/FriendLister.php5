<?php
/**
 *	Renders list of linked friend sites.
 *
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2021 Ceus Media <https://ceusmedia.de/>
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

/**
 *	Renders list of linked friend sites.
 *
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2021 Ceus Media <https://ceusmedia.de/>
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 */
class View_Helper_FriendLister extends Abstraction
{
	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		Environment		$env		Environment object
	 *	@return		void
	 */
	public function __construct( Environment $env )
	{
		$this->setEnv( $env );
	}

	/**
	 *	Renders and returns list for given XML file.
	 *	@access		public
	 *	@return		string
	 *	@throws		RuntimeException if XML file is not existing
	 */
	public function build(): string
	{
		$config		= $this->env->getConfig();														//
		$c			= new Dictionary( $config->getAll( 'module.ui_friendlister.' ) );		//
		if( !file_exists( $c->get( 'file' ) ) )														//
			throw new RuntimeException( 'File "'.$c->get( 'file' ).'" is not existing' );			//

		$list	= [];																			//
		$xml	= XML_ElementReader::readFile( $c->get( 'file' ) );									//
		foreach( $xml->friend as $item ){															//
			if( $item->hasAttribute( 'disabled' ) )													//
				if( $item->getAttribute( 'disabled' ) == "yes" )									//
					continue;																		//
			$icon	= $this->renderIcon( $item );													//
			$icon	= HtmlTag::create( 'span', $icon, array( 'class' => 'user-icon' ) );		//
			$name	= HtmlTag::create( 'span', $item->name, array( 'class' => 'user-name' ) );	//
			$attr	= array(
				'href'	=> (string) $item->link,
				'title'	=> addslashes( (string) $item->title ),
			);
			$link	= HtmlTag::create( 'a', $icon.$name, $attr );								//
			$label	= HtmlTag::create( 'span', $link, array( 'class' => 'user-label' ) );		//
			$list[]	= HtmlTag::create( 'li', $label, array( 'class' => 'user-item' ) );			//
		}
		$c->get( 'shuffle' ) ? shuffle( $list ) : NULL;												//
		return HtmlTag::create( 'ul', $list, array( 'class' => $c->get( 'class.list' ) ) );		//
	}

	/**
	 *	Renders and returns list for given XML file statically.
	 *	@access		public
	 *	@param		Environment		$env		Environment object
	 *	@return		string
	 */
	public static function render( Environment $env ): string
	{
		$helper	= new View_Helper_FriendLister( $env );
		return $helper->build();
	}

	/**
	 *	Renders friend icon if possible.
	 *	@access		protected
	 *	@param		XML_Element		$friend		Element of XML file to get icon for
	 *	@return		string|NULL
	 */
	protected function renderIcon( $friend ): string
	{
		$config		= $this->env->getConfig();														//
		$icon		= $config->get( 'module.ui_friendlister.icon.male' );							//
		if( $friend->hasAttribute( 'gender' ) && $friend->getAttribute( 'gender' ) == "f" )			//
			$icon	= $config->get( 'module.ui_friendlister.icon.female' );							//
		if( strlen( (string) $friend->icon ) )														//
			$icon	= (string) $friend->icon;														//
		if( !$icon )
			return '&nbsp;';
		$attributes	= array( 'src' => $icon, 'alt' => (string) $friend->name );						//
		return HtmlTag::create( 'img', NULL, $attributes );										//
	}
}
