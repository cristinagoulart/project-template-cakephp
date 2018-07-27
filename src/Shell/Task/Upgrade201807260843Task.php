<?php
namespace App\Shell\Task;

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

class Upgrade201807260843Task extends Shell
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

        $extension = Configure::read('Avatar.extension');
        $directory = WWW_ROOT . Configure::read('Avatar.directory');

        foreach ($query->all() as $entity) {
            $id = $entity->get('id');
            $decodedImage = file_get_contents($entity->get('image'));
            $source = imagecreatefromstring($decodedImage);

            imagealphablending($source, false);
            imagetruecolortopalette($source, false, 256);

            if (imagepng($source, $directory . $id . $extension, 6, PNG_NO_FILTER)) {
                $this->info("User [" . $entity->get('email') . "] is saved");

                $entity = $this->Users->patchEntity($entity, ['image' => null]);
                if ($this->Users->save($entity)) {
                    $this->info("User [" . $entity->get('email') . "] image field cleared");
                } else {
                    dd($entity->getErrors());
                }
            } else {
                $this->warn("User [" . $entity->get('email') . "] avatar failed");
            }
        }
    }
}
