<?php

namespace App\Avatar\Type;

use App\Avatar\AbstractAvatar;
use Cake\Core\Configure;

final class ImageSource extends AbstractAvatar
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
     *
     * @param mixed[] $options
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function get(): string
    {
        $directory = Configure::read('Avatar.directory');
        $filename = $this->getAvatarUrl($this->options);

        if (file_exists(WWW_ROOT . $filename)) {
            $this->options['src'] = $directory . $this->options['filename'];
        }

        return sprintf('%s', $this->options['src']);
    }
}
