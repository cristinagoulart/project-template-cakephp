<?php
namespace App\Controller\Traits;

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Http\Response;
use Cake\I18n\Time;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Text;
use IntlDateFormatter;
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
        $table = TableRegistry::getTableLocator()->get('ScheduledPersonalData');
        $query = $table->find()->where([
            'module' => $this->loadModel()->getAlias(),
            'record_id' => $id,
            'status' => 'pending'
        ])->first();

        // If is already in the table will not be insert again.
        if (!empty($query)) {
            Assert::isInstanceOf($query, EntityInterface::class);
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
