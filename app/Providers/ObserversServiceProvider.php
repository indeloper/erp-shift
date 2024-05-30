<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\CommercialOffer\CommercialOffer;
use App\Models\CommercialOffer\CommercialOfferMaterialSplit;
use App\Models\CommercialOffer\CommercialOfferWork;
use App\Models\Manual\ManualMaterial;
use App\Models\Manual\ManualMaterialCategory;
use App\Models\Manual\ManualNodeMaterials;
use App\Models\Manual\ManualReference;
use App\Models\MatAcc\MaterialAccountingBase;
use App\Models\MatAcc\MaterialAccountingMaterialFile;
use App\Models\MatAcc\MaterialAccountingOperation;
use App\Models\Notification\Notification;
use App\Models\Project;
use App\Models\Task;
use App\Models\TechAcc\Defects\Defects;
use App\Models\TechAcc\FuelTank\FuelTankOperation;
use App\Models\TechAcc\FuelTank\{FuelTank};
use App\Models\TechAcc\OurTechnicTicketReport;
use App\Models\TechAcc\Vehicles\OurVehicles;
use App\Models\TechAcc\Vehicles\VehicleCategories;
use App\Models\TechAcc\Vehicles\VehicleCategoryCharacteristics;
use App\Models\User;
use App\Models\Vacation\VacationsHistory;
use App\Models\WorkVolume\WorkVolumeMaterial;
use App\Models\WorkVolume\WorkVolumeWork;
use App\Observers\CommentObserver;
use App\Observers\CommercialOffers\CommercialOfferMaterialSplitObserver;
use App\Observers\CommercialOffers\CommercialOfferWorkObserver;
use App\Observers\CommercialOffersObserver;
use App\Observers\DefectObserver;
use App\Observers\Manual\ManualMaterialCategoryObserver;
use App\Observers\Manual\ManualMaterialObserver;
use App\Observers\Manual\ManualNodeMaterialsObserver;
use App\Observers\Manual\ManualReferenceObserver;
use App\Observers\MaterialAccounting\MaterialAccountingBaseObserver;
use App\Observers\MaterialAccountingMaterialFileObserver;
use App\Observers\MaterialAccountingOperationObserver;
use App\Observers\NotificationObserver;
use App\Observers\OurVehicleObserver;
use App\Observers\ProjectObserver;
use App\Observers\TaskObserver;
use App\Observers\TechAcc\FuelTank\FuelTankObserver;
use App\Observers\TechAcc\FuelTank\FuelTankOperationObserver;
use App\Observers\TechAcc\OurTechnicTicketReportObserver;
use App\Observers\UserObserver;
use App\Observers\VacationsHistoryObserver;
use App\Observers\VehicleCategoryCharacteristicObserver;
use App\Observers\VehicleCategoryObserver;
use App\Observers\WorkVolumes\WorkVolumeMaterialObserver;
use App\Observers\WorkVolumes\WorkVolumeWorkObserver;
use Illuminate\Support\ServiceProvider;

class ObserversServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Task::observe(TaskObserver::class);
        Notification::observe(NotificationObserver::class);

        User::observe(UserObserver::class);
        VacationsHistory::observe(VacationsHistoryObserver::class);

        ManualNodeMaterials::observe(ManualNodeMaterialsObserver::class);
        ManualMaterialCategory::observe(ManualMaterialCategoryObserver::class);
        ManualReference::observe(ManualReferenceObserver::class);

        WorkVolumeMaterial::observe(WorkVolumeMaterialObserver::class);
        WorkVolumeWork::observe(WorkVolumeWorkObserver::class);

        CommercialOffer::observe(CommercialOffersObserver::class);
        CommercialOfferMaterialSplit::observe(CommercialOfferMaterialSplitObserver::class);
        CommercialOfferWork::observe(CommercialOfferWorkObserver::class);

        MaterialAccountingBase::observe(MaterialAccountingBaseObserver::class);
        MaterialAccountingOperation::observe(MaterialAccountingOperationObserver::class);

        VehicleCategories::observe(VehicleCategoryObserver::class);
        VehicleCategoryCharacteristics::observe(VehicleCategoryCharacteristicObserver::class);
        OurVehicles::observe(OurVehicleObserver::class);

        FuelTank::observe(FuelTankObserver::class);
        FuelTankOperation::observe(FuelTankOperationObserver::class);

        Defects::observe(DefectObserver::class);
        Comment::observe(CommentObserver::class);
        OurTechnicTicketReport::observe(OurTechnicTicketReportObserver::class);

        Project::observe(ProjectObserver::class);
        MaterialAccountingMaterialFile::observe(MaterialAccountingMaterialFileObserver::class);
        ManualMaterial::observe(ManualMaterialObserver::class);
    }
}
