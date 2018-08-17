<?php
namespace App\Shell\Task;

use App\Avatar\Service as AvatarService;
use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

class Upgrade20180726084300Task extends Shell
{
    /**
     * Manage the available sub-commands along with their arguments and help
     *
     * @see http://book.cakephp.org/3.0/en/console-and-shells.html#configuring-options-and-generating-help
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = new ConsoleOptionParser('console');

        return $parser;
    }

    /**
     * main() method
     *
     * @return void
     */
    public function main()
    {
        $this->Users = TableRegistry::get('CakeDC/Users.Users');

        $query = $this->Users->find()
            ->where(['image IS NOT' => null]);

        $query->execute();

        if (!$query->count()) {
            $this->warn("No DB stored profile images found. Exiting...");

            return;
        }

        $avatarService = new AvatarService();

        foreach ($query->all() as $entity) {
            $source = $avatarService->getImageResource($entity->get('image'), true);

            $processed = $this->Users->saveCustomAvatar($entity, $source);

            if (!$processed) {
                $this->warn("User [" . $entity->get('email') . "] avatar failed");

                continue;
            }

            $this->info("User [" . $entity->get('email') . "] is saved");

            $entity = $this->Users->patchEntity($entity, ['image' => null]);

            if ($this->Users->save($entity)) {
                $this->info("User [" . $entity->get('email') . "] image field cleared");
            } else {
                $this->warn($entity->getErrors());
            }
        }
    }
}
