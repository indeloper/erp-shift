<?php

namespace App\Providers;

use App\Models\{Comment,
    Manual\ManualMaterial,
    MatAcc\MaterialAccountingMaterialFile,
    Project,
    Notification,
    Task,
    TechAcc\FuelTank\FuelTankOperation,
    User};
use App\Models\TechAcc\Vehicles\{
    OurVehicles,
    VehicleCategories,
    VehicleCategoryCharacteristics
};
use App\Models\TechAcc\FuelTank\{FuelTank};
use App\Models\TechAcc\OurTechnicTicketReport;
use App\Models\TechAcc\Defects\Defects;
use App\Models\MatAcc\{MaterialAccountingOperation, MaterialAccountingBase};
use App\Models\Vacation\VacationsHistory;
use App\Models\Manual\{ManualNodeMaterials, ManualMaterialCategory, ManualReference};
use App\Models\WorkVolume\{WorkVolumeMaterial, WorkVolumeWork};
use App\Models\CommercialOffer\{CommercialOffer, CommercialOfferMaterialSplit, CommercialOfferWork};

use App\Observers\MaterialAccounting\MaterialAccountingBaseObserver;
use App\Observers\{
    CommentObserver,
    DefectObserver,
    Manual\ManualMaterialObserver,
    MaterialAccountingMaterialFileObserver,
    NotificationObserver,
    OurVehicleObserver,
    ProjectObserver,
    TaskObserver,
    UserObserver,
    VacationsHistoryObserver,
    VehicleCategoryCharacteristicObserver,
    VehicleCategoryObserver,
    CommercialOffersObserver,
    MaterialAccountingOperationObserver};

use App\Observers\TechAcc\FuelTank\{FuelTankOperationObserver, FuelTankObserver};
use App\Observers\Manual\{ManualNodeMaterialsObserver, ManualMaterialCategoryObserver, ManualReferenceObserver};
use App\Observers\WorkVolumes\{WorkVolumeMaterialObserver, WorkVolumeWorkObserver};
use App\Observers\CommercialOffers\{CommercialOfferMaterialSplitObserver, CommercialOfferWorkObserver};
use App\Observers\TechAcc\OurTechnicTicketReportObserver;

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
