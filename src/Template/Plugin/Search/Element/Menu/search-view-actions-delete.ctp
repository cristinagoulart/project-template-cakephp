<?php $this->Html->scriptStart(); ?>
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
                Authorization: "<?= $this->request->getHeaderLine('authorization') ?>"
            },
            success: function (data) {
                // traverse upwards on the tree to find table instance and reload it
                $(that).closest("table").DataTable().ajax.reload();
            }
        });
    });
<?= $this->Html->scriptEnd() ?>