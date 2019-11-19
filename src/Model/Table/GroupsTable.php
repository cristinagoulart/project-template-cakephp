<?php

namespace App\Model\Table;

use Groups\Model\Table\GroupsTable as BaseTable;

/**
 * {@inheritDoc}
 *
 * @todo This class can be removed if Modules migration.json related type fields can be defined
 * with plugin prefix, for example "related(Groups)" will be replaced with "related(Groups.Groups)"
 */
class GroupsTable extends BaseTable
{
}
