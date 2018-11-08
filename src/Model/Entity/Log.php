<?php
namespace App\Model\Entity;

use DatabaseLog\Model\Entity\DatabaseLog;

/**
 * DatabaseLog Entity
 *
 * @property int $id
 * @property string $type
 * @property string $message
 * @property string $context
 * @property \Cake\I18n\Time $created
 * @property string $ip
 * @property string $hostname
 * @property string $uri
 * @property string $refer
 * @property string $user_agent
 * @property int $count
 */
class Log extends DatabaseLog
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true
    ];
}
