<?php

namespace VirVolta;

use pocketmine\block\Air;
use pocketmine\block\Obsidian;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\entity\projectile\Egg;
use pocketmine\event\Listener;
use pocketmine\level\Explosion;
use pocketmine\level\Position;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\entity\ProjectileHitEvent;

class Dynamite extends PluginBase implements Listener
{
    private $protect;

    public function onEnable()
    {
	$this->getServer()->getPluginManager()->registerEvents($this, $this);

        if ($this->getServer()->getPluginManager()->getPlugin("iProtector") !== null) {

            $this->protect = $this->getServer()->getPluginManager()->getPlugin("iProtector");

        } else {

            $this->getLogger()->critical("The Iprotect plugin is not installed ,It may have problems");

        }

    }

    private function canExplode(Position $position)
    {
        if(isset($this->protect)) {
            
            $result = true;
		
            foreach($this->protect->areas as $area){

                if($area->contains($position, $position->getLevel()->getName())){

                    if($area->getFlag("edit")){

                        $result = false;

                    }

                    if(!$area->getFlag("edit")){

                        $result = true;
                        break;

                    }

                }

            }

            return $result;   
            
        }
        
        return true;
    }

    public function onProjectileHitEntity(ProjectileHitEntityEvent $event)
    {
        $entity = $event->getEntity();

        if ($entity instanceof Egg) {

            $explosion = new Explosion(new Position($entity->getX(), $entity->getY(), $entity->getZ(), $entity->getLevel()), 3.3,$entity);
            $explosion->explodeA();
            $explosion->explodeB();


            $entity->flagForDespawn();
        }

    }

    public function onProjectileHit(ProjectileHitEvent $event)
    {
        $entity = $event->getEntity();

        if ($entity instanceof Egg) {

            $position = new Position($entity->getX(), $entity->getY(), $entity->getZ(), $entity->getLevel());
            $explosion = new Explosion($position, 3.3,$entity);
            $explosion->explodeA();
            $explosion->explodeB();

            $entity->flagForDespawn();

        }

    }

    public function onExplode(EntityExplodeEvent $event)
    {
        $entity = $event->getEntity();
        $center = $entity->getLevel()->getBlock($entity);
        $listBlock = [];

        if ($entity instanceof Egg) {

            $pos = new Position($entity->x,$entity->y,$entity->z,$entity->level);
            
            if(!$this->canExplode($pos)){
                
                $event->setCancelled();
            
            } else {

                for($i = 0; $i <= (3.3*2); $i++) {

                    $listBlock[] = $center->getSide($i);

                }

                foreach ($listBlock as $block) {

                    if ($block instanceof Obsidian) {

                        $block->getLevel()->setBlock($block, new Air());

                    }

                }

            }

        }

    }

}
