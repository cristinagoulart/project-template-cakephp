<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

/**
 * AvatarsSync shell command.
 */
class AvatarsSyncShell extends Shell
{

    /**
     * Manage the available sub-commands along with their arguments and help
     *
     * @see http://book.cakephp.org/3.0/en/console-and-shells.html#configuring-options-and-generating-help
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        return $parser;
    }

    /**
     * main() method.
     *
     * @return bool|int|null Success or error code.
     */
    public function main()
    {
        $this->Users = TableRegistry::get('CakeDC/Users.Users');

        $query = $this->Users->find()->all();

        if (!$query->count()) {
            $this->out("No users found for avatar sync. Exiting...");

            return null;
        }

        $usersCount = $query->count();
        $generated = $updated = 0;

        foreach ($query as $entity) {
            if ($this->Users->isCustomAvatarExists($entity)) {
                if ($this->Users->copyCustomAvatar($entity)) {
                    $updated++;
                }
            } else {
                $imageSource = $entity->get('image_src');
                $generated++;
            }
        }

        $this->out("Avatar sync. Updated: $updated. Generated: $generated. Users: $usersCount");
    }
}
