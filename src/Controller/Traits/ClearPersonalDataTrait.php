<?php
namespace App\Controller\Traits;

use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Text;
use IntlDateFormatter;

/**
 * Controller Trait responsible for add the personal data to clear in the scheduler.
 */
trait ClearPersonalDataTrait
{
    /**
     * Main method
     *
     * @param uuid $id Entity id
     * @return \Cake\Http\Response|void|null
     */
    public function clearPersonalData($id)
    {
        $table = TableRegistry::getTableLocator()->get('ScheduledPersonalData');
        $query = $table->find()->where([
            'module' => $this->loadModel()->getAlias(),
            'record_id' => $id,
            'status' => 'pending'
        ])->first();

        // If is already in the table will not be insert again.
        if (!empty($query)) {
            $time = $query->get('scheduled');
            $time = $time->i18nFormat([IntlDateFormatter::FULL, IntlDateFormatter::SHORT]);
            $this->Flash->success((string)__('The data will be removed on {0}', $time));

            return $this->redirect(['action' => 'view', $id]);
        }

        $scheduled_config = Configure::read('ClearPersonalData.scheduled');
        $data = [
            'id' => Text::uuid(),
            'module' => $this->loadModel()->getAlias(),
            'record_id' => $id,
            'user_id' => $this->Auth->user('id'),
            'scheduled' => new Time($scheduled_config),
            'status' => 'pending'
        ];

        $entity = $table->newEntity($data);

        if ($table->save($entity)) {
            $time = $entity->get('scheduled');
            $time = $time->i18nFormat([IntlDateFormatter::FULL, IntlDateFormatter::SHORT]);
            $this->Flash->success((string)__('The data will be removed on {0}', $time));
        } else {
            $this->Flash->error((string)__('Failed to schedule the delete of personal data.'));
        }

        return $this->redirect(['action' => 'view', $id]);
    }
}
