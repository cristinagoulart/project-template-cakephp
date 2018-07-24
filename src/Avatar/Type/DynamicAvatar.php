<?php
namespace App\Avatar\Type;

use App\Avatar\AvatarInterface;
use Cake\Core\Configure;
use \LasseRafn\InitialAvatarGenerator\InitialAvatar;

final class DynamicAvatar implements AvatarInterface
{
    /**
     * Gravatar default options.
     *
     * @var array
     */
    private $options = [];

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
        $source = !empty($this->options['name']) ? $this->options['name'] : $this->options['email'];
        $avatar = new InitialAvatar();
        $image = $avatar->name($source)
            ->size($this->options['size'])
            ->length($this->options['length'])
            ->background($this->options['background'])
            ->generate()
            ->stream('data-url');

        return $image->getContents();
    }
}
