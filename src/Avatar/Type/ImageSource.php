<?php
namespace App\Avatar\Type;

use App\Avatar\AvatarInterface;
use Cake\Core\Configure;

final class ImageSource extends AvatarInterface
{
    /**
     * Image default options.
     *
     * @var array
     */
    private $options = [
        'src' => '',
    ];

    /**
     * {@inheritDoc}
     */
    public function __construct(array $options)
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function get()
    {
        $directory = Configure::read('Avatar.directory');

        if (file_exists(WWW_ROOT . $directory . $this->options['filename'])) {
            $this->options['src'] = $directory . $this->options['filename'];
        }

        return sprintf('%s', $this->options['src']);
    }
}
