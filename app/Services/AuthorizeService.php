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

    public function authorizeJobCategoriesIndex()
    {
        return $this->user->hasPermission('human_resources_job_categories_view') ?: $this->returnUnauthorized();
    }

    public function authorizeJobCategoryShow()
    {
        return $this->authorizeJobCategoriesIndex();
    }

    public function authorizeReportGroupsIndex()
    {
        return $this->user->hasPermission('human_resources_report_group_view') ?: $this->returnUnauthorized();
    }

    public function authorizeReportGroupsShow()
    {
        return $this->authorizeJobCategoriesIndex();
    }
}
