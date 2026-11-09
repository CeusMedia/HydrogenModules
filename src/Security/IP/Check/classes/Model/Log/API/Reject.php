<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Data Model for rejected API requests logged by the IP Check module.
 *
 *	@category		Library
 *	@package		CeusMedia.HydrogenModules
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2026 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenModules
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model for rejected API requests logged by the IP Check module.
 *	@category		Library
 *	@package		CeusMedia.HydrogenModules
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2026 Christian Würker (ceusmedia.de)
 *	@license		https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenModules
 */
class Model_Log_API_Reject extends Model
{
	protected string $name			= 'log_api_rejects';

	protected array $columns		= [
		'id',
		'createdAt',
		'ip',
		'ipVersion',
		'score',
		'reasons',
		'requestMethod',
		'requestUri',
		'requestHeaders',
		'postData',
	];

	protected string $primaryKey	= 'id';

	protected array $indices		= [
		'createdAt',
		'ip',
		'score',
	];

	protected int $fetchMode		= \PDO::FETCH_CLASS;

	protected ?string $className	= 'Entity_Log_API_Reject';
}

