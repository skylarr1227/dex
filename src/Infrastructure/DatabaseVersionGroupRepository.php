<?php
declare(strict_types=1);

namespace Jp\Dex\Infrastructure;

use Jp\Dex\Domain\Moves\MoveId;
use Jp\Dex\Domain\Pokemon\PokemonId;
use Jp\Dex\Domain\Versions\GenerationId;
use Jp\Dex\Domain\Versions\VersionGroup;
use Jp\Dex\Domain\Versions\VersionGroupId;
use Jp\Dex\Domain\Versions\VersionGroupNotFoundException;
use Jp\Dex\Domain\Versions\VersionGroupRepositoryInterface;
use PDO;

final class DatabaseVersionGroupRepository implements VersionGroupRepositoryInterface
{
	private PDO $db;

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
	 * Get a version group by its id.
	 *
	 * @param VersionGroupId $versionGroupId
	 *
	 * @throws VersionGroupNotFoundException if no version group exists with
	 *     this id.
	 *
	 * @return VersionGroup
	 */
	public function getById(VersionGroupId $versionGroupId) : VersionGroup
	{
		$stmt = $this->db->prepare(
			'SELECT
				`identifier`,
				`generation_id`,
				`icon`,
				`sort`
			FROM `version_groups`
			WHERE `id` = :version_group_id
			LIMIT 1'
		);
		$stmt->bindValue(':version_group_id', $versionGroupId->value(), PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$result) {
			throw new VersionGroupNotFoundException(
				'No version group exists with id ' . $versionGroupId->value() . '.'
			);
		}

		$versionGroup = new VersionGroup(
			$versionGroupId,
			$result['identifier'],
			new GenerationId($result['generation_id']),
			$result['icon'],
			$result['sort']
		);

		return $versionGroup;
	}

	/**
	 * Get a version group by its identifier.
	 *
	 * @param string $identifier
	 *
	 * @throws VersionGroupNotFoundException if no version group exists with
	 *     this id.
	 *
	 * @return VersionGroup
	 */
	public function getByIdentifier(string $identifier) : VersionGroup
	{
		$stmt = $this->db->prepare(
			'SELECT
				`id`,
				`generation_id`,
				`icon`,
				`sort`
			FROM `version_groups`
			WHERE `identifier` = :identifier
			LIMIT 1'
		);
		$stmt->bindValue(':identifier', $identifier, PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$result) {
			throw new VersionGroupNotFoundException(
				"No version group exists with identifier $identifier."
			);
		}

		$versionGroup = new VersionGroup(
			new VersionGroupId($result['id']),
			$identifier,
			new GenerationId($result['generation_id']),
			$result['icon'],
			$result['sort']
		);

		return $versionGroup;
	}

	/**
	 * Get version groups that this Pokémon has appeared in, up to a certain
	 * generation. This method is used to get all relevant version groups for
	 * the dex Pokémon page.
	 *
	 * @param PokemonId $pokemonId
	 * @param GenerationId $end
	 *
	 * @return VersionGroup[] Indexed by id, ordered by sort.
	 */
	public function getWithPokemon(PokemonId $pokemonId, GenerationId $end) : array
	{
		$stmt = $this->db->prepare(
			'SELECT
				`id`,
				`identifier`,
				`generation_id`,
				`icon`,
				`sort`
			FROM `version_groups`
			WHERE `id` IN (
				SELECT
					`version_group_id`
				FROM `version_group_pokemon`
				WHERE `pokemon_id` = :pokemon_id
			)
			AND `generation_id` <= :end
			ORDER BY `sort`'
		);
		$stmt->bindValue(':pokemon_id', $pokemonId->value(), PDO::PARAM_INT);
		$stmt->bindValue(':end', $end->value(), PDO::PARAM_INT);
		$stmt->execute();

		$versionGroups = [];

		while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$versionGroup = new VersionGroup(
				new VersionGroupId($result['id']),
				$result['identifier'],
				new GenerationId($result['generation_id']),
				$result['icon'],
				$result['sort']
			);

			$versionGroups[$result['id']] = $versionGroup;
		}

		return $versionGroups;
	}

	/**
	 * Get version groups that this move has appeared in, up to a certain
	 * generation. This method is used to get all relevant version groups for
	 * the dex move page.
	 *
	 * @param MoveId $moveId
	 * @param GenerationId $end
	 *
	 * @return VersionGroup[] Indexed by id, ordered by sort.
	 */
	public function getWithMove(MoveId $moveId, GenerationId $end) : array
	{
		$stmt = $this->db->prepare(
			'SELECT
				`id`,
				`identifier`,
				`generation_id`,
				`icon`,
				`sort`
			FROM `version_groups`
			WHERE `id` IN (
				SELECT
					`version_group_id`
				FROM `version_group_moves`
				WHERE `move_id` = :move_id
			)
			AND `generation_id` <= :end
			ORDER BY `sort`'
		);
		$stmt->bindValue(':move_id', $moveId->value(), PDO::PARAM_INT);
		$stmt->bindValue(':end', $end->value(), PDO::PARAM_INT);
		$stmt->execute();

		$versionGroups = [];

		while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$versionGroup = new VersionGroup(
				new VersionGroupId($result['id']),
				$result['identifier'],
				new GenerationId($result['generation_id']),
				$result['icon'],
				$result['sort']
			);

			$versionGroups[$result['id']] = $versionGroup;
		}

		return $versionGroups;
	}

	/**
	 * Get version groups between these two generations, inclusive. This method
	 * is used to get all relevant version groups for the dex move page.
	 *
	 * @param GenerationId $begin
	 * @param GenerationId $end
	 *
	 * @return VersionGroup[] Indexed by id, ordered by sort.
	 */
	public function getBetween(GenerationId $begin, GenerationId $end) : array
	{
		$stmt = $this->db->prepare(
			'SELECT
				`id`,
				`identifier`,
				`generation_id`,
				`icon`,
				`sort`
			FROM `version_groups`
			WHERE `generation_id` BETWEEN :begin AND :end
			ORDER BY `sort`'
		);
		$stmt->bindValue(':begin', $begin->value(), PDO::PARAM_INT);
		$stmt->bindValue(':end', $end->value(), PDO::PARAM_INT);
		$stmt->execute();

		$versionGroups = [];

		while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$versionGroup = new VersionGroup(
				new VersionGroupId($result['id']),
				$result['identifier'],
				new GenerationId($result['generation_id']),
				$result['icon'],
				$result['sort']
			);

			$versionGroups[$result['id']] = $versionGroup;
		}

		return $versionGroups;
	}
}
