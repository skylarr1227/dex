<?php
declare(strict_types=1);

namespace Jp\Dex\Infrastructure;

use DateTime;
use Jp\Dex\Domain\Formats\FormatId;
use Jp\Dex\Domain\Pokemon\PokemonId;
use Jp\Dex\Domain\Stats\Usage\Averaged\UsageAveragedPokemon;
use Jp\Dex\Domain\Stats\Usage\Averaged\UsageAveragedPokemonRepositoryInterface;
use PDO;

class DatabaseUsageAveragedPokemonRepository implements UsageAveragedPokemonRepositoryInterface
{
	/** @var PDO $db */
	private $db;

	/**
	 * Constructor.
	 *
	 * @param PDO $db
	 */
	public function __construct(PDO $db)
	{
		$this->db = $db;
	}

	/**
	 * Get usage averaged Pokémon records by their start month, end month, and
	 * format. Indexed by Pokémon id value.
	 *
	 * @param DateTime $start
	 * @param DateTime $end
	 * @param FormatId $formatId
	 *
	 * @return UsageAveragedPokemon[]
	 */
	public function getByMonthsAndFormat(
		DateTime $start,
		DateTime $end,
		FormatId $formatId
	) : array {
		$stmt = $this->db->prepare(
			'SELECT
				`pokemon_id`,
				SUM(`raw`) AS `raw`,
				AVG(`raw_percent`) AS `raw_percent`,
				SUM(`real`) AS `real`,
				AVG(`real_percent`) AS `real_percent`
			FROM `usage_pokemon`
			WHERE `month` BETWEEN :start AND :end
				AND `format_id` = :format_id
			GROUP BY `pokemon_id`'
		);
		$stmt->bindValue(':start', $start->format('Y-m-01'), PDO::PARAM_STR);
		$stmt->bindValue(':end', $end->format('Y-m-01'), PDO::PARAM_STR);
		$stmt->bindValue(':format_id', $formatId->value(), PDO::PARAM_INT);
		$stmt->execute();

		$usageAveragedPokemons = [];

		while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$usageAveragedPokemon = new UsageAveragedPokemon(
				$start,
				$end,
				$formatId,
				new PokemonId($result['pokemon_id']),
				$result['raw'],
				(float) $result['raw_percent'],
				$result['real'],
				(float) $result['real_percent']
			);

			$usageAveragedPokemons[$result['pokemon_id']] = $usageAveragedPokemon;
		}

		return $usageAveragedPokemons;
	}
}