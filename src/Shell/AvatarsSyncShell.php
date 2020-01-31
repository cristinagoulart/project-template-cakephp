<?php

namespace App\Shell;

use Cake\Console\Shell;
use Cake\ORM\TableRegistry;
use Webmozart\Assert\Assert;

/**
 * AvatarsSync shell command.
 */
class AvatarsSyncShell extends Shell
{
    /**
     * @var object $Users
     */
    private $Users;

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
        $table = TableRegistry::getTableLocator()->get('Users');
        Assert::isInstanceOf($table, \App\Model\Table\UsersTable::class);
        $generated = $updated = 0;

        $query = $table->find()->all();
        $usersCount = $query->count();

        if (!$usersCount) {
            $this->out("No users found for avatar sync. Exiting...");

            return null;
        }

        foreach ($query as $entity) {
            if ($table->isCustomAvatarExists($entity)) {
                if ($table->copyCustomAvatar($entity)) {
                    $updated++;
                }

                continue;
            }

            $entity->get('image_src');
            $generated++;
        }

        $this->out("Avatar sync. Updated: $updated. Generated: $generated. Users: $usersCount");
    }
}
