<?php

/*
__PocketMine Plugin__
class=CCMapper
name=Chunk Claimers Mappers' Plugin!
version=0.0
apiversion=12
author=PEMapModder
*/

class CCMapper implements Plugin{
	public $status=array();//2 for every chunk, 1 for every quarter, 0 for no sync
	private $mapping_world="cc_field";
	public function __construct(ServerAPI $api, $s=0){
	}
	public function __destruct(){
	}
	public function init(){
		$this->server=ServerAPI::request();
		$this->api=$this->server->api;
		$this->dir=$this->api->plugin->configPath($this);
		$this->server->addHandler("player.spawn", array($this, "onSpawn"));
		$this->api->console->register("sync", "", array($this, "cmd"));
		$this->server->addHandler("player.block.touch", array($this, "blockTouchScheduler"));
		if(!$this->api->level->levelExists($this->mapping_world)){
			$lg=new SuperflatGenerator(array("preset"=>"2;7,80x1,3x3,2"));
			$wg=new WorldGenerator($lg, $this->mapping_world, 0x09a09f);
			$wg->generate();
			$wg->close();
		}
		$this->api->level->loadLevel($this->mapping_world);
	}
	public function onSpawn(Player $player){
		$this->status[$player->iusername]=0;
	}
	public function cmd($cmd, $args, $issuer){
		if(is_string($issuer))
			return "Please run this command in-game.";
		switch($cmd){
		case "sync":
			if(count($args)===0)
				return "Your sync mode is ".($this->status[$issuer->iusername]);
			if($args[0]==="0" or $args[0]==="1" or $args[0]==="2"){
				$this->status[$issuer->iusername]=(int)$args[0];
				return "Turned your sync mode to ".$args[0].".";
			}
			return "Your sync mode is ".($this->status[$issuer->iusername]);
		}
	}
	public function blockTouchScheduler($data, $event){
		$this->api->schedule(1, array($this, "onBlockTouch"), $data);//, false, $event);
	}
	public function onBlockTouch($data){
		$p=$data["player"];
		if($p->entity->level->getName()==$this->mapping_world and ($status=$this->getSync($p))){
			if($data["type"]==="break")
				$data["target"]=$data["target"]->level->getBlock($data["target"]);
			$t=$data["target"];
			console("[DEBUG] t:$t");
			for($i=0; $i<6; $i++){
				$this->sync($t->getSide($i), $status);
				console("[DEBUG] i:$i");
			}
			$this->sync($t, $status);
		}
	}
	private function sync(Block $block, $type){
		if($type<=0)return;
		$l=$block->level;
		$x=$block->x;
		$y=$block->y;
		$z=$block->z;
		$l->setBlock(new Vector3(255-$x, $y, $z), $block, false, false, true);
		$l->setBlock(new Vector3($x, $y, 255-$z), $block, false, false, true);
		$l->setBlock(new Vector3(255-$x, $y, 255-$z), $block, false, false, true);
		if($type>=2){
			if($x>=128)
				$x=255-$x;
			if($z>=128)
				$z=255-$z;
			$x=$x%16;
			$z=$z%16;
			for($cx=0; $cx<8; $cx++){
				// console("[DEBUG] Sync at x:$x y:$y z:$z type:$type");
				for($cz=0; $cz<8; $cz++){
					$pos=new Vector3($cx*16+$x, $y, $cz*16+$z);
					$l->setBlock($pos, $block, false, false, true);
					$this->sync($l->getBlock($pos), 1);
				}
			}
		}
	}
	public function getSync($name){
		if($name instanceof Player)
			$name=$name->iusername;
		return $this->status[$name];
	}
}
