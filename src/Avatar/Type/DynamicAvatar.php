<?php
namespace App\Avatar\Type;

use App\Avatar\AbstractAvatar;
use LasseRafn\InitialAvatarGenerator\InitialAvatar;

final class DynamicAvatar extends AbstractAvatar
{
    /**
     * Gravatar default options.
     *
     * @var array
     */
    private $options = [];

    /**
     * {@inheritDoc}
     *
     * @param mixed[] $options
     */
    public function __construct(array $options)
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function get(): string
    {
        $result = '';

        $filename = $this->getAvatarUrl($this->options);
        $file = WWW_ROOT . $filename;

        $source = !empty($this->options['name']) ? $this->options['name'] : $this->options['email'];
        $avatar = new InitialAvatar();
        $key = array_rand($this->options['background'], 1);

        $image = $avatar->name($source)
            ->size($this->options['size'])
            ->length($this->options['length'])
            ->background($this->options['background'][$key])
            ->generate()
            ->stream('data-url');

        $resource = imagecreatefromstring((string)file_get_contents($image->getContents()));
        $saved = $this->processAvatarResource($file, $resource);

        if ($saved) {
            $result = $filename;
        }

        return $result;
    }
}
