<?php

declare(strict_types=1);

namespace Noob\AcidIsland\generator\Worlds;

use pocketmine\item\StringToItemParser;
use pocketmine\world\ChunkManager;
use pocketmine\world\generator\Generator;

class WaterWorld extends Generator {

	public function __construct(int $seed, string $preset) {
		parent::__construct($seed, $preset);
	}

	public function generateChunk(ChunkManager $world, $chunkX, $chunkZ) : void {
		$chunk = $world->getChunk($chunkX, $chunkZ);
		for ($Z = 0; $Z < 16; ++$Z) {
			for ($X = 0; $X < 16; ++$X) {
				$chunk->setBlockStateId($X, 0, $Z, StringToItemParser::getInstance()->parse('BEDROCK')->getBlock()->getStateId());
				for ($y = 1; $y <= 10; ++$y) {
					$chunk->setBlockStateId($X, $y, $Z, StringToItemParser::getInstance()->parse('WATER')->getBlock()->getStateId());
				}
			}
		}
		if($chunkX == 0 && $chunkZ == 0){
			$ore = [
				'COAL_ORE', 'IRON_ORE', 'GOLD_ORE'
			];
			for($y = 5; $y <= 10; $y++){
				for($x = 0; $x <= 10; $x++){
					for($z = 0; $z <= 10; $z++){
						$rand = mt_rand(1, 5);
						if($rand == 3){
							$slot = mt_rand(0, 2);
							$chunk->setBlockStateId($x, $y, $z, StringToItemParser::getInstance()->parse($ore[$slot])->getBlock()->getStateId());
						}
						else{
							$chunk->setBlockStateId($x, $y, $z, StringToItemParser::getInstance()->parse('STONE')->getBlock()->getStateId());
						}
						$chunk->setBlockStateId($x, 9, $z, StringToItemParser::getInstance()->parse('DIRT')->getBlock()->getStateId());
						$chunk->setBlockStateId($x, 10, $z, StringToItemParser::getInstance()->parse('GRASS')->getBlock()->getStateId());
					}
				}
			}
			$tree_height = mt_rand(4, 6);
			$tree_max_y = 11 + $tree_height;
			for($tree_y = 11; $tree_y <= $tree_max_y; $tree_y++){
				$chunk->setBlockStateId(7, $tree_y, 7, StringToItemParser::getInstance()->parse('OAK_LOG')->getBlock()->getStateId());
			}
			$chunk->setBlockStateId(5, $tree_max_y, 7, StringToItemParser::getInstance()->parse('OAK_LEAVES')->getBlock()->getStateId());
			$chunk->setBlockStateId(7, $tree_max_y, 5, StringToItemParser::getInstance()->parse('OAK_LEAVES')->getBlock()->getStateId());
			$chunk->setBlockStateId(9, $tree_max_y, 7, StringToItemParser::getInstance()->parse('OAK_LEAVES')->getBlock()->getStateId());
			$chunk->setBlockStateId(7, $tree_max_y, 9, StringToItemParser::getInstance()->parse('OAK_LEAVES')->getBlock()->getStateId());
			$chunk->setBlockStateId(7, $tree_max_y+1, 6, StringToItemParser::getInstance()->parse('OAK_LEAVES')->getBlock()->getStateId());
			$chunk->setBlockStateId(6, $tree_max_y+1, 7, StringToItemParser::getInstance()->parse('OAK_LEAVES')->getBlock()->getStateId());
			$chunk->setBlockStateId(8, $tree_max_y+1, 7, StringToItemParser::getInstance()->parse('OAK_LEAVES')->getBlock()->getStateId());
			$chunk->setBlockStateId(7, $tree_max_y+1, 8, StringToItemParser::getInstance()->parse('OAK_LEAVES')->getBlock()->getStateId());
			$chunk->setBlockStateId(7, $tree_max_y+2, 7, StringToItemParser::getInstance()->parse('OAK_LEAVES')->getBlock()->getStateId());
			
			$chunk->setBlockStateId(10, 11, 7, StringToItemParser::getInstance()->parse('HAY_BALE')->getBlock()->getStateId());
			$chunk->setBlockStateId(10, 11, 8, StringToItemParser::getInstance()->parse('HAY_BALE')->getBlock()->getStateId());
			$chunk->setBlockStateId(10, 12, 8, StringToItemParser::getInstance()->parse('HAY_BALE')->getBlock()->getStateId());
			$chunk->setBlockStateId(10, 11, 9, StringToItemParser::getInstance()->parse('HAY_BALE')->getBlock()->getStateId());
			$chunk->setBlockStateId(10, 12, 9, StringToItemParser::getInstance()->parse('HAY_BALE')->getBlock()->getStateId());
			$chunk->setBlockStateId(10, 13, 9, StringToItemParser::getInstance()->parse('HAY_BALE')->getBlock()->getStateId());
			$chunk->setBlockStateId(10, 11, 10, StringToItemParser::getInstance()->parse('HAY_BALE')->getBlock()->getStateId());
			$chunk->setBlockStateId(10, 12, 10, StringToItemParser::getInstance()->parse('HAY_BALE')->getBlock()->getStateId());
			$chunk->setBlockStateId(10, 13, 10, StringToItemParser::getInstance()->parse('HAY_BALE')->getBlock()->getStateId());
			$chunk->setBlockStateId(10, 14, 10, StringToItemParser::getInstance()->parse('HAY_BALE')->getBlock()->getStateId());
			$chunk->setBlockStateId(7, 11, 10, StringToItemParser::getInstance()->parse('HAY_BALE')->getBlock()->getStateId());
			$chunk->setBlockStateId(8, 11, 10, StringToItemParser::getInstance()->parse('HAY_BALE')->getBlock()->getStateId());
			$chunk->setBlockStateId(8, 12, 10, StringToItemParser::getInstance()->parse('HAY_BALE')->getBlock()->getStateId());
			$chunk->setBlockStateId(9, 11, 10, StringToItemParser::getInstance()->parse('HAY_BALE')->getBlock()->getStateId());
			$chunk->setBlockStateId(9, 12, 10, StringToItemParser::getInstance()->parse('HAY_BALE')->getBlock()->getStateId());
			$chunk->setBlockStateId(9, 13, 10, StringToItemParser::getInstance()->parse('HAY_BALE')->getBlock()->getStateId());
			$chunk->setBlockStateId(9, 11, 9, StringToItemParser::getInstance()->parse('HAY_BALE')->getBlock()->getStateId());
			$chunk->setBlockStateId(9, 12, 9, StringToItemParser::getInstance()->parse('HAY_BALE')->getBlock()->getStateId());
			$chunk->setBlockStateId(9, 11, 8, StringToItemParser::getInstance()->parse('HAY_BALE')->getBlock()->getStateId());
			$chunk->setBlockStateId(8, 11, 9, StringToItemParser::getInstance()->parse('HAY_BALE')->getBlock()->getStateId());
			$chunk->setBlockStateId(6, 11, 7, StringToItemParser::getInstance()->parse('CRAFTING_TABLE')->getBlock()->getStateId());
			$chunk->setBlockStateId(1, 11, 1, StringToItemParser::getInstance()->parse('OAK_LOG')->getBlock()->getStateId());
			$chunk->setBlockStateId(1, 12, 1, StringToItemParser::getInstance()->parse('TORCH')->getBlock()->getStateId());
			$chunk->setBlockStateId(9, 11, 1, StringToItemParser::getInstance()->parse('OAK_LOG')->getBlock()->getStateId());
			$chunk->setBlockStateId(9, 12, 1, StringToItemParser::getInstance()->parse('TORCH')->getBlock()->getStateId());
			$chunk->setBlockStateId(1, 11, 9, StringToItemParser::getInstance()->parse('OAK_LOG')->getBlock()->getStateId());
			$chunk->setBlockStateId(1, 12, 9, StringToItemParser::getInstance()->parse('TORCH')->getBlock()->getStateId());
		}
	}

	public function populateChunk(ChunkManager $world, $chunkX, $chunkY) : void {
	}
}