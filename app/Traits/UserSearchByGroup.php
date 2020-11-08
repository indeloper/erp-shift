<?php

namespace App\Traits;

use App\Models\Group;

trait UserSearchByGroup {
    /**
     * @return array
     */
    public function findAllUsersAndReturnGroupIds(array $searchGroups): array
    {
        $usersFromGroup = [];
        $groups = Group::whereIn('id', $searchGroups)->get();

        foreach ($groups as $group) {
            $usersFromGroup = array_merge($usersFromGroup, count($group->getUsers()) ? $group->getUsers()->pluck('group_id')->toArray() : []);
        }

        return $usersFromGroup;
    }
}
