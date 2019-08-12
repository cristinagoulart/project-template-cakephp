<?php
namespace App\Controller\Traits;

use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\I18n\Time;
use IntlDateFormatter;
use Josegonzalez\CakeQueuesadilla\Queue\Queue;
use Webmozart\Assert\Assert;

/**
 * Controller Trait responsible for add the personal data to clear in the scheduler.
 */
trait ClearPersonalDataTrait
{
    /**
     * Main method
     *
     * @param string $id Entity id
     * @return \Cake\Http\Response|null
     */
    public function clearPersonalData(string $id) : ?\Cake\Http\Response
    {
        $scheduled_config = Configure::read('ClearPersonalData.scheduled');
        $delay = ((new Time($scheduled_config))->getTimestamp()) - (new Time())->now()->getTimestamp();

        $data = [
            'module' => $this->loadModel()->getAlias(),
            'record_id' => $id,
            'user_id' => $this->Auth->user('id'),
        ];

        $queue = [
            'queue' => 'personal_data_cleaner',
            'attempts' => 5,
            'delay' => $delay,
        ];

        Queue::push('\App\QueueJobs\PersonalDataCleanerQueue::addRecord', $data, $queue);

        $time = (new Time($scheduled_config))->i18nFormat([IntlDateFormatter::FULL, IntlDateFormatter::SHORT]);
        $this->Flash->success((string)__('The data will be removed on {0}', $time));

        return $this->redirect(['action' => 'view', $id]);
    }
}
