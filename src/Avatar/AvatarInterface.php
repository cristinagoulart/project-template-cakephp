<?php
namespace App\Avatar;

interface AvatarInterface
{
    /**
     * Avatar getter method.
     *
     * @return string
     */
    public function get(): string;
}
