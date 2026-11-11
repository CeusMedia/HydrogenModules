<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Entity;

/**
 *	Entity for rejected API requests logged by the IP Check module.
 *	@category		Library
 *	@package		CeusMedia.HydrogenModules
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2026 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenModules
 */
class Entity_Log_API_Reject extends Entity
{
	public int|NULL $id					= NULL;
	public string $ip					= '';
	public int $ipVersion				= 4;
	public int $score					= 0;
	public string $reasons				= '';
	public string $requestMethod		= '';
	public string $requestUri			= '';
	public string|NULL $requestHeaders	= NULL;
	public string|NULL $postData		= NULL;
	public string $createdAt;
}
