<?php

namespace App\Providers;

use App\Models\{Comment,
    Manual\ManualMaterial,
    MatAcc\MaterialAccountingMaterialFile,
    Notification\Notification,
    Project,
    Task,
    TechAcc\FuelTank\FuelTankOperation,
    User};
use App\Models\CommercialOffer\{CommercialOffer,
    CommercialOfferMaterialSplit,
    CommercialOfferWork};
use App\Models\Manual\{ManualMaterialCategory,
    ManualNodeMaterials,
    ManualReference};
use App\Models\MatAcc\{MaterialAccountingBase, MaterialAccountingOperation};
use App\Models\TechAcc\Defects\Defects;
use App\Models\TechAcc\FuelTank\{FuelTank};
use App\Models\TechAcc\OurTechnicTicketReport;
use App\Models\TechAcc\Vehicles\{OurVehicles,
    VehicleCategories,
    VehicleCategoryCharacteristics};
use App\Models\Vacation\VacationsHistory;
use App\Models\WorkVolume\{WorkVolumeMaterial, WorkVolumeWork};
use App\Observers\{CommentObserver,
    CommercialOffersObserver,
    DefectObserver,
    Manual\ManualMaterialObserver,
    MaterialAccountingMaterialFileObserver,
    MaterialAccountingOperationObserver,
    NotificationObserver,
    OurVehicleObserver,
    ProjectObserver,
    TaskObserver,
    UserObserver,
    VacationsHistoryObserver,
    VehicleCategoryCharacteristicObserver,
    VehicleCategoryObserver};
use App\Observers\CommercialOffers\{CommercialOfferMaterialSplitObserver,
    CommercialOfferWorkObserver};
use App\Observers\Manual\{ManualMaterialCategoryObserver,
    ManualNodeMaterialsObserver,
    ManualReferenceObserver};
use App\Observers\MaterialAccounting\MaterialAccountingBaseObserver;
use App\Observers\TechAcc\FuelTank\{FuelTankObserver,
    FuelTankOperationObserver};
use App\Observers\TechAcc\OurTechnicTicketReportObserver;
use App\Observers\WorkVolumes\{WorkVolumeMaterialObserver,
    WorkVolumeWorkObserver};
use Illuminate\Support\ServiceProvider;

class ObserversServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
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
