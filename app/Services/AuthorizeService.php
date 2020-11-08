<?php
namespace App\Services;

use App\Models\TechAcc\Defects\Defects;

class AuthorizeService
{
    protected $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }

    public function returnUnauthorized()
    {
        return abort(403);
    }

    public function authorizeVehicleCategoryEdit()
    {
        return $this->user->hasPermission('tech_acc_vehicle_category_edit') ?: $this->returnUnauthorized();
    }

    public function authorizeVehicleCategoryCreate()
    {
        return $this->user->hasPermission('tech_acc_vehicle_category_create') ?: $this->returnUnauthorized();
    }

    public function authorizeDefectDelete(Defects $defect)
    {
        return ($this->user->id == $defect->user_id and $defect->isNew()) ?: $this->returnUnauthorized();
    }
}
