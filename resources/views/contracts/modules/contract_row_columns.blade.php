<td data-label="№" class="text-center ">@{{ contract.internal_id }}</td>
<td data-label="Внешний №" class="wrapword-xl" >
    <div class="d-flex">
        <div v-if="parent && contract.main_contract_id" style="height: 12px" class="d-xs-none d-md-block">
            <div style="width: 30px; height: 100%; margin-right: 10px" class="flex-column">
                <div style="border-bottom: 2px solid grey; border-left: 2px solid grey; width: 100%; height: 100%"></div>
                <div :style="contract.id !== contracts.slice().filter(el => el.main_contract_id === parent.id).pop().id
                    ? 'border-left: 2px solid grey; width: 100%; height: 100%'
                    : 'width: 100%; height: 100%'"></div>
            </div>
        </div>
        <div>
            @{{ contract.foreign_id ? contract.foreign_id : 'Отсутствует' }}
        </div>
    </div>
    <template v-if="contract.main_contract_id">
        <br class="d-md-none">
        <a class="table-link d-md-none" :href="'{{ route('projects::contract::card', ['project_id', 'main_contract_id']) }}'
        .split('project_id').join(contract.project_id).split('main_contract_id').join(contract.main_contract_id)">
            <u>Осн. договор: @{{ contract.main_contract.name_for_humans }} </u>
        </a>
    </template>
</td>
<td data-label="Контрагент" class="wrapword-xl">
    <a :href="'{{ route('contractors::card', 'contractor_id') }}'
        .split('contractor_id').join(contract.contractor_id)" class="table-link">@{{ contract.contractor_name }}</a>
</td>
<td data-label="Адрес" class="wrapword-xl">
    <a :href="'{{ route('contractors::card', 'contractor_id') }}'
        .split('contractor_id').join(contract.contractor_id)" class="table-link">@{{ contract.object_address }}</a>
</td>
<td data-label="Проект" class="wrapword-xl">@{{ contract.project_name }}</td>
<td data-label="Тип" :data-target="rowTypeCondition(contract) ? `.collapseContract${contract.contract_id}` : ''"
    :data-toggle="rowTypeCondition(contract) ? 'collapse' : ''" :class="rowTypeCondition(contract) ? 'collapsed tr-pointer' : ''" :aria-expanded="rowTypeCondition(contract) ? 'false' : ''">
    @{{ contract.name }}
</td>
<td data-label="Дата добавления"  class="wrapword-xl">
    <span :class="isWeekendDay(contract.created_at, 'DD.MM.YYYY HH:mm:ss') ? 'weekend-day' : ''">
        @{{ isValidDate(contract.created_at, 'DD.MM.YYYY HH:mm:ss') ? weekdayDate(contract.created_at, 'DD.MM.YYYY HH:mm:ss', 'DD.MM.YYYY dd HH:mm:ss') : '-' }}
    </span>
</td>
<td data-label="Версия" class="wrapword-xl text-center">@{{ contract.version }}</td>
<td data-label="Статус"  class="wrapword-xl">@{{ statuses[contract.status] }}</td>
<td data-label="Юр. Лицо"  class="wrapword-xl">@{{ entities[contract.project_entity] }}</td>
<td data-label="" class="td-actions text-right actions">
    <a v-if="contract.garant_file_name" rel="tooltip" target="_blank"
       :href="'{{ asset('storage/docs/contracts/' . 'garant_file_name') }}'.split('garant_file_name').join(contract.garant_file_name)"
       class="btn-info btn-link btn-xs btn btn-space" data-original-title="Просмотр гарантийного письма">
        <i class="fa fa-file-text-o"></i>
    </a>
    <a v-if="contract.status === 6" rel="tooltip"  target="_blank"
       :href="'{{ asset('storage/docs/contracts/' . 'final_file_name') }}'.split('final_file_name').join(contract.final_file_name)"
       class="btn btn-success btn-link btn-xs btn-space" data-original-title="Просмотр подписанного договора">
        <i class="fa fa-eye"></i>
    </a>
    <a v-if="contract.status > 1 && contract.status !== 6" rel="tooltip" target="_blank"
       :href="'{{ asset('storage/docs/contracts/' . 'file_name') }}'.split('file_name').join(contract.file_name)"
       class="btn btn-info btn-link btn-xs btn-space" data-original-title="Просмотр договора">
        <i class="fa fa-eye"></i>
    </a>
    <a v-if="contract.main_contract_id" rel="tooltip"
       :href="'{{ route('projects::contract::card', ['project_id', 'main_contract_id']) }}'
        .split('project_id').join(contract.project_id).split('main_contract_id').join(contract.main_contract_id)"
       class="btn btn-info btn-link btn-xs btn-space" data-original-title="Просмотр основного договора">
        <i class="fa fa-home"></i>
    </a>
    <a :href="'{{ route('projects::contract::card', ['project_id', 'subst_id']) }}'
        .split('project_id').join(contract.project_id).split('subst_id').join(contract.id)" rel="tooltip"
       class="btn-link btn-xs btn btn-open btn-space" data-original-title="Открыть">
        <i class="fa fa-share-square-o"></i>
    </a>
</td>
