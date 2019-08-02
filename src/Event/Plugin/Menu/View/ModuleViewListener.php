<?php

namespace App\Event\Plugin\Menu\View;

use App\Menu\MenuName;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Http\ServerRequest;
use Cake\Routing\Router;
use Menu\Event\EventName as MenuEventName;
use Menu\MenuBuilder\MenuInterface;
use Menu\MenuBuilder\MenuItemFactory;
use Menu\MenuBuilder\MenuItemInterface;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Webmozart\Assert\Assert;

class ModuleViewListener implements EventListenerInterface
{
    use MenuEntityTrait;

    /**
     * @inheritdoc
     *
     * @return array associative array or event key names pointing to the function
     * that should be called in the object when the respective event is fired
     */
    public function implementedEvents()
    {
        return [
            (string)MenuEventName::GET_MENU_ITEMS() => 'getMenuItems'
        ];
    }

    /**
     * Method that returns the menu for Search View
     *
     * @param Event $event Event object
     * @param string $name Menu name
     * @param mixed[] $user Current user
     * @param bool $fullBaseUrl Flag for fullbase url on menu links
     * @param mixed[] $modules Modules to fetch menu items for
     * @param MenuInterface|null $menu Menu object to be updated
     * @return void
     */
    public function getMenuItems(Event $event, string $name, array $user, bool $fullBaseUrl = false, array $modules = [], MenuInterface $menu = null): void
    {
        Assert::isInstanceOf($menu, MenuInterface::class);

        $listens = [MenuName::MODULE_VIEW];
        if (!in_array($name, $listens)) {
            return;
        }

        // Actions are available only when viewing an entity
        $entity = $event->getSubject();
        if (!($entity instanceof EntityInterface)) {
            return;
        }

        $request = Router::getRequest();
        Assert::isInstanceOf($request, ServerRequest::class);

        $menu->addMenuItem($this->getEditMenuItem($entity, $request));
        $menu->addMenuItem($this->getDeleteMenuItem($entity, $request));

        $moreActions = MenuItemFactory::createMenuItem([
            'label' => __('More Actions'),
            'icon' => 'plus-square-o',
            'type' => 'button_group',
            'order' => 0,
        ]);

        $moreActions->addMenuItem($this->getClearPersonalDataMenuItem($entity, $request));
        $moreActions->addMenuItem($this->getPermissionsMenuItem($entity, $request));
        $moreActions->addMenuItem($this->getChangelogMenuItem($entity, $request));

        $menu->addMenuItem($moreActions);
        $event->setResult($event);
    }

    /**
     * Creates and returns the menu item for the permissions action
     *
     * @param EntityInterface $entity Entity to be deleted
     * @param ServerRequest $request Current server request
     * @return \Menu\MenuBuilder\MenuItemInterface
     */
    public function getPermissionsMenuItem(EntityInterface $entity, ServerRequest $request): MenuItemInterface
    {
        $plugin = $request->getParam('plugin');
        $controller = $request->getParam('controller');
        $id = $entity->get('id');

        return MenuItemFactory::createMenuItem([
            'url' => ['plugin' => $plugin, 'controller' => $controller, 'action' => 'managePermissions'],
            'attributes' => ['class' => ' '],
            'label' => __('Permissions'),
            'icon' => 'shield',
            'type' => 'link_button_modal',
            'modal_target' => 'permissions-modal-add',
            'order' => 50,
            'viewElement' => ['modal-permissions', ['id' => $id]]
        ]);
    }

    /**
     * Creates and returns the menu item for the changelog action
     *
     * @param EntityInterface $entity Entity to be deleted
     * @param ServerRequest $request Current server request
     * @return \Menu\MenuBuilder\MenuItemInterface
     */
    public function getChangelogMenuItem(EntityInterface $entity, ServerRequest $request): MenuItemInterface
    {
        $plugin = $request->getParam('plugin');
        $controller = $request->getParam('controller');
        $id = $entity->get('id');

        return MenuItemFactory::createMenuItem([
            'url' => ['plugin' => $plugin, 'controller' => $controller, 'action' => 'changelog', $id],
            'attributes' => ['class' => ' '],
            'label' => __('Changelog'),
            'icon' => 'book',
            'type' => 'link_button',
            'order' => 60
        ]);
    }

    /**
     * Creates and returns the menu item for the clearPersonalData action
     *
     * @param EntityInterface $entity Entity to be deleted
     * @param ServerRequest $request Current server request
     * @return \Menu\MenuBuilder\MenuItemInterface
     */
    public function getClearPersonalDataMenuItem(EntityInterface $entity, ServerRequest $request): MenuItemInterface
    {
        $plugin = $request->getParam('plugin');
        $controller = $request->getParam('controller');
        $id = $entity->get('id');

        // Check if in the module there is at least one field marked as personal.
        $moduleConfig = (array)(new ModuleConfig(ConfigType::FIELDS(), $controller))->parseToArray();
        $hasPersonal = false;
        foreach ($moduleConfig as $key => $value) {
            if (in_array('personal', array_keys($value))) {
                if ($value['personal'] === true) {
                    $hasPersonal = true;
                    break;
                }
            }
        }

        $item = MenuItemFactory::createMenuItem([
            'url' => ['plugin' => $plugin, 'controller' => $controller, 'action' => 'clearPersonalData', $id],
            'attributes' => ['class' => ' '],
            'label' => __('Clear Personal Data'),
            'icon' => 'trash-o',
            'type' => 'postlink_button',
            'order' => 60,
            'confirmMsg' => __('Are you sure you want to delete all personal data of this record?')
        ]);

        $item->disableIf(function () use ($hasPersonal) {
            return !$hasPersonal;
        });

        return $item;
    }
}
