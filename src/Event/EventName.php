<?php

namespace App\Event;

use MyCLabs\Enum\Enum;

/**
 * Event Name enum
 */
class EventName extends Enum
{
    // API events
    const API_ADD_AFTER_SAVE = 'API.Add.afterSave';
    const API_ADD_BEFORE_SAVE = 'API.Add.beforeSave';
    const API_EDIT_AFTER_FIND = 'API.Edit.afterFind';
    const API_EDIT_AFTER_SAVE = 'API.Edit.afterSave';
    const API_EDIT_BEFORE_FIND = 'API.Edit.beforeFind';
    const API_EDIT_BEFORE_SAVE = 'API.Edit.beforeSave';
    const API_INDEX_AFTER_PAGINATE = 'API.Index.afterPaginate';
    const API_INDEX_BEFORE_PAGINATE = 'API.Index.beforePaginate';
    const API_INDEX_BEFORE_RENDER = 'API.Index.beforeRender';
    const API_LOOKUP_AFTER_FIND = 'API.Lookup.afterFind';
    const API_LOOKUP_BEFORE_FIND = 'API.Lookup.beforeFind';
    const API_RELATED_AFTER_PAGINATE = 'API.Related.afterPaginate';
    const API_RELATED_BEFORE_PAGINATE = 'API.Related.beforePaginate';
    const API_RELATED_BEFORE_RENDER = 'API.Related.beforeRender';
    const API_VIEW_AFTER_FIND = 'API.View.afterFind';
    const API_VIEW_BEFORE_FIND = 'API.View.beforeFind';
}
