<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ScheduledJobLog Entity
 *
 * @property string $id
 * @property string $scheduled_job_id
 * @property string $context
 * @property string $status
 * @property string $extra
 * @property \Cake\I18n\Time|string $datetime
 * @property \Cake\I18n\Time|string $created
 *
 * @property \App\Model\Entity\ScheduledJob $scheduled_job
 */
class ScheduledJobLog extends Entity
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
        '*' => true,
        'id' => false,
    ];
}
