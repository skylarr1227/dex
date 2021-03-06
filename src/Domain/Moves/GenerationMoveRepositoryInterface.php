<?php
declare(strict_types=1);

namespace Jp\Dex\Domain\Moves;

use Jp\Dex\Domain\Types\TypeId;
use Jp\Dex\Domain\Versions\GenerationId;

interface GenerationMoveRepositoryInterface
{
	/**
	 * Get a generation move by its generation and move.
	 *
	 * @param GenerationId $generationId
	 * @param MoveId $moveId
	 *
	 * @throws GenerationMoveNotFoundException if no generation move exists with
	 *     this generation and move.
	 *
	 * @return GenerationMove
	 */
	public function getByGenerationAndMove(
		GenerationId $generationId,
		MoveId $moveId
	) : GenerationMove;

	/**
	 * Get generation moves by their generation and type.
	 *
	 * @param GenerationId $generationId
	 * @param TypeId $typeId
	 *
	 * @return GenerationMove[] Indexed by move id.
	 */
	public function getByGenerationAndType(
		GenerationId $generationId,
		TypeId $typeId
	) : array;
}
