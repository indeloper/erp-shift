<?php

namespace App\Providers;

use App\Models\{Comment,
    Manual\ManualMaterial,
    MatAcc\MaterialAccountingMaterialFile,
    Notification,
    Task,
    TechAcc\FuelTank\FuelTankOperation,
    User};
use App\Models\TechAcc\Vehicles\{
    OurVehicles,
    VehicleCategories,
    VehicleCategoryCharacteristics
};
use App\Models\TechAcc\OurTechnicTicketReport;
use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\Defects\Defects;
use App\Models\MatAcc\MaterialAccountingOperation;
use App\Models\Vacation\VacationsHistory;
use App\Models\CommercialOffer\CommercialOffer;
use App\Models\Manual\ManualNodeMaterials;
use App\Models\Manual\ManualMaterialCategory;

use App\Models\WorkVolume\WorkVolumeMaterial;
use App\Models\WorkVolume\WorkVolumeWork;

use App\Models\CommercialOffer\CommercialOfferMaterialSplit;
use App\Models\CommercialOffer\CommercialOfferWork;

use App\Models\MatAcc\MaterialAccountingBase;

use App\Observers\{CommentObserver,
    DefectObserver,
    Manual\ManualMaterialObserver,
    MaterialAccountingMaterialFileObserver,
    NotificationObserver,
    OurVehicleObserver,
    TaskObserver,
    TechAcc\FuelTank\FuelTankOperationObserver,
    UserObserver,
    VacationsHistoryObserver,
    VehicleCategoryCharacteristicObserver,
    VehicleCategoryObserver,
    CommercialOffersObserver,
    MaterialAccountingOperationObserver};

use App\Observers\TechAcc\FuelTank\FuelTankObserver;
use App\Observers\TechAcc\OurTechnicTicketReportObserver;

use App\Observers\Manual\ManualNodeMaterialsObserver;
use App\Observers\Manual\ManualMaterialCategoryObserver;

use App\Observers\WorkVolumes\WorkVolumeMaterialObserver;
use App\Observers\WorkVolumes\WorkVolumeWorkObserver;

use App\Observers\CommercialOffers\CommercialOfferMaterialSplitObserver;
use App\Observers\CommercialOffers\CommercialOfferWorkObserver;

use App\Observers\MaterialAccounting\MaterialAccountingBaseObserver;

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

        WorkVolumeMaterial::observe(WorkVolumeMaterialObserver::class);
        WorkVolumeWork::observe(WorkVolumeWorkObserver::class);

        CommercialOfferMaterialSplit::observe(CommercialOfferMaterialSplitObserver::class);
        CommercialOfferWork::observe(CommercialOfferWorkObserver::class);


        MaterialAccountingBase::observe(MaterialAccountingBaseObserver::class);
        VehicleCategories::observe(VehicleCategoryObserver::class);
        VehicleCategoryCharacteristics::observe(VehicleCategoryCharacteristicObserver::class);
        OurVehicles::observe(OurVehicleObserver::class);
        CommercialOffer::observe(CommercialOffersObserver::class);
        Defects::observe(DefectObserver::class);
        Comment::observe(CommentObserver::class);
        OurTechnicTicketReport::observe(OurTechnicTicketReportObserver::class);
        FuelTank::observe(FuelTankObserver::class);
        FuelTankOperation::observe(FuelTankOperationObserver::class);
        MaterialAccountingOperation::observe(MaterialAccountingOperationObserver::class);
        MaterialAccountingMaterialFile::observe(MaterialAccountingMaterialFileObserver::class);
        ManualMaterial::observe(ManualMaterialObserver::class);
    }
}
