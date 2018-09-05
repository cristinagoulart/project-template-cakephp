<?php
use Cake\Core\Configure;
use Cake\Event\Event;
use Menu\Event\EventName;
use Menu\MenuBuilder\MenuInterface;

$event = new Event((string)EventName::GET_MENU_ITEMS(), $entity, [
    'name' => 'search_view',
    'user' => $user,
]);
$this->eventManager()->dispatch($event);

/** @var \Menu\MenuBuilder\Menu $menu */
$menu = $event->getResult();
if (!($menu instanceof MenuInterface)) {
    return;
}

echo $this->element('menu-render', ['menu' => $menu, 'user' => $user, 'menuType' => 'actions']);

$this->Html->scriptStart();

/** @var \Menu\MenuBuilder\MenuItemInterface $menuItem */
foreach ($menu->getMenuItems() as $menuItem):
?>
    // trigger deletion of the record from the dynamic DataTables entries
    $("a[data-type='ajax-delete-record'][href='<?= $this->Url->build($menuItem->getUrl()) ?>']").click(function (e) {
        e.preventDefault();

        var that = this;

        if (! confirm($(this).data("confirm-msg"))) {
            return;
        }

        $.ajax({
            url: $(this).attr("href"),
            method: "DELETE",
            dataType: "json",
            contentType: "application/json",
            headers: {
                Authorization: "Bearer <?= Configure::read("API.token") ?>"
            },
            success: function (data) {
                // traverse upwards on the tree to find table instance and reload it
                $(that).closest("table").DataTable().ajax.reload();
            }
        });
    });
<?php
endforeach;
echo $this->Html->scriptEnd();
?>