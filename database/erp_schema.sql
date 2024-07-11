create table if not exists action_logs
(
    id           bigint unsigned auto_increment
        primary key,
    logable_type varchar(255)                 not null,
    logable_id   bigint unsigned              not null,
    user_id      int unsigned                 not null,
    actions      longtext collate utf8mb4_bin not null,
    created_at   timestamp                    null,
    updated_at   timestamp                    null,
    deleted_at   timestamp                    null
)
    collate = utf8mb4_unicode_ci;

create index if not exists action_logs_logable_type_logable_id_index
    on action_logs (logable_type, logable_id);

create table if not exists appointments
(
    id                   bigint unsigned auto_increment
        primary key,
    appointmentable_type varchar(255)    not null,
    appointmentable_id   bigint unsigned not null,
    project_id           bigint unsigned not null,
    created_at           timestamp       null,
    updated_at           timestamp       null,
    deleted_at           timestamp       null
)
    collate = utf8mb4_unicode_ci;

create index if not exists appointments_appointmentable_type_appointmentable_id_index
    on appointments (appointmentable_type, appointmentable_id);

create index if not exists appointments_project_id_index
    on appointments (project_id);

create table if not exists brigades
(
    id         bigint unsigned auto_increment
        primary key,
    number     bigint       not null,
    direction  smallint     not null,
    foreman_id int unsigned null,
    user_id    int unsigned not null,
    deleted_at timestamp    null,
    created_at timestamp    null,
    updated_at timestamp    null
)
    collate = utf8mb4_unicode_ci;

create index if not exists brigades_foreman_id_index
    on brigades (foreman_id);

create index if not exists brigades_user_id_index
    on brigades (user_id);

create table if not exists category_characteristic_technic
(
    id                         bigint unsigned auto_increment
        primary key,
    technic_id                 int          not null,
    category_characteristic_id int          not null,
    value                      varchar(255) null,
    created_at                 timestamp    null,
    updated_at                 timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists category_characteristics
(
    id          bigint unsigned auto_increment
        primary key,
    name        varchar(255)         not null,
    description varchar(255)         null,
    is_hidden   tinyint(1) default 0 not null,
    required    tinyint(1) default 0 not null,
    unit        varchar(255)         null,
    created_at  timestamp            null,
    updated_at  timestamp            null,
    deleted_at  timestamp            null
)
    collate = utf8mb4_unicode_ci;

create table if not exists com_offer_gantts
(
    id           int unsigned auto_increment
        primary key,
    com_offer_id int unsigned           not null,
    gantt_image  varchar(255)           not null,
    `order`      int unsigned default 1 not null,
    created_at   timestamp              null,
    updated_at   timestamp              null
)
    collate = utf8mb4_unicode_ci;

create table if not exists comments
(
    id               int unsigned auto_increment
        primary key,
    commentable_id   int unsigned         not null,
    commentable_type varchar(255)         not null,
    comment          text                 null,
    author_id        int unsigned         not null,
    created_at       timestamp            null,
    updated_at       timestamp            null,
    `system`         tinyint(1) default 0 not null,
    count            int                  null
)
    collate = utf8mb4_unicode_ci;

create table if not exists commercial_offer_advancements
(
    id                  int unsigned auto_increment
        primary key,
    commercial_offer_id int unsigned         not null,
    is_percent          tinyint(1) default 0 not null,
    value               double(8, 2)         not null,
    description         text                 null,
    created_at          timestamp            null,
    updated_at          timestamp            null
)
    collate = utf8mb4_unicode_ci;

create table if not exists commercial_offer_manual_notes
(
    id                    int unsigned auto_increment
        primary key,
    name                  text                   not null,
    need_value            tinyint(1)   default 0 not null,
    commercial_offer_type int unsigned default 1 not null comment 'Тип коммерческого предложения. Поле для фильтрации в КП. 1 - шпунтовое КП, 2 - свайное КП',
    created_at            timestamp              null,
    updated_at            timestamp              null,
    deleted_at            timestamp              null
)
    collate = utf8mb4_unicode_ci;

create table if not exists commercial_offer_manual_requirements
(
    id                    int unsigned auto_increment
        primary key,
    name                  text                   not null,
    need_value            tinyint(1)   default 0 not null,
    commercial_offer_type int unsigned default 1 not null comment 'Тип коммерческого предложения. Поле для фильтрации в КП. 1 - шпунтовое КП, 2 - свайное КП',
    created_at            timestamp              null,
    updated_at            timestamp              null,
    deleted_at            timestamp              null
)
    collate = utf8mb4_unicode_ci;

create table if not exists commercial_offer_material_splits
(
    id                    int unsigned auto_increment
        primary key,
    man_mat_id            int unsigned                   not null,
    type                  int unsigned                   not null,
    count                 double(10, 3)                  null,
    time                  int unsigned                   null,
    security_price_one    double(15, 2)                  null,
    security_price_result double(15, 2)                  null,
    created_at            timestamp                      null,
    updated_at            timestamp                      null,
    price_per_one         varchar(255)                   null,
    result_price          varchar(255)                   null,
    com_offer_id          int unsigned                   not null,
    subcontractor_file_id int unsigned                   null,
    is_hidden             tinyint(1)   default 0         not null,
    is_used               tinyint(1)   default 0         not null,
    material_type         varchar(255) default 'regular' not null,
    unit                  varchar(20)  default 'шт'      not null,
    parent_id             bigint                         null,
    comment               text                           null
)
    collate = utf8mb4_unicode_ci;

create table if not exists commercial_offer_notes
(
    id                  int unsigned auto_increment
        primary key,
    commercial_offer_id int unsigned not null,
    note                text         not null,
    created_at          timestamp    null,
    updated_at          timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists commercial_offer_request_files
(
    id            int unsigned auto_increment
        primary key,
    request_id    int unsigned         not null,
    is_result     tinyint(1)           not null,
    file_name     varchar(255)         not null,
    original_name varchar(255)         not null,
    created_at    timestamp            null,
    updated_at    timestamp            null,
    is_proj_doc   tinyint(1) default 0 not null
)
    collate = utf8mb4_unicode_ci;

create table if not exists commercial_offer_requests
(
    id                  int unsigned auto_increment
        primary key,
    user_id             int unsigned           not null,
    project_id          int unsigned           not null,
    commercial_offer_id int unsigned           not null,
    status              int unsigned default 0 not null,
    description         text                   null,
    result_comment      text                   null,
    created_at          timestamp              null,
    updated_at          timestamp              null,
    is_tongue           tinyint(1)   default 0 not null
)
    collate = utf8mb4_unicode_ci;

create table if not exists commercial_offer_requirements
(
    id                  int unsigned auto_increment
        primary key,
    commercial_offer_id int unsigned not null,
    requirement         text         not null,
    created_at          timestamp    null,
    updated_at          timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists commercial_offer_works
(
    id                    bigint unsigned auto_increment
        primary key,
    user_id               int unsigned             not null,
    commercial_offer_id   int unsigned             not null,
    work_volume_work_id   int unsigned             null,
    manual_work_id        int unsigned             not null,
    count                 double(10, 3)            null,
    term                  int unsigned             null,
    is_tongue             tinyint(1)               not null,
    price_per_one         double(15, 2)            null,
    result_price          double(15, 2)            null,
    subcontractor_file_id int unsigned             null,
    is_hidden             tinyint(1)  default 0    not null,
    `order`               int         default 1    not null,
    created_at            timestamp                null,
    updated_at            timestamp                null,
    unit                  varchar(20) default 'шт' not null
)
    collate = utf8mb4_unicode_ci;

create table if not exists commercial_offers
(
    id              int unsigned auto_increment
        primary key,
    project_id      int unsigned                        not null,
    name            varchar(255)                        not null,
    file_name       varchar(255)                        not null,
    user_id         int unsigned                        not null,
    status          int unsigned                        not null,
    version         varchar(255) default '0'            not null,
    created_at      timestamp                           null,
    updated_at      timestamp                           null,
    work_volume_id  int unsigned                        null,
    signer_user_id  int unsigned                        null,
    is_tongue       tinyint(1)   default 1              not null,
    is_uploaded     tinyint(1)   default 0              null,
    contact_id      int unsigned                        null,
    nds             double(8, 2) default 20.00          not null,
    contract_number varchar(255)                        null,
    contract_date   varchar(255)                        null,
    budget          varchar(255)                        null,
    title           text                                null,
    deleted_at      timestamp                           null,
    `option`        varchar(255) default 'По умолчанию' not null,
    is_signed       tinyint(1)   default 0              null
)
    collate = utf8mb4_unicode_ci;

create table if not exists companies_legal_forms
(
    id         bigint unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    short_name varchar(255) not null comment 'Краткое название формы предприятия',
    name       varchar(255) not null comment 'Полное название формы предприятия',
    created_at timestamp    null,
    updated_at timestamp    null
)
    comment 'Таблица с данными о формах предприятий' collate = utf8mb4_unicode_ci;

create table if not exists companies
(
    id                         int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    company_1c_uid             varchar(255)              null comment 'Уникальный идентификатор в 1С',
    legal_form_id              bigint unsigned default 1 not null,
    name                       varchar(255)              not null comment 'Наименование организации',
    full_name                  varchar(255)              not null comment 'Полное наименование организации',
    legal_address              varchar(255)              not null comment 'Юридический адрес',
    actual_address             varchar(255)              null comment 'Фактический адрес',
    phone                      varchar(255)              null comment 'Телефон',
    ogrn                       varchar(255)              null comment 'ОГРН',
    inn                        varchar(255)              null comment 'ИНН',
    web_site                   varchar(255)              null comment 'Адрес сайта',
    email                      varchar(255)              not null comment 'Email',
    logo                       varchar(255)              null comment 'Файл с логотипом',
    chief_engineer_employee_id bigint unsigned           null comment 'Идентификатор сотрудника с должностью «Главный инженер»',
    ceo_employee_id            bigint unsigned           null comment 'Идентификатор сотрудника с должностью «Генеральный директор»',
    created_at                 timestamp                 null,
    updated_at                 timestamp                 null,
    deleted_at                 timestamp                 null,
    constraint companies_legal_form_id_foreign
        foreign key (legal_form_id) references companies_legal_forms (id)
)
    comment 'Список организаций' collate = utf8mb4_unicode_ci;

create index if not exists companies_ceo_employee_id_index
    on companies (ceo_employee_id);

create index if not exists companies_chief_engineer_employee_id_index
    on companies (chief_engineer_employee_id);

create table if not exists company_report_template_types
(
    id         int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    name       int unsigned not null comment 'Значение',
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null
)
    comment 'Типы шаблонов для бланков компаний' collate = utf8mb4_unicode_ci;

create table if not exists company_report_templates
(
    id            int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    company_id    int unsigned not null comment 'ID организации',
    template_type int unsigned not null comment 'Тип шаблона',
    template      text         not null comment 'Шаблон',
    created_at    timestamp    null,
    updated_at    timestamp    null,
    deleted_at    timestamp    null,
    constraint company_report_templates_company_id_foreign
        foreign key (company_id) references companies (id),
    constraint company_report_templates_template_type_foreign
        foreign key (template_type) references company_report_template_types (id)
)
    comment 'Шаблоны для отчетов по компаниям' collate = utf8mb4_unicode_ci;

create table if not exists contract_commercial_offer_relations
(
    id                  bigint unsigned auto_increment
        primary key,
    contract_id         int unsigned not null,
    commercial_offer_id int unsigned not null,
    created_at          timestamp    null,
    updated_at          timestamp    null
)
    collate = utf8mb4_unicode_ci;

create index if not exists contract_commercial_offer_relations_commercial_offer_id_index
    on contract_commercial_offer_relations (commercial_offer_id);

create index if not exists contract_commercial_offer_relations_contract_id_index
    on contract_commercial_offer_relations (contract_id);

create table if not exists contract_files
(
    id            int unsigned auto_increment
        primary key,
    contract_id   int unsigned not null,
    name          varchar(255) not null,
    file_name     varchar(255) not null,
    original_name varchar(255) not null,
    created_at    timestamp    null,
    updated_at    timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists contract_key_dates
(
    id          bigint unsigned auto_increment
        primary key,
    contract_id int unsigned not null,
    key_date_id int unsigned null,
    name        varchar(255) null,
    sum         varchar(255) null,
    date_from   timestamp    null,
    date_to     timestamp    null,
    note        text         null,
    created_at  timestamp    null,
    updated_at  timestamp    null,
    deleted_at  timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists contract_key_dates_preselected_names
(
    id         bigint unsigned auto_increment
        primary key,
    value      varchar(255) not null,
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists contract_request_files
(
    id            int unsigned auto_increment
        primary key,
    request_id    int unsigned         not null,
    is_result     tinyint(1)           not null,
    file_name     varchar(255)         not null,
    original_name varchar(255)         not null,
    created_at    timestamp            null,
    updated_at    timestamp            null,
    is_proj_doc   tinyint(1) default 0 not null
)
    collate = utf8mb4_unicode_ci;

create table if not exists contract_requests
(
    id             int unsigned auto_increment
        primary key,
    user_id        int unsigned           not null,
    project_id     int unsigned           not null,
    contract_id    int unsigned           not null,
    status         int unsigned default 1 not null,
    name           varchar(255)           not null,
    description    varchar(255)           null,
    result_comment varchar(255)           null,
    created_at     timestamp              null,
    updated_at     timestamp              null,
    thesis_id      int unsigned           null
)
    collate = utf8mb4_unicode_ci;

create table if not exists contract_theses
(
    id          int unsigned auto_increment
        primary key,
    user_id     int unsigned           not null,
    contract_id int unsigned           not null,
    status      int unsigned default 1 not null,
    name        varchar(255)           not null,
    description text                   not null,
    created_at  timestamp              null,
    updated_at  timestamp              null
)
    collate = utf8mb4_unicode_ci;

create table if not exists contract_thesis_files
(
    id            int unsigned auto_increment
        primary key,
    thesis_id     int unsigned not null,
    file_name     varchar(255) not null,
    original_name varchar(255) not null,
    created_at    timestamp    null,
    updated_at    timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists contract_thesis_verifiers
(
    id         int unsigned auto_increment
        primary key,
    user_id    int unsigned             not null,
    created_at timestamp                null,
    updated_at timestamp                null,
    thesis_id  int unsigned             not null,
    status     varchar(255) default '1' not null
)
    collate = utf8mb4_unicode_ci;

create table if not exists contractor_additional_types
(
    id              bigint unsigned auto_increment
        primary key,
    contractor_id   int unsigned not null,
    additional_type int unsigned not null,
    user_id         int unsigned not null,
    created_at      timestamp    null,
    updated_at      timestamp    null,
    deleted_at      timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists contractor_contact_phones
(
    id           int unsigned auto_increment
        primary key,
    contact_id   int unsigned           not null,
    name         varchar(255)           not null,
    phone_number varchar(255)           not null,
    dop_phone    varchar(255)           null,
    type         int unsigned default 0 not null,
    is_main      tinyint(1)   default 0 not null,
    created_at   timestamp              null,
    updated_at   timestamp              null
)
    collate = utf8mb4_unicode_ci;

create table if not exists contractor_contacts
(
    id            int unsigned auto_increment
        primary key,
    contractor_id int unsigned not null,
    first_name    varchar(255) not null,
    last_name     varchar(255) not null,
    patronymic    varchar(255) null,
    position      varchar(255) null,
    email         varchar(255) null,
    phone_number  varchar(255) not null,
    note          varchar(255) null,
    created_at    timestamp    null,
    updated_at    timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists contractor_files
(
    id                  int unsigned auto_increment
        primary key,
    created_at          timestamp    null,
    updated_at          timestamp    null,
    commercial_offer_id int unsigned not null,
    contractor_id       int unsigned not null,
    file_name           varchar(255) not null,
    original_name       varchar(255) not null,
    type                int unsigned null
)
    collate = utf8mb4_unicode_ci;

create table if not exists contractor_phones
(
    id            int unsigned auto_increment
        primary key,
    contractor_id int unsigned           not null,
    name          varchar(255)           not null,
    phone_number  varchar(255)           not null,
    dop_phone     varchar(255)           null,
    type          int unsigned default 0 not null,
    is_main       tinyint(1)   default 0 not null,
    created_at    timestamp              null,
    updated_at    timestamp              null
)
    collate = utf8mb4_unicode_ci;

create table if not exists contractor_types
(
    id         bigint unsigned auto_increment
        primary key,
    name       varchar(255) not null comment 'Наименование',
    slug       varchar(255) not null comment 'Кодовое наименование',
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null
)
    comment 'Типы контрагентов' collate = utf8mb4_unicode_ci;

create table if not exists contractors
(
    id              int unsigned auto_increment
        primary key,
    full_name       varchar(255)           not null,
    short_name      varchar(255)           not null,
    inn             varchar(255)           null,
    kpp             varchar(255)           null,
    ogrn            varchar(255)           null,
    legal_address   varchar(255)           null,
    physical_adress varchar(255)           null,
    general_manager varchar(255)           null,
    phone_number    varchar(255)           null,
    email           varchar(255)           null,
    main_type       int unsigned           null,
    in_archive      tinyint(1) default 0   not null,
    created_at      timestamp              null,
    updated_at      timestamp              null,
    is_client       int        default 1   not null,
    notify          varchar(1) default '0' not null,
    user_id         int unsigned           null,
    deleted_at      timestamp              null,
    constraint contractors_email_unique
        unique (email)
)
    collate = utf8mb4_unicode_ci;

create table if not exists bank_details
(
    id            int unsigned auto_increment
        primary key,
    contractor_id int unsigned not null,
    check_account varchar(255) null,
    bik           varchar(255) null,
    cor_account   varchar(255) null,
    bank_name     varchar(255) null,
    created_at    timestamp    null,
    updated_at    timestamp    null,
    constraint bank_details_contractor_id_foreign
        foreign key (contractor_id) references contractors (id)
            on delete cascade
)
    collate = utf8mb4_unicode_ci;

create table if not exists contracts
(
    id                     int unsigned auto_increment
        primary key,
    project_id             int unsigned           not null,
    name                   varchar(255)           not null,
    commercial_offer_id    varchar(255)           null,
    user_id                int unsigned           not null,
    status                 int unsigned default 1 not null,
    version                int unsigned default 1 not null,
    created_at             timestamp              null,
    updated_at             timestamp              null,
    foreign_id             varchar(255)           null,
    file_name              varchar(255)           null,
    subcontractor_id       int unsigned default 0 not null,
    garant_file_name       varchar(255)           null,
    final_file_name        varchar(255)           null,
    contract_id            int unsigned           null,
    type                   varchar(255)           null,
    main_contract_id       int unsigned           null,
    deleted_at             timestamp              null,
    ks_date                varchar(255)           null,
    start_notifying_before int                    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists defects
(
    id                  bigint unsigned auto_increment
        primary key,
    user_id             int unsigned             not null,
    defectable_id       int unsigned             not null,
    defectable_type     varchar(255)             not null,
    description         text                     not null,
    status              varchar(255) default '1' not null,
    responsible_user_id int unsigned             null,
    repair_start_date   timestamp                null,
    repair_end_date     timestamp                null,
    created_at          timestamp                null,
    updated_at          timestamp                null,
    deleted_at          timestamp                null
)
    collate = utf8mb4_unicode_ci;

create table if not exists departments
(
    id         int unsigned auto_increment
        primary key,
    created_at timestamp    null,
    updated_at timestamp    null,
    name       varchar(255) not null
)
    collate = utf8mb4_unicode_ci;

create table if not exists employees_1c_payments_deductions
(
    id                          bigint unsigned auto_increment comment 'Уникальный идентификатор записи о платежах/вычетах сотрудника из 1С'
        primary key,
    payments_deductions_1c_name varchar(255) not null comment 'Наименование платежа/вычета из 1С',
    synonym                     varchar(255) null comment 'Синоним (псевдоним) для удобства использования',
    use_in_export               tinyint(1)   null comment 'Флаг использования в процессе экспорта данных',
    created_at                  timestamp    null,
    updated_at                  timestamp    null,
    deleted_at                  timestamp    null
)
    comment 'Таблица платежей/вычетов сотрудников из 1С' collate = utf8mb4_unicode_ci;

create table if not exists employees_1c_posts
(
    id                 int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    name               varchar(255) not null comment 'Наименование должности',
    declination_format varchar(255) not null comment 'Формат склонения',
    post_1c_uid        varchar(255) not null comment 'Наименование отчетной группы',
    company_id         int unsigned not null comment 'ID организации',
    created_at         timestamp    null,
    updated_at         timestamp    null,
    deleted_at         timestamp    null,
    constraint employees_1c_posts_company_id_foreign
        foreign key (company_id) references companies (id)
)
    comment 'Должности сотрудников, синхронизированных c 1С' collate = utf8mb4_unicode_ci;

create table if not exists employees_1c_post_inflections
(
    id            int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    post_id       int unsigned not null comment 'Id должности',
    nominative    varchar(255) null comment 'Именительный падеж',
    genitive      varchar(255) null comment 'Родительный падеж',
    dative        varchar(255) null comment 'Дательный падеж',
    accusative    varchar(255) null comment 'Винительный падеж',
    ablative      varchar(255) null comment 'Творительный падеж',
    prepositional varchar(255) null comment 'Предложный падеж',
    created_at    timestamp    null,
    updated_at    timestamp    null,
    deleted_at    timestamp    null,
    constraint employees_1c_post_inflections_post_id_foreign
        foreign key (post_id) references employees_1c_posts (id)
)
    comment 'Склонения должностей' collate = utf8mb4_unicode_ci;

create table if not exists employees_1c_salaries_groups
(
    id         bigint unsigned auto_increment comment 'Уникальный идентификатор группы зарплаты сотрудников из 1С'
        primary key,
    name       varchar(255) not null comment 'Наименование группы зарплаты',
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null
)
    comment 'Таблица групп зарплаты сотрудников из 1С' collate = utf8mb4_unicode_ci;

create table if not exists employees_1c_subdivisions
(
    id                    int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    subdivision_parent_id int unsigned null comment 'Уникальный идентификатор',
    name                  varchar(255) not null comment 'Наименование должности',
    subdivision_1c_uid    varchar(255) not null comment 'Уникальный идентификатор 1С',
    company_id            int unsigned not null comment 'ID организации',
    created_at            timestamp    null,
    updated_at            timestamp    null,
    deleted_at            timestamp    null,
    constraint employees_1c_subdivisions_company_id_foreign
        foreign key (company_id) references companies (id)
)
    comment 'Список подразделений, синхронизированных с 1С' collate = utf8mb4_unicode_ci;

create table if not exists employees_report_groups
(
    id         int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    name       varchar(255) not null comment 'Наименование отчетной группы',
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null
)
    comment 'Отчетные группы сотрудников. Используются в модуле «Учет рабочего времени» для формирования отчета в excel'
    collate = utf8mb4_unicode_ci;

create table if not exists extra_commercial_offers
(
    id                  int unsigned auto_increment
        primary key,
    project_id          int unsigned not null,
    user_id             int unsigned not null,
    version             int unsigned not null,
    commercial_offer_id int unsigned not null,
    file_name           varchar(255) not null,
    created_at          timestamp    null,
    updated_at          timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists extra_documents
(
    id                  int unsigned auto_increment
        primary key,
    project_id          int unsigned not null,
    user_id             int unsigned not null,
    version             int unsigned not null,
    project_document_id int unsigned not null,
    file_name           varchar(255) not null,
    created_at          timestamp    null,
    updated_at          timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists file_entries
(
    id                int unsigned auto_increment
        primary key,
    filename          varchar(255) not null,
    size              int          not null,
    mime              varchar(255) not null,
    original_filename varchar(255) not null,
    user_id           int unsigned not null,
    created_at        timestamp    null,
    updated_at        timestamp    null,
    documentable_id   int          null,
    documentable_type varchar(255) null
)
    collate = utf8mb4_unicode_ci;

create table if not exists fuel_operations_histories
(
    id                bigint unsigned auto_increment
        primary key,
    user_id           int                          not null,
    fuel_operation_id bigint                       not null,
    changed_fields    longtext collate utf8mb4_bin not null,
    created_at        timestamp                    null,
    updated_at        timestamp                    null,
    deleted_at        timestamp                    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists fuel_tank_flow_types
(
    id         bigint unsigned auto_increment
        primary key,
    name       varchar(255) not null comment 'Наименование',
    slug       varchar(255) not null comment 'Кодовое наименование',
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null
)
    comment 'Типы топливных операций' collate = utf8mb4_unicode_ci;

create table if not exists fuel_tank_operations
(
    id             bigint unsigned auto_increment
        primary key,
    fuel_tank_id   bigint                                not null,
    author_id      bigint                                not null,
    object_id      bigint                                not null,
    our_technic_id bigint                                null,
    contractor_id  bigint                                null,
    value          int                                   not null,
    type           int                                   not null,
    description    text                                  null,
    operation_date timestamp default current_timestamp() not null on update current_timestamp(),
    created_at     timestamp                             null,
    updated_at     timestamp                             null,
    deleted_at     timestamp                             null,
    owner_id       int                                   null,
    result_value   double(13, 3)                         null
)
    collate = utf8mb4_unicode_ci;

create table if not exists `groups`
(
    id            int unsigned auto_increment
        primary key,
    created_at    timestamp    null,
    updated_at    timestamp    null,
    name          varchar(255) not null,
    department_id int unsigned not null,
    constraint groups_department_id_foreign
        foreign key (department_id) references departments (id)
            on delete cascade
)
    collate = utf8mb4_unicode_ci;

create table if not exists job_categories
(
    id              bigint unsigned auto_increment
        primary key,
    name            text         not null,
    report_group_id int unsigned null,
    user_id         int unsigned not null,
    created_at      timestamp    null,
    updated_at      timestamp    null,
    deleted_at      timestamp    null
)
    collate = utf8mb4_unicode_ci;

create index if not exists job_categories_report_group_id_index
    on job_categories (report_group_id);

create index if not exists job_categories_user_id_index
    on job_categories (user_id);

create table if not exists job_category_tariffs
(
    id              bigint unsigned auto_increment
        primary key,
    job_category_id int unsigned not null,
    tariff_id       int unsigned not null,
    user_id         int unsigned not null,
    rate            double(8, 2) not null,
    created_at      timestamp    null,
    updated_at      timestamp    null,
    deleted_at      timestamp    null
)
    collate = utf8mb4_unicode_ci;

create index if not exists job_category_tariffs_job_category_id_index
    on job_category_tariffs (job_category_id);

create index if not exists job_category_tariffs_tariff_id_index
    on job_category_tariffs (tariff_id);

create index if not exists job_category_tariffs_user_id_index
    on job_category_tariffs (user_id);

create table if not exists jobs
(
    id           bigint unsigned auto_increment
        primary key,
    queue        varchar(255)     not null,
    payload      longtext         not null,
    attempts     tinyint unsigned not null,
    reserved_at  int unsigned     null,
    available_at int unsigned     not null,
    created_at   int unsigned     not null
)
    collate = utf8mb4_unicode_ci;

create index if not exists jobs_queue_index
    on jobs (queue);

create table if not exists labor_safety_order_type_categories
(
    id         int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    name       varchar(255) not null comment 'Значение',
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null,
    constraint labor_safety_order_type_categories_name_unique
        unique (name)
)
    comment 'Виды типов приказов в модуле «Охрана труда»' collate = utf8mb4_unicode_ci;

create table if not exists labor_safety_order_types
(
    id                     int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    order_type_category_id int unsigned not null comment 'Вид типа приказа',
    name                   varchar(255) not null comment 'Наименование',
    sort_order             int unsigned not null,
    short_name             varchar(255) not null comment 'Краткое наименование',
    full_name              varchar(255) not null comment 'Краткое наименование',
    template               text         not null comment 'Шаблон',
    created_at             timestamp    null,
    updated_at             timestamp    null,
    deleted_at             timestamp    null,
    constraint labor_safety_order_types_full_name_unique
        unique (full_name),
    constraint labor_safety_order_types_name_unique
        unique (name),
    constraint labor_safety_order_types_short_name_unique
        unique (short_name),
    constraint labor_safety_order_types_order_type_category_id_foreign
        foreign key (order_type_category_id) references labor_safety_order_type_categories (id)
)
    comment 'Типы приказов для формирования в модуле «Охрана труда»' collate = utf8mb4_unicode_ci;

create index if not exists labor_safety_order_types_sort_order_index
    on labor_safety_order_types (sort_order);

create table if not exists labor_safety_request_orders
(
    id             int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    order_type_id  int unsigned not null comment 'ID типа приказа',
    generated_html text         not null comment 'Сгенерированный приказ в html',
    created_at     timestamp    null,
    updated_at     timestamp    null,
    deleted_at     timestamp    null,
    constraint labor_safety_request_orders_order_type_id_foreign
        foreign key (order_type_id) references labor_safety_order_types (id)
)
    comment 'Приказы для заявок на формирование приказов в модуле «Охрана труда»' collate = utf8mb4_unicode_ci;

create table if not exists labor_safety_request_statuses
(
    id         int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    name       varchar(255) not null comment 'Значение',
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null
)
    comment 'Состояние заявок на формирование приказов в модуле «Охрана труда»' collate = utf8mb4_unicode_ci;

create table if not exists labor_safety_worker_types
(
    id         int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    name       varchar(255) not null comment 'Значение',
    sort_order int unsigned not null,
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null
)
    comment 'Типы сотрудников (рабочих), для формирования приказов в модуле «Охрана труда»' collate = utf8mb4_unicode_ci;

create index if not exists labor_safety_worker_types_sort_order_index
    on labor_safety_worker_types (sort_order);

create table if not exists manual_copied_works
(
    id             int unsigned auto_increment
        primary key,
    parent_work_id int unsigned not null,
    child_work_id  int unsigned not null,
    created_at     timestamp    null,
    updated_at     timestamp    null,
    deleted_at     timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists manual_material_categories
(
    id            int unsigned auto_increment
        primary key,
    name          varchar(50)  not null,
    description   varchar(255) null,
    category_unit varchar(255) not null,
    created_at    timestamp    null,
    updated_at    timestamp    null,
    formula       text         null,
    deleted_at    timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists manual_material_category_attributes
(
    id          int unsigned auto_increment
        primary key,
    name        varchar(50)          not null,
    description varchar(255)         null,
    unit        varchar(255)         null,
    is_required tinyint(1)           not null,
    category_id int unsigned         not null,
    created_at  timestamp            null,
    updated_at  timestamp            null,
    is_preset   tinyint(1)           null,
    `from`      varchar(255)         null,
    `to`        varchar(255)         null,
    step        varchar(255)         null,
    value       varchar(255)         null,
    deleted_at  timestamp            null,
    is_display  tinyint(1) default 1 not null
)
    collate = utf8mb4_unicode_ci;

create table if not exists manual_material_category_relation_to_works
(
    id                          bigint unsigned auto_increment
        primary key,
    manual_material_category_id int unsigned not null,
    work_id                     int unsigned not null,
    created_at                  timestamp    null,
    updated_at                  timestamp    null,
    deleted_at                  timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists manual_material_parameters
(
    id         int unsigned auto_increment
        primary key,
    attr_id    int unsigned not null,
    mat_id     int unsigned not null,
    value      varchar(255) not null,
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null
)
    collate = utf8mb4_unicode_ci;

create index if not exists manual_material_parameters_attr_id_index
    on manual_material_parameters (attr_id);

create index if not exists manual_material_parameters_mat_id_index
    on manual_material_parameters (mat_id);

create table if not exists manual_material_passports
(
    id          int unsigned auto_increment
        primary key,
    material_id int unsigned not null,
    name        varchar(255) not null,
    file_name   varchar(255) not null,
    user_id     int unsigned null,
    created_at  timestamp    null,
    updated_at  timestamp    null,
    deleted_at  timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists manual_materials
(
    id                  int unsigned auto_increment
        primary key,
    name                varchar(150)    not null,
    description         varchar(255)    null,
    category_id         int unsigned    not null,
    passport_file       varchar(255)    null,
    buy_cost            double(8, 2)    null,
    use_cost            double(8, 2)    null,
    created_at          timestamp       null,
    updated_at          timestamp       null,
    deleted_at          timestamp       null,
    manual_reference_id bigint unsigned null
)
    collate = utf8mb4_unicode_ci;

create index if not exists manual_materials_category_id_index
    on manual_materials (category_id);

create index if not exists manual_materials_id_index
    on manual_materials (id);

create index if not exists manual_materials_manual_reference_id_index
    on manual_materials (manual_reference_id);

create table if not exists manual_node_categories
(
    id            int unsigned auto_increment
        primary key,
    name          varchar(255)              not null,
    description   text                      null,
    safety_factor double(5, 2) default 0.00 not null,
    created_at    timestamp                 null,
    updated_at    timestamp                 null,
    deleted_at    timestamp                 null
)
    collate = utf8mb4_unicode_ci;

create table if not exists manual_node_materials
(
    id                 int unsigned auto_increment
        primary key,
    node_id            int unsigned             not null,
    manual_material_id int unsigned             not null,
    count              varchar(255)             not null,
    created_at         timestamp                null,
    updated_at         timestamp                null,
    unit               varchar(20) default 'шт' not null,
    deleted_at         timestamp                null
)
    collate = utf8mb4_unicode_ci;

create table if not exists manual_nodes
(
    id               int unsigned auto_increment
        primary key,
    node_category_id int unsigned         not null,
    name             varchar(255)         not null,
    description      text                 null,
    created_at       timestamp            null,
    updated_at       timestamp            null,
    is_compact_wv    tinyint(1) default 0 not null,
    is_compact_cp    tinyint(1) default 0 not null,
    deleted_at       timestamp            null
)
    collate = utf8mb4_unicode_ci;

create table if not exists manual_reference_parameters
(
    id                  bigint unsigned auto_increment
        primary key,
    attr_id             bigint       not null,
    manual_reference_id bigint       not null,
    value               varchar(255) not null,
    deleted_at          timestamp    null,
    created_at          timestamp    null,
    updated_at          timestamp    null
)
    collate = utf8mb4_unicode_ci;

create index if not exists manual_reference_parameters_attr_id_index
    on manual_reference_parameters (attr_id);

create index if not exists manual_reference_parameters_manual_reference_id_index
    on manual_reference_parameters (manual_reference_id);

create table if not exists manual_references
(
    id          bigint unsigned auto_increment
        primary key,
    name        varchar(255) not null,
    description text         null,
    category_id bigint       not null,
    deleted_at  timestamp    null,
    created_at  timestamp    null,
    updated_at  timestamp    null
)
    collate = utf8mb4_unicode_ci;

create index if not exists manual_references_category_id_index
    on manual_references (category_id);

create table if not exists manual_relation_material_works
(
    id                 int unsigned auto_increment
        primary key,
    manual_material_id int unsigned null,
    manual_work_id     int unsigned null,
    created_at         timestamp    null,
    updated_at         timestamp    null,
    deleted_at         timestamp    null
)
    collate = utf8mb4_unicode_ci;

create index if not exists manual_relation_material_works_manual_material_id_index
    on manual_relation_material_works (manual_material_id);

create index if not exists manual_relation_material_works_manual_work_id_index
    on manual_relation_material_works (manual_work_id);

create table if not exists manual_works
(
    id             int unsigned auto_increment
        primary key,
    work_group_id  int unsigned         null,
    name           varchar(150)         null,
    description    varchar(100)         null,
    price_per_unit double(20, 2)        null,
    unit           varchar(15)          null,
    unit_per_days  varchar(5)           null,
    nds            varchar(5)           not null,
    created_at     timestamp            null,
    updated_at     timestamp            null,
    show_materials tinyint(1) default 1 not null,
    is_copied      tinyint(1) default 0 not null,
    deleted_at     timestamp            null
)
    collate = utf8mb4_unicode_ci;

create table if not exists material_accounting_bases
(
    id                 int unsigned auto_increment
        primary key,
    object_id          int unsigned             not null,
    manual_material_id int unsigned             not null,
    date               varchar(255)             not null,
    count              varchar(255)             not null,
    created_at         timestamp                null,
    updated_at         timestamp                null,
    transferred_today  tinyint(1)  default 0    not null,
    unit               varchar(20) default 'шт' not null,
    used               tinyint(1)  default 0    not null,
    ancestor_base_id   bigint unsigned          null
)
    collate = utf8mb4_unicode_ci;

create table if not exists material_accounting_material_additions
(
    id                    int unsigned auto_increment
        primary key,
    operation_id          int unsigned not null,
    operation_material_id int unsigned not null,
    description           varchar(255) null,
    user_id               int unsigned not null,
    created_at            timestamp    null,
    updated_at            timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists material_accounting_material_files
(
    id                    int unsigned auto_increment
        primary key,
    operation_id          int unsigned           not null,
    operation_material_id int unsigned           not null,
    file_name             varchar(255)           not null,
    path                  varchar(255)           not null,
    type                  int unsigned default 1 not null,
    created_at            timestamp              null,
    updated_at            timestamp              null,
    deleted_at            timestamp              null
)
    collate = utf8mb4_unicode_ci;

create table if not exists material_accounting_operation_files
(
    id                 int unsigned auto_increment
        primary key,
    operation_id       int unsigned not null,
    manual_material_id int unsigned not null,
    file_name          varchar(255) not null,
    path               varchar(255) not null,
    user_id            int unsigned not null,
    author_type        int unsigned not null,
    type               int unsigned not null,
    created_at         timestamp    null,
    updated_at         timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists material_accounting_operation_materials
(
    id                  int unsigned auto_increment
        primary key,
    operation_id        int unsigned             not null,
    manual_material_id  int unsigned             not null,
    count               varchar(255) default '0' not null,
    unit                int unsigned             not null,
    type                int unsigned             not null,
    created_at          timestamp                null,
    updated_at          timestamp                null,
    looses              varchar(255) default '0' not null,
    deleted_at          timestamp                null,
    updated_material_id int unsigned             null,
    fact_date           timestamp                null,
    used                tinyint(1)   default 0   not null,
    base_id             bigint unsigned          null
)
    collate = utf8mb4_unicode_ci;

create table if not exists material_accounting_operation_responsible_users
(
    id           int unsigned auto_increment
        primary key,
    operation_id int unsigned         not null,
    user_id      int unsigned         not null,
    type         tinyint(1) default 0 not null,
    created_at   timestamp            null,
    updated_at   timestamp            null
)
    collate = utf8mb4_unicode_ci;

create table if not exists material_accounting_operations
(
    id                int unsigned auto_increment
        primary key,
    type              int unsigned           not null,
    object_id_from    int unsigned           not null,
    object_id_to      int unsigned           not null,
    planned_date_from varchar(255)           not null,
    planned_date_to   varchar(255)           not null,
    actual_date_from  varchar(255)           not null,
    actual_date_to    varchar(255)           not null,
    comment_from      text                   null,
    comment_to        text                   null,
    comment_author    text                   null,
    author_id         int unsigned           not null,
    sender_id         int unsigned           not null,
    recipient_id      int unsigned           not null,
    supplier_id       int unsigned           null,
    responsible_RP    int unsigned           null,
    status            int unsigned           not null,
    is_close          tinyint(1)   default 0 not null,
    reason            text                   null,
    created_at        timestamp              null,
    updated_at        timestamp              null,
    parent_id         int unsigned default 0 not null,
    deleted_at        timestamp              null,
    contract_id       bigint                 null
)
    collate = utf8mb4_unicode_ci;

create table if not exists material_accounting_ttn_materials
(
    id          int unsigned auto_increment
        primary key,
    ttn_id      int unsigned not null,
    count       varchar(255) not null,
    unit        varchar(255) not null,
    material_id int unsigned not null,
    created_at  timestamp    null,
    updated_at  timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists material_accounting_ttns
(
    id                       int unsigned auto_increment
        primary key,
    operation_id             int unsigned not null,
    main_entity              varchar(255) not null,
    take_time                varchar(255) null,
    take_fact_arrival_time   varchar(255) null,
    take_fact_departure_time varchar(255) null,
    take_weight              varchar(255) null,
    take_places_count        varchar(255) null,
    give_time                varchar(255) null,
    give_fact_arrival_time   varchar(255) null,
    give_fact_departure_time varchar(255) null,
    give_weight              varchar(255) null,
    give_places_count        varchar(255) null,
    entity                   varchar(255) null,
    city                     varchar(255) null,
    address                  varchar(255) null,
    `index`                  varchar(255) null,
    phone_number             varchar(255) null,
    driver_name              varchar(255) null,
    driver_phone_number      varchar(255) null,
    vehicle                  varchar(255) null,
    vehicle_number           varchar(255) null,
    trailer                  varchar(255) null,
    trailer_number           varchar(255) null,
    carrier                  varchar(255) null,
    consignor                int unsigned not null,
    created_at               timestamp    null,
    updated_at               timestamp    null,
    main_entity_to           int unsigned null
)
    collate = utf8mb4_unicode_ci;

create table if not exists menu_items
(
    id         bigint unsigned auto_increment
        primary key,
    title      varchar(255)                 not null comment 'Заголовок меню',
    parent_id  bigint unsigned              null comment 'Для подэлементов',
    route_name varchar(255)                 null comment 'Название роута',
    is_su      tinyint(1) default 0         not null comment 'is super users',
    icon_path  varchar(255)                 null,
    gates      longtext collate utf8mb4_bin null,
    actives    longtext collate utf8mb4_bin null,
    status     tinyint(1) default 0         not null,
    deleted_at timestamp                    null,
    created_at timestamp                    null,
    updated_at timestamp                    null,
    constraint menu_items_parent_id_foreign
        foreign key (parent_id) references menu_items (id)
            on update cascade on delete cascade
)
    collate = utf8mb4_unicode_ci;

create table if not exists message_files
(
    id            int unsigned auto_increment
        primary key,
    message_id    int unsigned not null,
    user_id       int unsigned not null,
    path          varchar(255) not null,
    file_name     varchar(255) not null,
    original_name varchar(255) not null,
    type          varchar(255) not null,
    created_at    timestamp    null,
    updated_at    timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists message_forwards
(
    id                   bigint unsigned auto_increment
        primary key,
    message_id           int unsigned not null,
    forwarded_message_id int unsigned not null,
    created_at           timestamp    null,
    updated_at           timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists messages
(
    id           int unsigned auto_increment
        primary key,
    thread_id    int unsigned not null,
    user_id      int unsigned not null,
    body         text         not null,
    has_relation tinyint(1)   not null,
    created_at   timestamp    null,
    updated_at   timestamp    null,
    deleted_at   timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists migrations
(
    id        int unsigned auto_increment
        primary key,
    migration varchar(255) not null,
    batch     int          not null
)
    collate = utf8mb4_unicode_ci;

create table if not exists notification_types
(
    id           bigint unsigned auto_increment
        primary key,
    `group`      int unsigned         not null,
    name         text                 not null,
    for_everyone tinyint(1) default 0 not null,
    created_at   timestamp            null,
    updated_at   timestamp            null,
    deleted_at   timestamp            null
)
    collate = utf8mb4_unicode_ci;

create table if not exists notifications
(
    id                    int unsigned auto_increment
        primary key,
    name                  text                 null,
    description           varchar(255)         null,
    status                int unsigned         null,
    user_id               int unsigned         null,
    contractor_id         int unsigned         null,
    project_id            int unsigned         null,
    object_id             int unsigned         null,
    department_id         int unsigned         null,
    group_id              int unsigned         null,
    is_seen               tinyint(1) default 0 not null,
    is_showing            tinyint(1) default 1 not null,
    type                  int unsigned         null,
    task_id               int unsigned         null,
    voice_url             int unsigned         null,
    created_at            timestamp            null,
    updated_at            timestamp            null,
    is_deleted            tinyint(1) default 0 not null,
    target_id             int unsigned         null,
    notificationable_type varchar(255)         null,
    notificationable_id   bigint unsigned      null
)
    collate = utf8mb4_unicode_ci;

create index if not exists notifications_notificationable_type_notificationable_id_index
    on notifications (notificationable_type, notificationable_id);

create table if not exists notifications_for_groups
(
    id              bigint unsigned auto_increment
        primary key,
    notification_id int unsigned not null,
    group_id        int unsigned not null,
    created_at      timestamp    null,
    updated_at      timestamp    null,
    deleted_at      timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists notifications_for_permissions
(
    id              bigint unsigned auto_increment
        primary key,
    notification_id int unsigned not null,
    permission      varchar(255) not null,
    created_at      timestamp    null,
    updated_at      timestamp    null,
    deleted_at      timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists notifications_for_users
(
    id              bigint unsigned auto_increment
        primary key,
    notification_id int unsigned not null,
    user_id         int unsigned not null,
    created_at      timestamp    null,
    updated_at      timestamp    null,
    deleted_at      timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists oauth_access_tokens
(
    id         varchar(100) not null
        primary key,
    user_id    int          null,
    client_id  int unsigned not null,
    name       varchar(255) null,
    scopes     text         null,
    revoked    tinyint(1)   not null,
    created_at timestamp    null,
    updated_at timestamp    null,
    expires_at datetime     null
)
    collate = utf8mb4_unicode_ci;

create index if not exists oauth_access_tokens_user_id_index
    on oauth_access_tokens (user_id);

create table if not exists oauth_auth_codes
(
    id         varchar(100) not null
        primary key,
    user_id    int          not null,
    client_id  int unsigned not null,
    scopes     text         null,
    revoked    tinyint(1)   not null,
    expires_at datetime     null
)
    collate = utf8mb4_unicode_ci;

create table if not exists oauth_clients
(
    id                     int unsigned auto_increment
        primary key,
    user_id                int          null,
    name                   varchar(255) not null,
    secret                 varchar(100) not null,
    redirect               text         not null,
    personal_access_client tinyint(1)   not null,
    password_client        tinyint(1)   not null,
    revoked                tinyint(1)   not null,
    created_at             timestamp    null,
    updated_at             timestamp    null
)
    collate = utf8mb4_unicode_ci;

create index if not exists oauth_clients_user_id_index
    on oauth_clients (user_id);

create table if not exists oauth_personal_access_clients
(
    id         int unsigned auto_increment
        primary key,
    client_id  int unsigned not null,
    created_at timestamp    null,
    updated_at timestamp    null
)
    collate = utf8mb4_unicode_ci;

create index if not exists oauth_personal_access_clients_client_id_index
    on oauth_personal_access_clients (client_id);

create table if not exists oauth_refresh_tokens
(
    id              varchar(100) not null
        primary key,
    access_token_id varchar(100) not null,
    revoked         tinyint(1)   not null,
    expires_at      datetime     null
)
    collate = utf8mb4_unicode_ci;

create index if not exists oauth_refresh_tokens_access_token_id_index
    on oauth_refresh_tokens (access_token_id);

create table if not exists object_responsible_user_roles
(
    id         bigint unsigned auto_increment comment 'Уникальный идентфикатор'
        primary key,
    slug       varchar(255) not null comment 'Кодовое наименование',
    name       varchar(255) not null comment 'Наименование',
    created_at timestamp    null,
    updated_at timestamp    null
)
    comment 'Роли ответственных на объектах' collate = utf8mb4_unicode_ci;

create index if not exists object_responsible_user_roles_slug_index
    on object_responsible_user_roles (slug);

create table if not exists object_responsible_users
(
    id                              int unsigned auto_increment
        primary key,
    object_id                       int unsigned    not null,
    user_id                         int unsigned    not null,
    object_responsible_user_role_id bigint unsigned not null comment 'ID роли ответственного',
    created_at                      timestamp       null,
    updated_at                      timestamp       null,
    constraint object_responsible_user_role_foreign
        foreign key (object_responsible_user_role_id) references object_responsible_user_roles (id)
)
    comment 'Ответственные на объектах' collate = utf8mb4_unicode_ci;

create table if not exists our_technic_ticket_our_vehicle
(
    our_technic_ticket_id int unsigned not null,
    our_vehicle_id        int unsigned not null,
    created_at            timestamp    null,
    updated_at            timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists our_technic_ticket_reports
(
    id                    bigint unsigned auto_increment
        primary key,
    our_technic_ticket_id int unsigned not null,
    hours                 double       not null,
    user_id               int unsigned not null,
    comment               text         null,
    date                  varchar(10)  null,
    created_at            timestamp    null,
    updated_at            timestamp    null,
    deleted_at            timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists our_technic_ticket_user
(
    tic_id         bigint        not null,
    user_id        bigint        not null,
    type           int default 1 not null,
    created_at     timestamp     null,
    updated_at     timestamp     null,
    deactivated_at varchar(255)  null,
    primary key (tic_id, user_id, type)
)
    collate = utf8mb4_unicode_ci;

create table if not exists our_technic_tickets
(
    id                bigint unsigned auto_increment
        primary key,
    our_technic_id    int unsigned           not null,
    sending_object_id int unsigned           null,
    getting_object_id int unsigned           null,
    usage_days        int unsigned           null,
    status            int unsigned default 1 not null,
    type              int unsigned default 1 not null,
    sending_from_date timestamp              null,
    sending_to_date   timestamp              null,
    getting_from_date timestamp              null,
    getting_to_date   timestamp              null,
    usage_from_date   timestamp              null,
    usage_to_date     timestamp              null,
    comment           text                   null,
    created_at        timestamp              null,
    updated_at        timestamp              null,
    deleted_at        timestamp              null,
    specialization    int                    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists our_vehicle_parameters
(
    id                bigint unsigned auto_increment
        primary key,
    vehicle_id        int unsigned not null,
    characteristic_id int unsigned not null,
    value             varchar(255) null,
    created_at        timestamp    null,
    updated_at        timestamp    null,
    deleted_at        timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists our_vehicles
(
    id             bigint unsigned auto_increment
        primary key,
    category_id    int unsigned not null,
    user_id        int unsigned not null,
    number         varchar(255) not null,
    trailer_number varchar(255) null,
    mark           varchar(255) not null,
    model          varchar(255) not null,
    owner          varchar(255) not null,
    created_at     timestamp    null,
    updated_at     timestamp    null,
    deleted_at     timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists participants
(
    id         int unsigned auto_increment
        primary key,
    thread_id  int unsigned         not null,
    user_id    int unsigned         not null,
    last_read  timestamp            null,
    starred    tinyint(1) default 0 not null,
    created_at timestamp            null,
    updated_at timestamp            null,
    deleted_at timestamp            null
)
    collate = utf8mb4_unicode_ci;

create table if not exists password_resets
(
    email      varchar(255) not null,
    token      varchar(255) not null,
    created_at timestamp    null
)
    collate = utf8mb4_unicode_ci;

create index if not exists password_resets_email_index
    on password_resets (email);

create table if not exists pay_and_holds
(
    id         bigint unsigned auto_increment
        primary key,
    name       text         not null,
    short_name varchar(255) null,
    type       int          not null,
    created_at timestamp    null,
    updated_at timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists permissions
(
    id         int unsigned auto_increment
        primary key,
    name       varchar(255) not null,
    codename   varchar(255) not null,
    created_at timestamp    null,
    updated_at timestamp    null,
    category   int unsigned not null
)
    collate = utf8mb4_unicode_ci;

create table if not exists group_permissions
(
    id            int unsigned auto_increment
        primary key,
    group_id      int unsigned null,
    permission_id int unsigned null,
    created_at    timestamp    null,
    updated_at    timestamp    null,
    constraint group_permissions_group_id_foreign
        foreign key (group_id) references `groups` (id)
            on delete cascade,
    constraint group_permissions_permission_id_foreign
        foreign key (permission_id) references permissions (id)
            on delete cascade
)
    collate = utf8mb4_unicode_ci;

create table if not exists production_calendar_day_types
(
    id         bigint unsigned auto_increment comment 'Уникальный идентификатор типа дня в производственном календаре'
        primary key,
    name       varchar(255) not null comment 'Наименование типа дня',
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null
)
    comment 'Таблица типов дней в производственном календаре' collate = utf8mb4_unicode_ci;

create table if not exists production_calendars
(
    id         bigint unsigned auto_increment comment 'Уникальный идентификатор записи в производственном календаре'
        primary key,
    date       date            not null comment 'Дата в производственном календаре',
    date_type  bigint unsigned not null comment 'Тип дня',
    created_at timestamp       null,
    updated_at timestamp       null,
    deleted_at timestamp       null,
    constraint production_calendars_date_type_foreign
        foreign key (date_type) references production_calendar_day_types (id)
)
    comment 'Таблица производственного календаря' collate = utf8mb4_unicode_ci;

create table if not exists project_contacts
(
    id         int unsigned auto_increment
        primary key,
    project_id int unsigned not null,
    contact_id int unsigned not null,
    note       varchar(255) null,
    created_at timestamp    null,
    updated_at timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists project_contractors
(
    id            int unsigned auto_increment
        primary key,
    project_id    int unsigned not null,
    contractor_id int unsigned not null,
    user_id       int unsigned not null,
    created_at    timestamp    null,
    updated_at    timestamp    null,
    deleted_at    timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists project_contractors_change_histories
(
    id                int unsigned auto_increment
        primary key,
    project_id        int unsigned not null,
    old_contractor_id int unsigned null,
    new_contractor_id int unsigned null,
    user_id           int unsigned not null,
    created_at        timestamp    null,
    updated_at        timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists project_documents
(
    id         int unsigned auto_increment
        primary key,
    project_id int unsigned not null,
    name       varchar(255) not null,
    file_name  varchar(255) not null,
    user_id    int unsigned null,
    version    varchar(255) not null,
    created_at timestamp    null,
    updated_at timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists project_object_document_types
(
    id         bigint unsigned auto_increment comment 'Уникальный идентфикатор'
        primary key,
    sortOrder  int          not null comment 'Порядок сортировки документов',
    name       varchar(255) not null comment 'Наименование',
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null
)
    comment 'Типы документов в модуле «Документооборот на объектах»' collate = utf8mb4_unicode_ci;

create table if not exists project_object_documents_status_types
(
    id         bigint unsigned auto_increment
        primary key,
    name       varchar(255) not null comment 'Наименование',
    slug       varchar(255) not null comment 'Кодовое наименование',
    sortOrder  int          not null comment 'Порядок сортировки',
    style      varchar(255) not null comment 'Цветовая маркировка',
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null
)
    comment 'Типы статусов документов в модуле «Документооборот на объектах»' collate = utf8mb4_unicode_ci;

create table if not exists project_object_document_statuses
(
    id             bigint unsigned auto_increment comment 'Уникальный идентфикатор'
        primary key,
    status_type_id bigint unsigned not null comment 'ID типа статуса',
    sortOrder      int             not null comment 'Порядок сортировки',
    name           varchar(255)    not null comment 'Наименование',
    created_at     timestamp       null,
    updated_at     timestamp       null,
    deleted_at     timestamp       null,
    constraint project_object_document_statuses_status_type_id_foreign
        foreign key (status_type_id) references project_object_documents_status_types (id)
)
    comment 'Статусы документов в модуле «Документооборот на объектах»' collate = utf8mb4_unicode_ci;

create table if not exists project_object_document_status_options
(
    id                 bigint unsigned auto_increment comment 'Уникальный идентфикатор'
        primary key,
    document_type_id   bigint unsigned              not null comment 'ID типа документа',
    document_status_id bigint unsigned              null comment 'ID статуса документа',
    options            longtext collate utf8mb4_bin null comment 'Параметры формы дополнительные',
    created_at         timestamp                    null,
    updated_at         timestamp                    null,
    constraint document_status_id_foreign
        foreign key (document_status_id) references project_object_document_statuses (id),
    constraint document_type_id_foreign
        foreign key (document_type_id) references project_object_document_types (id)
)
    comment 'Дополнительные поля ввода формы в модуле «Документооборот на объектах»' collate = utf8mb4_unicode_ci;

create table if not exists project_object_document_status_type_relations
(
    id                 bigint unsigned auto_increment comment 'Уникальный идентфикатор'
        primary key,
    document_status_id bigint unsigned not null comment 'ID статуса документа',
    document_type_id   bigint unsigned not null comment 'ID типа документа',
    default_selection  tinyint(1)      null comment 'Статус при создании',
    created_at         timestamp       null,
    updated_at         timestamp       null,
    constraint project_object_document_status_foreign
        foreign key (document_status_id) references project_object_document_statuses (id),
    constraint project_object_document_type_foreign
        foreign key (document_type_id) references project_object_document_types (id)
)
    comment 'Связи типов и статусов в модуле «Документооборот на объектах»' collate = utf8mb4_unicode_ci;

create index if not exists project_object_documents_status_types_slug_index
    on project_object_documents_status_types (slug);

create table if not exists project_responsible_user_redirect_histories
(
    id          int unsigned auto_increment
        primary key,
    vacation_id int unsigned null,
    role_id     int unsigned not null,
    project_id  int unsigned not null,
    old_user_id int unsigned not null,
    new_user_id int unsigned not null,
    role        int unsigned not null,
    reason      varchar(255) null,
    created_at  timestamp    null,
    updated_at  timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists project_responsible_users
(
    id         int unsigned auto_increment
        primary key,
    project_id int unsigned not null,
    user_id    int unsigned not null,
    role       varchar(2)   null,
    created_at timestamp    null,
    updated_at timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists projects
(
    id                       int unsigned auto_increment
        primary key,
    contractor_id            int unsigned           not null,
    name                     varchar(255)           not null,
    object_id                int unsigned           not null,
    description              varchar(255)           null,
    status                   int unsigned default 1 not null,
    user_id                  int unsigned           not null,
    is_start                 tinyint(1)   default 0 not null,
    is_important             tinyint(1)   default 0 not null,
    created_at               timestamp              null,
    updated_at               timestamp              null,
    sales_user_id            int unsigned           null,
    time_responsible_user_id bigint unsigned        null,
    entity                   int unsigned default 1 not null,
    deleted_at               timestamp              null,
    is_tongue                tinyint(1)   default 0 not null,
    is_pile                  tinyint(1)   default 0 not null
)
    collate = utf8mb4_unicode_ci;

create index if not exists projects_time_responsible_user_id_index
    on projects (time_responsible_user_id);

create table if not exists q3w_material_accounting_types
(
    id         int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    value      varchar(255) not null comment 'Наименование',
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists q3w_material_brand_types
(
    id         int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    name       varchar(255) not null comment 'Наименование типа марки',
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists q3w_material_comments
(
    id         bigint unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    comment    text         not null comment 'Комментарий',
    author_id  int unsigned not null comment 'Идентификатор автора комментария',
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null
)
    collate = utf8mb4_unicode_ci;

create index if not exists q3w_material_comments_author_id_index
    on q3w_material_comments (author_id);

create table if not exists q3w_material_snapshot_material_comments
(
    id         bigint unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    comment    text         not null comment 'Комментарий',
    author_id  int unsigned not null comment 'Идентификатор автора комментария',
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null
)
    collate = utf8mb4_unicode_ci;

create index if not exists q3w_material_snapshot_material_comments_author_id_index
    on q3w_material_snapshot_material_comments (author_id);

create table if not exists q3w_material_supply_objects
(
    id         bigint unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    name       varchar(255) not null comment 'Наименование объекта для планирования поставок',
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists q3w_material_supply_planning
(
    id                     bigint unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    brand_type_id          int unsigned           not null comment 'Тип марки',
    planning_object_id     bigint unsigned        not null comment 'Идентификатор планируемого объекта',
    quantity               decimal(8, 3) unsigned not null comment 'Количество (в единицах измерения)',
    planned_project_weight double(8, 2) unsigned  not null comment 'Запланированный вес материала по проекту',
    created_at             timestamp              null,
    updated_at             timestamp              null,
    deleted_at             timestamp              null,
    constraint q3w_material_supply_planning_brand_type_id_foreign
        foreign key (brand_type_id) references q3w_material_brand_types (id),
    constraint q3w_material_supply_planning_planning_object_id_foreign
        foreign key (planning_object_id) references q3w_material_supply_objects (id)
)
    comment 'Планирование поставок материалов на объекты' collate = utf8mb4_unicode_ci;

create table if not exists q3w_material_supply_expected_deliveries
(
    id                 bigint unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    supply_planning_id bigint unsigned       not null comment 'Запланированный материал',
    contractor_id      int unsigned          not null comment 'Поставщик',
    quantity           double(8, 2) unsigned not null comment 'Количество (в единицах измерения)',
    amount             int unsigned          not null comment 'Количество (в штуках)',
    created_at         timestamp             null,
    updated_at         timestamp             null,
    deleted_at         timestamp             null,
    constraint q3w_material_supply_expected_deliveries_contractor_id_foreign
        foreign key (contractor_id) references contractors (id),
    constraint supply_planning_id_foreign
        foreign key (supply_planning_id) references q3w_material_supply_planning (id)
)
    collate = utf8mb4_unicode_ci;

create table if not exists q3w_material_transformation_types
(
    id         int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    value      varchar(255) not null comment 'Значение',
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists q3w_measure_units
(
    id         int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    value      varchar(255) not null comment 'Наименование',
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists q3w_material_types
(
    id                   int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    name                 varchar(255)           not null comment 'Наименование',
    description          text                   null comment 'Описание',
    measure_unit         int unsigned           not null comment 'Основная единица измерения',
    measure_instructions text                   null comment 'Инструкция по измерению материала',
    accounting_type      int unsigned default 1 not null comment 'Тип учета',
    created_at           timestamp              null,
    updated_at           timestamp              null,
    deleted_at           timestamp              null,
    constraint q3w_material_types_accounting_type_foreign
        foreign key (accounting_type) references q3w_material_accounting_types (id),
    constraint q3w_material_types_measure_unit_foreign
        foreign key (measure_unit) references q3w_measure_units (id)
)
    collate = utf8mb4_unicode_ci;

create table if not exists q3w_material_brands
(
    id               int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    material_type_id int unsigned not null comment 'Тип материала',
    brand_type_id    int unsigned null comment 'Тип марки материала',
    name             varchar(255) not null comment 'Наименование марки',
    created_at       timestamp    null,
    updated_at       timestamp    null,
    deleted_at       timestamp    null,
    constraint q3w_material_brands_brand_type_id_foreign
        foreign key (brand_type_id) references q3w_material_brands (id),
    constraint q3w_material_brands_material_type_id_foreign
        foreign key (material_type_id) references q3w_material_types (id)
)
    collate = utf8mb4_unicode_ci;

create table if not exists q3w_material_standards
(
    id                     int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    name                   varchar(255)              not null comment 'Наименование эталона',
    material_type          int unsigned              not null comment 'Тип материала',
    weight                 double unsigned           not null comment 'Вес за 1 единицу измерения',
    participates_in_search tinyint(1)      default 0 not null comment 'Участвует в поиске по эталонам в операциях',
    description            text                      null comment 'Описание',
    created_at             timestamp                 null,
    updated_at             timestamp                 null,
    deleted_at             timestamp                 null,
    selection_counter      bigint unsigned default 0 not null comment 'Cчетчик выборы эталона для ранжирования по поулярности',
    constraint q3w_material_standards_name_unique
        unique (name),
    constraint q3w_material_standards_material_type_foreign
        foreign key (material_type) references q3w_material_types (id)
)
    collate = utf8mb4_unicode_ci;

create table if not exists q3w_material_brands_relations
(
    id          int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    brand_id    int unsigned not null comment 'Тип марки материала',
    standard_id int unsigned not null comment 'Тип марки материала',
    created_at  timestamp    null,
    updated_at  timestamp    null,
    deleted_at  timestamp    null,
    constraint q3w_material_brands_relations_brand_id_foreign
        foreign key (brand_id) references q3w_material_brands (id),
    constraint q3w_material_brands_relations_standard_id_foreign
        foreign key (standard_id) references q3w_material_standards (id)
)
    collate = utf8mb4_unicode_ci;

create index if not exists q3w_material_standards_material_type_index
    on q3w_material_standards (material_type);

create index if not exists q3w_material_types_accounting_type_index
    on q3w_material_types (accounting_type);

create index if not exists q3w_material_types_measure_unit_index
    on q3w_material_types (measure_unit);

create table if not exists q3w_operation_file_types
(
    id                int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    name              varchar(255) not null comment 'Наименование',
    string_identifier varchar(255) not null comment 'Строковый идентификатор',
    created_at        timestamp    null,
    updated_at        timestamp    null,
    deleted_at        timestamp    null,
    constraint q3w_operation_file_types_string_identifier_unique
        unique (string_identifier)
)
    collate = utf8mb4_unicode_ci;

create table if not exists q3w_operation_material_comments
(
    id         bigint unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    comment    text         not null comment 'Комментарий',
    author_id  int unsigned not null comment 'Идентификатор автора комментария',
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null
)
    collate = utf8mb4_unicode_ci;

create index if not exists q3w_operation_material_comments_author_id_index
    on q3w_operation_material_comments (author_id);

create table if not exists q3w_operation_route_stage_types
(
    id         int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    name       varchar(255) not null comment 'Наименование маршрута операции',
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists q3w_operation_routes
(
    id         int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    name       varchar(255) not null comment 'Наименование маршрута операции',
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists q3w_operation_route_stages
(
    id                            int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    parent_route_stage_id         int unsigned null comment 'ID предыдущего этапа (q3w_operation_route_stages)',
    operation_route_id            int unsigned not null comment 'Маршрут',
    operation_route_stage_type_id int unsigned not null comment 'Тип маршрута',
    name                          varchar(255) not null comment 'Наименование этапа маршрута',
    created_at                    timestamp    null,
    updated_at                    timestamp    null,
    deleted_at                    timestamp    null,
    human_readable_name           varchar(255) null comment 'Человекочитаемый псевдоним имени маршрута',
    constraint q3w_operation_route_stages_operation_route_id_foreign
        foreign key (operation_route_id) references q3w_operation_routes (id),
    constraint q3w_operation_route_stages_operation_route_stage_type_id_foreign
        foreign key (operation_route_stage_type_id) references q3w_operation_route_stage_types (id)
)
    collate = utf8mb4_unicode_ci;

create index if not exists q3w_operation_route_stages_operation_route_id_index
    on q3w_operation_route_stages (operation_route_id);

create index if not exists q3w_operation_route_stages_operation_route_stage_type_id_index
    on q3w_operation_route_stages (operation_route_stage_type_id);

create index if not exists q3w_operation_route_stages_parent_route_stage_id_index
    on q3w_operation_route_stages (parent_route_stage_id);

create table if not exists q3w_project_object_material_accounting_types
(
    id         int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    name       varchar(255) not null comment 'Наименование',
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists project_objects
(
    id                                     int unsigned auto_increment
        primary key,
    bitrix_id                              bigint                 null comment 'Id в Битрикс',
    name                                   text                   not null,
    address                                text                   not null,
    cadastral_number                       varchar(255)           null,
    is_active                              tinyint(1)   default 1 not null,
    short_name                             text                   null,
    created_at                             timestamp              null,
    updated_at                             timestamp              null,
    material_accounting_type               int unsigned default 1 not null comment 'Идентификатор типа материального учета объекта',
    is_participates_in_material_accounting tinyint(1)             not null comment 'Участвует в материальном учете',
    is_participates_in_documents_flow      tinyint(1)             not null comment 'Участвует в документообороте',
    constraint project_objects_material_accounting_type_foreign
        foreign key (material_accounting_type) references q3w_project_object_material_accounting_types (id)
)
    collate = utf8mb4_unicode_ci;

create index if not exists project_objects_material_accounting_type_index
    on project_objects (material_accounting_type);

create table if not exists q3w_material_supply_materials
(
    id                       bigint unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    standard_id              int unsigned    not null comment 'Эталон',
    supply_planning_id       bigint unsigned not null comment 'Запланированный материал',
    source_project_object_id int unsigned    not null comment 'Объект, с которого планируется поставка',
    weight                   double unsigned not null comment 'Планируемый вес завоза',
    amount                   int unsigned    not null comment 'Количество выбранного материала',
    created_at               timestamp       null,
    updated_at               timestamp       null,
    deleted_at               timestamp       null,
    constraint q3w_material_supply_materials_source_project_object_id_foreign
        foreign key (source_project_object_id) references project_objects (id),
    constraint q3w_material_supply_materials_standard_id_foreign
        foreign key (standard_id) references q3w_material_standards (id),
    constraint q3w_material_supply_materials_supply_planning_id_foreign
        foreign key (supply_planning_id) references q3w_material_supply_planning (id)
)
    comment 'План по материалам на объекте' collate = utf8mb4_unicode_ci;

create table if not exists q3w_materials
(
    id             bigint unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    standard_id    int unsigned    not null comment 'Идентификатор эталона',
    project_object int unsigned    not null comment 'Идентификатор объекта',
    amount         int unsigned    not null comment 'Количество (в штуках)',
    quantity       double unsigned not null comment 'Количество (в единицах измерения)',
    created_at     timestamp       null,
    updated_at     timestamp       null,
    deleted_at     timestamp       null,
    comment_id     bigint unsigned null comment 'Комментарий',
    constraint q3w_materials_comment_id_foreign
        foreign key (comment_id) references q3w_material_comments (id),
    constraint q3w_materials_project_object_foreign
        foreign key (project_object) references project_objects (id),
    constraint q3w_materials_standard_id_foreign
        foreign key (standard_id) references q3w_material_standards (id)
)
    collate = utf8mb4_unicode_ci;

create index if not exists q3w_materials_project_object_index
    on q3w_materials (project_object);

create index if not exists q3w_materials_standard_id_index
    on q3w_materials (standard_id);

create table if not exists q3w_standard_properties
(
    id         int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    name       varchar(255) not null comment 'Наименование свойства',
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists q3w_standard_properties_relations
(
    id                   int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    standard_property_id int unsigned not null comment 'Свойство эталона',
    standard_id          int unsigned not null comment 'Эталон',
    created_at           timestamp    null,
    updated_at           timestamp    null,
    deleted_at           timestamp    null,
    constraint q3w_standard_properties_relations_standard_id_foreign
        foreign key (standard_id) references q3w_material_standards (id),
    constraint q3w_standard_properties_relations_standard_property_id_foreign
        foreign key (standard_property_id) references q3w_standard_properties (id)
)
    collate = utf8mb4_unicode_ci;

create table if not exists q3w_transform_operation_stages
(
    id         bigint unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    name       varchar(255) not null comment 'Наименование',
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists questionnaire_piles
(
    id               int unsigned auto_increment
        primary key,
    questionnaire_id int unsigned not null,
    cut              varchar(255) null,
    length           varchar(255) null,
    depth            varchar(255) null,
    head_height      varchar(255) null,
    count            varchar(255) null,
    created_at       timestamp    null,
    updated_at       timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists questionnaire_tongues
(
    id               int unsigned auto_increment
        primary key,
    questionnaire_id int unsigned not null,
    type             varchar(255) null,
    count            varchar(255) null,
    length           varchar(255) null,
    dive_type        varchar(255) null,
    term             varchar(255) null,
    created_at       timestamp    null,
    updated_at       timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists questionnaires
(
    id                   int unsigned auto_increment
        primary key,
    project_id           int unsigned         not null,
    contact_name         varchar(255)         null,
    contact_number       varchar(255)         null,
    contact_email        varchar(255)         null,
    pit_perimeter        varchar(255)         null,
    pit_depth            varchar(255)         null,
    pit_square           varchar(255)         null,
    is_tongue            tinyint(1) default 0 not null,
    is_pile              tinyint(1) default 0 not null,
    is_soil_leader       tinyint(1) default 0 not null,
    binding_type         varchar(255)         null,
    binding_count        varchar(255)         null,
    binding_length       varchar(255)         null,
    strut_type           varchar(255)         null,
    strut_count          varchar(255)         null,
    strut_diameter       varchar(255)         null,
    racks_type           varchar(255)         null,
    racks_count          varchar(255)         null,
    racks_diameter       varchar(255)         null,
    thrust_type          varchar(255)         null,
    thrust_count         varchar(255)         null,
    thrust_diameter      varchar(255)         null,
    tables_count         varchar(255)         null,
    gk_list_count        varchar(255)         null,
    embedded_parts_count varchar(255)         null,
    soil_count           varchar(255)         null,
    leader_count         varchar(255)         null,
    leader_trench        varchar(255)         null,
    comment              text                 null,
    token                varchar(255)         not null,
    created_at           timestamp            null,
    updated_at           timestamp            null
)
    collate = utf8mb4_unicode_ci;

create table if not exists report_groups
(
    id         bigint unsigned auto_increment
        primary key,
    name       text         not null,
    user_id    int unsigned not null,
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null
)
    collate = utf8mb4_unicode_ci;

create index if not exists report_groups_user_id_index
    on report_groups (user_id);

create table if not exists reviews
(
    id                  int unsigned auto_increment
        primary key,
    review              text                   not null,
    result_comment      text                   null,
    result_status       int unsigned default 0 not null,
    commercial_offer_id int unsigned           null,
    reviewable_type     varchar(255)           not null,
    reviewable_id       bigint unsigned        not null,
    created_at          timestamp              null,
    updated_at          timestamp              null
)
    collate = utf8mb4_unicode_ci;

create index if not exists reviews_reviewable_type_reviewable_id_index
    on reviews (reviewable_type, reviewable_id);

create table if not exists support_mail_files
(
    id              int unsigned auto_increment
        primary key,
    support_mail_id int unsigned not null,
    path            varchar(255) not null,
    original_name   varchar(255) not null,
    created_at      timestamp    null,
    updated_at      timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists support_mails
(
    id                 int unsigned auto_increment
        primary key,
    title              varchar(255)               not null,
    description        text                       null,
    user_id            int unsigned               not null,
    page_path          varchar(255)               not null,
    created_at         timestamp                  null,
    updated_at         timestamp                  null,
    status             varchar(255) default 'new' not null,
    solved_at          varchar(255)               null,
    estimate           int unsigned               null,
    gitlab_link        text                       null,
    result_description text                       null
)
    collate = utf8mb4_unicode_ci;

create table if not exists tariff_rates
(
    id         bigint unsigned auto_increment
        primary key,
    name       varchar(255) not null,
    type       int unsigned not null,
    user_id    int unsigned not null,
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null
)
    collate = utf8mb4_unicode_ci;

create index if not exists tariff_rates_user_id_index
    on tariff_rates (user_id);

create table if not exists task_changing_fields
(
    id         bigint unsigned auto_increment
        primary key,
    task_id    bigint       not null,
    field_name varchar(255) not null,
    value      varchar(255) not null,
    created_at timestamp    null,
    updated_at timestamp    null,
    old_value  varchar(255) null
)
    collate = utf8mb4_unicode_ci;

create table if not exists task_files
(
    id            int unsigned auto_increment
        primary key,
    task_id       int unsigned           not null,
    user_id       int unsigned           not null,
    file_name     varchar(255)           not null,
    original_name varchar(255)           not null,
    is_final      int unsigned default 0 not null,
    created_at    timestamp              null,
    updated_at    timestamp              null
)
    collate = utf8mb4_unicode_ci;

create table if not exists task_redirects
(
    id                  int unsigned auto_increment
        primary key,
    task_id             int unsigned not null,
    old_user_id         int unsigned not null,
    responsible_user_id int unsigned not null,
    redirect_note       varchar(255) null,
    created_at          timestamp    null,
    updated_at          timestamp    null,
    vacation_id         int unsigned null
)
    collate = utf8mb4_unicode_ci;

create table if not exists tasks
(
    id                  int unsigned auto_increment
        primary key,
    name                varchar(255)           not null,
    description         text                   null,
    project_id          int unsigned           null,
    contractor_id       int unsigned           null,
    user_id             int unsigned           null,
    responsible_user_id int unsigned           null,
    contact_id          int unsigned           null,
    incoming_phone      varchar(255)           null,
    internal_phone      varchar(255)           null,
    final_note          text                   null,
    is_seen             tinyint(1)   default 0 not null,
    status_result       int unsigned           null,
    status              int unsigned default 1 not null,
    is_solved           int unsigned default 0 not null,
    expired_at          datetime               not null,
    created_at          timestamp              null,
    updated_at          timestamp              null,
    notify_send         int          default 0 not null,
    target_id           int unsigned           null,
    revive_at           timestamp              null,
    result              varchar(255)           null,
    prev_task_id        int unsigned           null,
    deleted_at          timestamp              null,
    taskable_type       varchar(255)           null,
    taskable_id         bigint unsigned        null
)
    collate = utf8mb4_unicode_ci;

create index if not exists tasks_taskable_type_taskable_id_index
    on tasks (taskable_type, taskable_id);

create table if not exists technic_brands
(
    id         bigint unsigned auto_increment
        primary key,
    name       varchar(255) null comment 'Наименование',
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null
)
    comment 'Бренды / марки техники' collate = utf8mb4_unicode_ci;

create table if not exists technic_brand_models
(
    id               bigint unsigned auto_increment
        primary key,
    technic_brand_id bigint unsigned not null comment 'ID бренда техники',
    name             varchar(255)    not null comment 'Наименование',
    description      varchar(255)    null comment 'Описание',
    created_at       timestamp       null,
    updated_at       timestamp       null,
    deleted_at       timestamp       null,
    constraint technic_brand_models_technic_brand_id_foreign
        foreign key (technic_brand_id) references technic_brands (id)
)
    comment 'Модели техники' collate = utf8mb4_unicode_ci;

create table if not exists technic_categories
(
    id          bigint unsigned auto_increment
        primary key,
    name        varchar(255) not null,
    description varchar(255) null,
    created_at  timestamp    null,
    updated_at  timestamp    null,
    deleted_at  timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists technic_category_category_characteristic
(
    id                         bigint unsigned auto_increment
        primary key,
    technic_category_id        int       not null,
    category_characteristic_id int       not null,
    created_at                 timestamp null,
    updated_at                 timestamp null
)
    collate = utf8mb4_unicode_ci;

create table if not exists technic_movement_statuses
(
    id         bigint unsigned auto_increment
        primary key,
    name       varchar(255) not null comment 'Наименование',
    slug       varchar(255) not null,
    sortOrder  int          not null comment 'Порядок сортировки',
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null
)
    comment 'Статусы перемещений стоительной техники»' collate = utf8mb4_unicode_ci;

create table if not exists tg_notification_urls
(
    id              bigint unsigned auto_increment
        primary key,
    target_url      varchar(255) not null,
    encoded_url     varchar(255) null,
    notification_id varchar(255) not null,
    created_at      timestamp    null,
    updated_at      timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists threads
(
    id               int unsigned auto_increment
        primary key,
    subject          varchar(255) null,
    creator_id       int unsigned null,
    max_participants int          null comment 'Max number of participants allowed',
    start_date       timestamp    null,
    end_date         timestamp    null,
    avatar           varchar(255) null comment 'Profile picture for the conversation',
    created_at       timestamp    null,
    updated_at       timestamp    null,
    deleted_at       timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists timecard_additions
(
    id          bigint unsigned auto_increment
        primary key,
    timecard_id bigint unsigned      not null,
    user_id     bigint unsigned      not null,
    project_id  bigint unsigned      null,
    type        int unsigned         not null,
    name        varchar(500)         not null,
    amount      double(10, 2)        not null,
    prolonged   tinyint(1) default 0 not null,
    created_at  timestamp            null,
    updated_at  timestamp            null,
    deleted_at  timestamp            null
)
    collate = utf8mb4_unicode_ci;

create index if not exists timecard_additions_timecard_id_type_name_index
    on timecard_additions (timecard_id, type, name);

create table if not exists timecard_days
(
    id          bigint unsigned auto_increment
        primary key,
    timecard_id bigint unsigned      not null,
    user_id     bigint unsigned      not null,
    day         int                  not null,
    is_opened   tinyint(1) default 0 not null,
    completed   tinyint(1) default 0 not null,
    created_at  timestamp            null,
    updated_at  timestamp            null,
    deleted_at  timestamp            null
)
    collate = utf8mb4_unicode_ci;

create index if not exists timecard_days_timecard_id_day_index
    on timecard_days (timecard_id, day);

create table if not exists timecard_records
(
    id              bigint unsigned auto_increment
        primary key,
    timecard_day_id bigint unsigned not null,
    user_id         bigint unsigned not null,
    type            int             not null,
    tariff_id       int unsigned    null,
    project_id      int unsigned    null,
    length          double(8, 3)    null,
    amount          int             null,
    start           varchar(255)    null,
    end             varchar(255)    null,
    commentary      varchar(255)    null,
    created_at      timestamp       null,
    updated_at      timestamp       null,
    deleted_at      timestamp       null
)
    collate = utf8mb4_unicode_ci;

create index if not exists timecard_records_timecard_day_id_type_index
    on timecard_records (timecard_day_id, type);

create table if not exists timecards
(
    id         bigint unsigned auto_increment
        primary key,
    user_id    bigint unsigned        not null,
    author_id  bigint unsigned        not null,
    month      int unsigned           not null,
    ktu        int unsigned default 0 not null,
    is_opened  tinyint(1)   default 1 not null,
    created_at timestamp              null,
    updated_at timestamp              null,
    deleted_at timestamp              null,
    year       int                    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists timesheet_day_categories
(
    id         bigint unsigned auto_increment comment 'Уникальный идентификатор категории дня в табеле учета рабочего времени'
        primary key,
    name       varchar(255) not null comment 'Наименование категории дня',
    short_name varchar(255) not null comment 'Краткое наименование категории дня',
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null,
    constraint timesheet_day_categories_name_unique
        unique (name),
    constraint timesheet_day_categories_shortname_unique
        unique (short_name)
)
    comment 'Таблица категорий дней в табеле учета рабочего времени' collate = utf8mb4_unicode_ci;

create table if not exists timesheet_post_tariffs
(
    id                bigint unsigned auto_increment comment 'Уникальный идентификатор тарифа по часам'
        primary key,
    post_id           int unsigned not null comment 'Идентификатор должности',
    tariff_start_date date         not null comment 'Дата начала действия тарифа',
    tariff_end_date   date         null comment 'Дата окончания действия тарифа',
    created_at        timestamp    null,
    updated_at        timestamp    null,
    deleted_at        timestamp    null,
    constraint timesheet_post_tariffs_post_id_foreign
        foreign key (post_id) references `groups` (id)
)
    collate = utf8mb4_unicode_ci;

create index if not exists timesheet_post_tariffs_tariff_end_date_index
    on timesheet_post_tariffs (tariff_end_date);

create index if not exists timesheet_post_tariffs_tariff_start_date_index
    on timesheet_post_tariffs (tariff_start_date);

create table if not exists timesheet_states
(
    id         bigint unsigned auto_increment comment 'Уникальный идентификатор записи'
        primary key,
    name       varchar(255) not null comment 'Наименование состояния',
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null
)
    comment 'Справочник состояний табеля' collate = utf8mb4_unicode_ci;

create table if not exists timesheet_tariffs_types
(
    id         bigint unsigned auto_increment comment 'Уникальный идентификатор типа тарифа в табеле учета рабочего времени'
        primary key,
    name       varchar(255)           not null comment 'Наименование типа тарифа',
    sort_order int unsigned default 0 not null comment 'Порядок сортировки',
    created_at timestamp              null,
    updated_at timestamp              null,
    deleted_at timestamp              null
)
    comment 'Таблица типов тарифов в табеле учета рабочего времени' collate = utf8mb4_unicode_ci;

create table if not exists timesheet_tariffs
(
    id                        bigint unsigned auto_increment comment 'Уникальный идентификатор тарифа по часам'
        primary key,
    name                      varchar(255)         not null comment 'Наименование тарифа',
    timesheet_tariffs_type_id bigint unsigned      not null comment 'Тип тарифа',
    is_overwork               tinyint(1) default 0 not null comment 'Является ли тариф переработкой',
    tariff_color              varchar(255)         null comment 'Цвет тарифа',
    sort_order                int unsigned         not null comment 'Порядок сортировки тарифа',
    created_at                timestamp            null,
    updated_at                timestamp            null,
    deleted_at                timestamp            null,
    constraint timesheet_tariffs_timesheet_tariffs_type_id_foreign
        foreign key (timesheet_tariffs_type_id) references timesheet_tariffs_types (id)
)
    collate = utf8mb4_unicode_ci;

create table if not exists timesheet_tariff_rates
(
    id                       bigint unsigned auto_increment comment 'Уникальный идентификатор тарифа по часам'
        primary key,
    timesheet_tariff_id      bigint unsigned not null comment 'Тариф',
    timesheet_post_tariff_id bigint unsigned not null comment 'Тариф должности',
    rate                     double(8, 2)    not null comment 'Сумма тарифа',
    created_at               timestamp       null,
    updated_at               timestamp       null,
    deleted_at               timestamp       null,
    constraint timesheet_tariff_rates_timesheet_post_tariff_id_foreign
        foreign key (timesheet_post_tariff_id) references timesheet_post_tariffs (id),
    constraint timesheet_tariff_rates_timesheet_tariff_id_foreign
        foreign key (timesheet_tariff_id) references timesheet_tariffs (id)
)
    collate = utf8mb4_unicode_ci;

create table if not exists user_disabled_notifications
(
    id              bigint unsigned auto_increment
        primary key,
    user_id         int unsigned         not null,
    notification_id int unsigned         not null,
    in_telegram     tinyint(1) default 1 not null,
    in_system       tinyint(1) default 1 not null,
    created_at      timestamp            null,
    updated_at      timestamp            null,
    deleted_at      timestamp            null
)
    collate = utf8mb4_unicode_ci;

create table if not exists users
(
    id              int unsigned auto_increment
        primary key,
    first_name      varchar(255)           null,
    last_name       varchar(255)           null,
    patronymic      varchar(255)           null,
    user_full_name  varchar(255) as (concat(`last_name`, ' ', left(`first_name`, 1), '.',
                                            if(left(`patronymic`, 1) <> '', concat(' ', left(`patronymic`, 1), '.'),
                                               ''))),
    birthday        varchar(10)            null,
    email           varchar(255)           null,
    person_phone    varchar(255)           null,
    work_phone      varchar(255)           null,
    department_id   int unsigned           null,
    group_id        int unsigned           null,
    company         int unsigned default 1 not null,
    job_category_id int unsigned           null,
    brigade_id      bigint unsigned        null,
    image           varchar(255)           null,
    password        varchar(255)           not null,
    status          tinyint(1)   default 1 not null,
    is_su           tinyint(1)   default 0 not null,
    remember_token  varchar(100)           null,
    created_at      timestamp              null,
    updated_at      timestamp              null,
    chat_id         varchar(255)           null,
    in_vacation     tinyint(1)   default 0 not null,
    is_deleted      tinyint(1)   default 0 not null,
    INN             varchar(255)           null comment 'ИНН пользователя',
    gender          char(255)              null comment 'Пол пользователя (M - мужской, F - женский)',
    constraint users_email_unique
        unique (email),
    constraint users_inn_unique
        unique (INN)
)
    collate = utf8mb4_unicode_ci;

create table if not exists employees
(
    id                         bigint unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    user_id                    int unsigned                          not null comment 'Пользователь',
    employee_1c_name           varchar(255)                          not null comment 'Имя сотрудника в 1С',
    personnel_number           varchar(255)                          not null comment 'Табельный номер сотрудника',
    employee_1c_uid            varchar(255)                          not null comment 'Уникальный идентификатор сотрудника в 1С',
    employee_1c_post_id        int unsigned                          not null comment 'Уникальный идентификатор должности сотрудника в 1С',
    employee_1c_subdivision_id int unsigned                          not null comment 'Уникальный идентификатор подразделения сотрудника в 1С',
    company_id                 int unsigned                          not null comment 'Уникальный идентификатор организации, в которой работает сотрудник, в 1С',
    employment_date            date                                  not null comment 'Дата приема на работу',
    dismissal_date             date                                  null comment 'Дата увольнения',
    report_group_id            int unsigned                          null comment 'Отчетная группа',
    created_at                 timestamp default current_timestamp() not null on update current_timestamp(),
    updated_at                 timestamp                             null,
    deleted_at                 timestamp                             null,
    constraint employees_company_id_foreign
        foreign key (company_id) references companies (id),
    constraint employees_employee_1c_subdivision_id_foreign
        foreign key (employee_1c_subdivision_id) references employees_1c_subdivisions (id),
    constraint employees_report_group_id_foreign
        foreign key (report_group_id) references employees_report_groups (id),
    constraint employees_user_id_foreign
        foreign key (user_id) references users (id)
)
    comment 'Список сотрудников организаций, синхронизированных с 1С.' collate = utf8mb4_unicode_ci;

alter table companies
    add constraint companies_ceo_employee_id_foreign
        foreign key (ceo_employee_id) references employees (id);

alter table companies
    add constraint companies_chief_engineer_employee_id_foreign
        foreign key (chief_engineer_employee_id) references employees (id);

create table if not exists employee_name_inflections
(
    id            int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    employee_id   bigint unsigned not null comment 'Id сотрудника',
    nominative    varchar(255)    null comment 'Именительный падеж',
    genitive      varchar(255)    null comment 'Родительный падеж',
    dative        varchar(255)    null comment 'Дательный падеж',
    accusative    varchar(255)    null comment 'Винительный падеж',
    ablative      varchar(255)    null comment 'Творительный падеж',
    prepositional varchar(255)    null comment 'Предложный падеж',
    created_at    timestamp       null,
    updated_at    timestamp       null,
    deleted_at    timestamp       null,
    constraint employee_name_inflections_employee_id_foreign
        foreign key (employee_id) references employees (id)
)
    comment 'Склонения имен сотрудников' collate = utf8mb4_unicode_ci;

create table if not exists employees_1c_salaries
(
    id                             bigint unsigned auto_increment comment 'Уникальный идентификатор записи о зарплате сотрудника из 1С'
        primary key,
    employee_id                    bigint unsigned not null comment 'Идентификатор сотрудника, для которого указана зарплата',
    employees_1c_salaries_group_id bigint unsigned not null comment 'Идентификатор группы зарплаты из 1С',
    month                          int unsigned    not null comment 'Месяц, за который указана зарплата',
    year                           int unsigned    not null comment 'Год, за который указана зарплата',
    name                           varchar(255)    not null comment 'Наименование зарплаты',
    value                          double(8, 2)    not null comment 'Значение зарплаты',
    created_at                     timestamp       null,
    updated_at                     timestamp       null,
    deleted_at                     timestamp       null,
    constraint employees_1c_salaries_employee_id_foreign
        foreign key (employee_id) references employees (id),
    constraint employees_1c_salaries_employees_1c_salaries_group_id_foreign
        foreign key (employees_1c_salaries_group_id) references employees_1c_salaries_groups (id)
)
    comment 'Таблица зарплат сотрудников из 1С' collate = utf8mb4_unicode_ci;

create index if not exists employees_1c_salaries_month_index
    on employees_1c_salaries (month);

create index if not exists employees_1c_salaries_year_index
    on employees_1c_salaries (year);

create table if not exists favorite_menu_item_user
(
    menu_item_id bigint unsigned not null,
    user_id      int unsigned    not null,
    primary key (menu_item_id, user_id),
    constraint favorite_menu_item_user_menu_item_id_foreign
        foreign key (menu_item_id) references menu_items (id)
            on update cascade on delete cascade,
    constraint favorite_menu_item_user_user_id_foreign
        foreign key (user_id) references users (id)
            on update cascade on delete cascade
)
    collate = utf8mb4_unicode_ci;

create table if not exists fuel_tanks
(
    id                    bigint unsigned auto_increment
        primary key,
    tank_number           varchar(255)                         not null,
    awaiting_confirmation tinyint(1)                           null comment 'Ожидает подтверждение перемещения и передачи ответственности',
    object_id             int unsigned                         null comment 'ID объекта',
    responsible_id        int unsigned                         null comment 'ID ответственного',
    company_id            int unsigned                         null comment 'ID организации-собственника',
    fuel_level            int      default 0                   not null comment 'Текущий уровень топлива',
    explotation_start     datetime default current_timestamp() null,
    comment_movement_tmp  varchar(255)                         null comment 'Временный комментарий при передаче емкости',
    chat_message_tmp      longtext collate utf8mb4_bin         null comment 'ID чата и сообщения о необходимости подтвердить перемещение емкости',
    created_at            timestamp                            null,
    updated_at            timestamp                            null,
    deleted_at            timestamp                            null,
    constraint fuel_tanks_company_id_foreign
        foreign key (company_id) references companies (id),
    constraint fuel_tanks_object_id_foreign
        foreign key (object_id) references project_objects (id),
    constraint fuel_tanks_responsible_id_foreign
        foreign key (responsible_id) references users (id)
)
    collate = utf8mb4_unicode_ci;

create table if not exists fuel_tank_flow_remains
(
    id           bigint unsigned auto_increment
        primary key,
    fuel_tank_id bigint unsigned not null comment 'ID топливной емкости',
    volume       int             null comment 'Количество топлива',
    created_at   timestamp       null,
    updated_at   timestamp       null,
    constraint fuel_tank_flow_remains_fuel_tank_id_foreign
        foreign key (fuel_tank_id) references fuel_tanks (id)
)
    comment 'Регистр накопления - остатки топлива в емкостях' collate = utf8mb4_unicode_ci;

create table if not exists fuel_tank_movements
(
    id                 bigint unsigned auto_increment
        primary key,
    author_id          int unsigned    not null comment 'ID автора',
    fuel_tank_id       bigint unsigned not null comment 'ID топливной емкости',
    object_id          int unsigned    null comment 'ID объекта',
    previous_object_id int unsigned    null comment 'ID предыдущего объекта',
    fuel_level         int             null comment 'Текущий уровень топлива',
    created_at         timestamp       null,
    updated_at         timestamp       null,
    deleted_at         timestamp       null,
    constraint fuel_tank_movements_author_id_foreign
        foreign key (author_id) references users (id),
    constraint fuel_tank_movements_fuel_tank_id_foreign
        foreign key (fuel_tank_id) references fuel_tanks (id),
    constraint fuel_tank_movements_object_id_foreign
        foreign key (object_id) references project_objects (id),
    constraint fuel_tank_movements_previous_object_id_foreign
        foreign key (previous_object_id) references project_objects (id)
)
    comment 'Движение топлива в емкостях' collate = utf8mb4_unicode_ci;

create index if not exists fuel_tanks_tank_number_index
    on fuel_tanks (tank_number);

create table if not exists labor_safety_requests
(
    id                              int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    order_number                    varchar(255) default 'б/н' not null comment 'Номер приказа',
    order_date                      date                       not null comment 'Дата приказа',
    company_id                      int unsigned               not null comment 'ID компании',
    project_object_id               int unsigned               not null comment 'ID объекта',
    author_user_id                  int unsigned               not null comment 'ID автора',
    implementer_user_id             int unsigned               null comment 'ID исполнителя',
    responsible_employee_id         bigint unsigned            not null comment 'ID ответственного сотрудника',
    sub_responsible_employee_id     bigint unsigned            null comment 'ID замещающего ответственного сотрудника',
    request_status_id               int unsigned               not null comment 'ID статуса заявки',
    generated_html                  mediumtext                 null comment 'Сформированные приказы',
    comment                         text                       not null comment 'Комментарий',
    created_at                      timestamp                  null,
    updated_at                      timestamp                  null,
    deleted_at                      timestamp                  null,
    project_manager_employee_id     bigint unsigned            null comment 'Руководитель проекта',
    sub_project_manager_employee_id bigint unsigned            null comment 'Заместитель руководителя проекта',
    constraint l_s_r_implementer_user_id_foreign
        foreign key (implementer_user_id) references users (id),
    constraint l_s_r_resp_employee_id_foreign
        foreign key (responsible_employee_id) references employees (id),
    constraint l_s_r_sub_resp_employee_id_foreign
        foreign key (sub_responsible_employee_id) references employees (id),
    constraint labor_safety_requests_author_user_id_foreign
        foreign key (author_user_id) references users (id),
    constraint labor_safety_requests_company_id_foreign
        foreign key (company_id) references companies (id),
    constraint labor_safety_requests_project_manager_employee_id_foreign
        foreign key (project_manager_employee_id) references employees (id),
    constraint labor_safety_requests_project_object_id_foreign
        foreign key (project_object_id) references project_objects (id),
    constraint labor_safety_requests_sub_project_manager_employee_id_foreign
        foreign key (sub_project_manager_employee_id) references employees (id)
)
    comment 'Заявки на формирование приказов в модуле «Охрана труда»' collate = utf8mb4_unicode_ci;

create table if not exists labor_safety_request_workers
(
    id                 int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    request_id         int unsigned    not null comment 'ID Заявки',
    worker_employee_id bigint unsigned not null comment 'ID сотрудника',
    worker_type_id     int unsigned    not null comment 'ID типа сотрудника',
    created_at         timestamp       null,
    updated_at         timestamp       null,
    deleted_at         timestamp       null,
    constraint l_s_r_worker_employee_id_foreign
        foreign key (worker_employee_id) references employees (id),
    constraint labor_safety_request_workers_request_id_foreign
        foreign key (request_id) references labor_safety_requests (id),
    constraint labor_safety_request_workers_worker_type_id_foreign
        foreign key (worker_type_id) references labor_safety_worker_types (id)
)
    comment 'Список сотрудников (рабочих), для которых необходимо сформировать приказы в модуле «Охрана труда»'
    collate = utf8mb4_unicode_ci;

create table if not exists labor_safety_order_workers
(
    id                 int unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    request_id         int unsigned not null comment 'ID заявки',
    order_type_id      int unsigned not null comment 'ID типа приказа',
    requests_worker_id int unsigned not null comment 'ID записи со ссылкой на сотрудника, сформированному при подаче заявки',
    created_at         timestamp    null,
    updated_at         timestamp    null,
    deleted_at         timestamp    null,
    constraint labor_safety_order_workers_order_type_id_foreign
        foreign key (order_type_id) references labor_safety_order_types (id),
    constraint labor_safety_order_workers_request_id_foreign
        foreign key (request_id) references labor_safety_requests (id),
    constraint labor_safety_order_workers_requests_worker_id_foreign
        foreign key (requests_worker_id) references labor_safety_request_workers (id)
)
    comment 'Список сотрудников (рабочих), которые участвуют при формировании приказов в модуле «Охрана труда»'
    collate = utf8mb4_unicode_ci;

create index if not exists labor_safety_requests_order_date_index
    on labor_safety_requests (order_date);

create table if not exists our_technics
(
    id                     bigint unsigned auto_increment
        primary key,
    name                   varchar(255)         null comment 'Наименование',
    responsible_id         bigint unsigned      null comment 'ID ответственного employee',
    company_id             int unsigned         null comment 'ID организации-собственника',
    third_party_mark       tinyint(1) default 0 not null comment 'Техника стороннего контрагента',
    contractor_id          int unsigned         null comment 'ID контрагента',
    technic_brand_id       bigint unsigned      null comment 'ID бренда техники',
    technic_brand_model_id bigint unsigned      null comment 'ID модели техники',
    manufacture_year       int                  null comment 'Год выпуска',
    serial_number          varchar(255)         null comment 'Заводской номер',
    registration_number    varchar(255)         null comment 'Государственный номер регистрации',
    brand                  varchar(255)         not null,
    model                  varchar(255)         not null,
    owner                  varchar(255)         not null,
    start_location_id      int                  null,
    inventory_number       varchar(255)         not null,
    exploitation_start     date                 null,
    technic_category_id    int                  not null,
    created_at             timestamp            null,
    updated_at             timestamp            null,
    deleted_at             timestamp            null,
    constraint our_technics_company_id_foreign
        foreign key (company_id) references companies (id),
    constraint our_technics_contractor_id_foreign
        foreign key (contractor_id) references contractors (id),
    constraint our_technics_responsible_id_foreign
        foreign key (responsible_id) references employees (id),
    constraint our_technics_technic_brand_id_foreign
        foreign key (technic_brand_id) references technic_brands (id),
    constraint our_technics_technic_brand_model_id_foreign
        foreign key (technic_brand_model_id) references technic_brand_models (id)
)
    collate = utf8mb4_unicode_ci;

create table if not exists fuel_tank_flows
(
    id                     bigint unsigned auto_increment
        primary key,
    event_date             date            not null comment 'Дата время факта события',
    author_id              int unsigned    not null comment 'ID автора',
    responsible_id         int unsigned    null comment 'ID ответственного',
    fuel_tank_id           bigint unsigned null comment 'ID топливной емкости',
    object_id              int unsigned    null comment 'ID объекта',
    company_id             int unsigned    null comment 'ID организации',
    contractor_id          int unsigned    null comment 'ID контрагента',
    our_technic_id         bigint unsigned null comment 'ID единицы техники',
    third_party_consumer   varchar(255)    null comment 'Сторонний потребитель топлива',
    fuel_tank_flow_type_id bigint unsigned not null comment 'ID типа топливной операции',
    volume                 int             null comment 'Количество топлива',
    document               varchar(255)    null comment 'Реквизиты документа',
    comment                varchar(255)    null comment 'Комментарий',
    created_at             timestamp       null,
    updated_at             timestamp       null,
    deleted_at             timestamp       null,
    constraint fuel_tank_flows_author_id_foreign
        foreign key (author_id) references users (id),
    constraint fuel_tank_flows_company_id_foreign
        foreign key (company_id) references companies (id),
    constraint fuel_tank_flows_contractor_id_foreign
        foreign key (contractor_id) references contractors (id),
    constraint fuel_tank_flows_fuel_tank_flow_type_id_foreign
        foreign key (fuel_tank_flow_type_id) references fuel_tank_flow_types (id),
    constraint fuel_tank_flows_fuel_tank_id_foreign
        foreign key (fuel_tank_id) references fuel_tanks (id),
    constraint fuel_tank_flows_object_id_foreign
        foreign key (object_id) references project_objects (id),
    constraint fuel_tank_flows_our_technic_id_foreign
        foreign key (our_technic_id) references our_technics (id),
    constraint fuel_tank_flows_responsible_id_foreign
        foreign key (responsible_id) references users (id)
)
    comment 'Движение топлива в емкостях' collate = utf8mb4_unicode_ci;

create table if not exists fuel_tank_transfer_histories
(
    id                       bigint unsigned auto_increment
        primary key,
    author_id                int unsigned    not null comment 'ID автора',
    fuel_tank_id             bigint unsigned not null comment 'ID топливной емкости',
    tank_moving_confirmation tinyint(1)      null comment 'Подтвержждение перемещения и передачи ответственности',
    object_id                int unsigned    null comment 'ID объекта',
    previous_object_id       int unsigned    null comment 'ID предыдущего объекта',
    responsible_id           int unsigned    null comment 'ID ответственного',
    previous_responsible_id  int unsigned    null comment 'ID предыдущего ответственного',
    fuel_tank_flow_id        bigint unsigned null comment 'ID топливной транзакции',
    fuel_level               int             null comment 'Остаток топлива в емкости',
    parent_fuel_level_id     bigint unsigned null comment 'Id записи о предыдущем остатке топлива',
    event_date               date            not null comment 'Дата время факта события',
    created_at               timestamp       null,
    updated_at               timestamp       null,
    deleted_at               timestamp       null,
    constraint fuel_tank_transfer_histories_parent_fuel_level_id_foreign
        foreign key (parent_fuel_level_id) references fuel_tank_transfer_histories (id),
    constraint fuel_tank_transfer_hystories_author_id_foreign
        foreign key (author_id) references users (id),
    constraint fuel_tank_transfer_hystories_fuel_tank_flow_id_foreign
        foreign key (fuel_tank_flow_id) references fuel_tank_flows (id),
    constraint fuel_tank_transfer_hystories_fuel_tank_id_foreign
        foreign key (fuel_tank_id) references fuel_tanks (id),
    constraint fuel_tank_transfer_hystories_object_id_foreign
        foreign key (object_id) references project_objects (id),
    constraint fuel_tank_transfer_hystories_previous_object_id_foreign
        foreign key (previous_object_id) references project_objects (id),
    constraint fuel_tank_transfer_hystories_previous_responsible_id_foreign
        foreign key (previous_responsible_id) references users (id),
    constraint fuel_tank_transfer_hystories_responsible_id_foreign
        foreign key (responsible_id) references users (id)
)
    comment 'История перемещения и передачи ответственности по топливным емкостям' collate = utf8mb4_unicode_ci;

create table if not exists project_object_documents
(
    id                 bigint unsigned auto_increment comment 'Уникальный идентфикатор'
        primary key,
    document_type_id   bigint unsigned              not null comment 'ID типа документа',
    document_status_id bigint unsigned              not null comment 'ID статуса документа',
    project_object_id  int unsigned                 not null comment 'ID объекта',
    options            longtext collate utf8mb4_bin null comment 'Параметры дополнительные',
    author_id          int unsigned                 not null comment 'ID автора',
    document_name      varchar(255)                 not null comment 'Наименование документа',
    document_date      date                         null comment 'Дата документа',
    created_at         timestamp                    null,
    updated_at         timestamp                    null,
    deleted_at         timestamp                    null,
    constraint project_object_documents_author_id_foreign
        foreign key (author_id) references users (id),
    constraint project_object_documents_document_status_id_foreign
        foreign key (document_status_id) references project_object_document_statuses (id),
    constraint project_object_documents_document_type_id_foreign
        foreign key (document_type_id) references project_object_document_types (id),
    constraint project_object_documents_project_object_id_foreign
        foreign key (project_object_id) references project_objects (id)
)
    comment 'Документы в модуле «Документооборот на объектах»' collate = utf8mb4_unicode_ci;

create table if not exists q3w_material_operations
(
    id                              bigint unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    operation_route_id              int unsigned                             not null comment 'Идентификатор типа операции',
    operation_route_stage_id        int unsigned                             not null comment 'Идентификатор этапа операции',
    source_project_object_id        int unsigned                             null comment 'Идентификатор объекта, с которого отправляется материал',
    destination_project_object_id   int unsigned                             null comment 'Идентификатор идентификатор объекта, куда должен прибыть материал',
    contractor_id                   int unsigned                             null comment 'Идентификатор контрагента (поставщика)',
    consignment_note_number         varchar(255) default '0'                 not null comment 'Номер ТТН',
    operation_date                  timestamp    default current_timestamp() not null comment 'Дата начала',
    creator_user_id                 int unsigned                             not null comment 'ID пользователя, создавшего операцию',
    source_responsible_user_id      int unsigned                             null comment 'ID ответственного пользователя со стороны объекта-отправителя',
    destination_responsible_user_id int unsigned                             null comment 'ID ответственного пользователя со стороны объекта-получателя',
    creator_comment                 text                                     not null comment 'Комментарий пользователя',
    created_at                      timestamp                                null,
    updated_at                      timestamp                                null,
    deleted_at                      timestamp                                null,
    transformation_type_id          int unsigned                             null comment 'Тип преобразования материала',
    constraint q3w_material_operations_contractor_id_foreign
        foreign key (contractor_id) references contractors (id),
    constraint q3w_material_operations_creator_user_id_foreign
        foreign key (creator_user_id) references users (id),
    constraint q3w_material_operations_destination_project_object_id_foreign
        foreign key (destination_project_object_id) references project_objects (id),
    constraint q3w_material_operations_destination_responsible_user_id_foreign
        foreign key (destination_responsible_user_id) references users (id),
    constraint q3w_material_operations_operation_route_id_foreign
        foreign key (operation_route_id) references q3w_operation_routes (id),
    constraint q3w_material_operations_operation_route_stage_id_foreign
        foreign key (operation_route_stage_id) references q3w_operation_route_stages (id),
    constraint q3w_material_operations_source_project_object_id_foreign
        foreign key (source_project_object_id) references project_objects (id),
    constraint q3w_material_operations_source_responsible_user_id_foreign
        foreign key (source_responsible_user_id) references users (id),
    constraint q3w_material_operations_transformation_type_id_foreign
        foreign key (transformation_type_id) references q3w_material_transformation_types (id)
)
    collate = utf8mb4_unicode_ci;

create index if not exists q3w_material_operations_contractor_id_index
    on q3w_material_operations (contractor_id);

create index if not exists q3w_material_operations_creator_user_id_index
    on q3w_material_operations (creator_user_id);

create index if not exists q3w_material_operations_destination_project_object_id_index
    on q3w_material_operations (destination_project_object_id);

create index if not exists q3w_material_operations_destination_responsible_user_id_index
    on q3w_material_operations (destination_responsible_user_id);

create index if not exists q3w_material_operations_operation_route_id_index
    on q3w_material_operations (operation_route_id);

create index if not exists q3w_material_operations_operation_route_stage_id_index
    on q3w_material_operations (operation_route_stage_id);

create index if not exists q3w_material_operations_source_project_object_id_index
    on q3w_material_operations (source_project_object_id);

create index if not exists q3w_material_operations_source_responsible_user_id_index
    on q3w_material_operations (source_responsible_user_id);

create table if not exists q3w_material_snapshots
(
    id                bigint unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    operation_id      bigint unsigned not null comment 'Идентификатор операции',
    project_object_id int unsigned    not null comment 'Идентификатор объекта',
    created_at        timestamp       null,
    updated_at        timestamp       null,
    deleted_at        timestamp       null,
    constraint q3w_material_snapshots_operation_id_foreign
        foreign key (operation_id) references q3w_material_operations (id),
    constraint q3w_material_snapshots_project_object_id_foreign
        foreign key (project_object_id) references project_objects (id)
)
    collate = utf8mb4_unicode_ci;

create table if not exists q3w_material_snapshot_materials
(
    id          bigint unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    snapshot_id bigint unsigned not null comment 'Идентификатор снапшота',
    standard_id int unsigned    not null comment 'Идентификатор эталона',
    amount      int unsigned    null comment 'Количество в штуках',
    quantity    double unsigned not null comment 'Количество в единицах измерения',
    created_at  timestamp       null,
    updated_at  timestamp       null,
    deleted_at  timestamp       null,
    comment_id  bigint unsigned null comment 'Комментарий',
    constraint q3w_material_snapshot_materials_comment_id_foreign
        foreign key (comment_id) references q3w_material_snapshot_material_comments (id),
    constraint q3w_material_snapshot_materials_snapshot_id_foreign
        foreign key (snapshot_id) references q3w_material_snapshots (id),
    constraint q3w_material_snapshot_materials_standard_id_foreign
        foreign key (standard_id) references q3w_material_standards (id)
)
    collate = utf8mb4_unicode_ci;

create index if not exists q3w_material_snapshot_materials_snapshot_id_index
    on q3w_material_snapshot_materials (snapshot_id);

create index if not exists q3w_material_snapshot_materials_standard_id_index
    on q3w_material_snapshot_materials (standard_id);

create index if not exists q3w_material_snapshots_operation_id_index
    on q3w_material_snapshots (operation_id);

create index if not exists q3w_material_snapshots_project_object_id_index
    on q3w_material_snapshots (project_object_id);

create table if not exists q3w_operation_comments
(
    id                       bigint unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    material_operation_id    bigint unsigned not null comment 'Идентификатор операции',
    operation_route_stage_id int unsigned    not null comment 'Идентификатор этапа (статуса) операции',
    user_id                  int unsigned    not null comment 'Идентификатор пользователя, оставившего операцию',
    comment                  text            not null comment 'Комментарий пользователя',
    created_at               timestamp       null,
    updated_at               timestamp       null,
    deleted_at               timestamp       null,
    constraint q3w_operation_comments_material_operation_id_foreign
        foreign key (material_operation_id) references q3w_material_operations (id),
    constraint q3w_operation_comments_operation_route_stage_id_foreign
        foreign key (operation_route_stage_id) references q3w_operation_route_stages (id),
    constraint q3w_operation_comments_user_id_foreign
        foreign key (user_id) references users (id)
)
    collate = utf8mb4_unicode_ci;

create index if not exists q3w_operation_comments_material_operation_id_index
    on q3w_operation_comments (material_operation_id);

create index if not exists q3w_operation_comments_operation_route_stage_id_index
    on q3w_operation_comments (operation_route_stage_id);

create index if not exists q3w_operation_comments_user_id_index
    on q3w_operation_comments (user_id);

create table if not exists q3w_operation_files
(
    id                       bigint unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    material_operation_id    bigint unsigned null comment 'Идентификатор операции',
    operation_route_stage_id int unsigned    null comment 'Идентификатор этапа (статуса) операции',
    upload_file_type         int unsigned    not null comment 'Идентификатор типа файла',
    file_name                varchar(255)    not null comment 'Имя файла',
    file_path                varchar(255)    not null comment 'Относительный путь к файлу',
    original_file_name       varchar(255)    not null comment 'Оригинальное имя файла',
    user_id                  int unsigned    not null comment 'Имя пользователя, загрузившего файл',
    created_at               timestamp       null,
    updated_at               timestamp       null,
    deleted_at               timestamp       null,
    constraint q3w_operation_files_material_operation_id_foreign
        foreign key (material_operation_id) references q3w_material_operations (id),
    constraint q3w_operation_files_operation_route_stage_id_foreign
        foreign key (operation_route_stage_id) references q3w_operation_route_stages (id),
    constraint q3w_operation_files_upload_file_type_foreign
        foreign key (upload_file_type) references q3w_operation_file_types (id),
    constraint q3w_operation_files_user_id_foreign
        foreign key (user_id) references users (id)
)
    collate = utf8mb4_unicode_ci;

create index if not exists q3w_operation_files_material_operation_id_index
    on q3w_operation_files (material_operation_id);

create index if not exists q3w_operation_files_operation_route_stage_id_index
    on q3w_operation_files (operation_route_stage_id);

create index if not exists q3w_operation_files_user_id_index
    on q3w_operation_files (user_id);

create table if not exists q3w_operation_materials
(
    id                           bigint unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    material_operation_id        bigint unsigned              not null comment 'Идентификатор операции',
    standard_id                  int unsigned                 not null comment 'Идентификатор эталона',
    amount                       int unsigned                 null comment 'Количество в штуках',
    initial_amount               int unsigned                 null comment 'Количество в штуках, которые указал инициатор',
    quantity                     double unsigned              not null comment 'Количество в единицах измерения',
    initial_quantity             double unsigned              not null comment 'Количество в единицах измерения, которые указал инициатор',
    edit_states                  longtext collate utf8mb4_bin null comment 'Массив состояний записи в процессе работы с операцией',
    created_at                   timestamp                    null,
    updated_at                   timestamp                    null,
    deleted_at                   timestamp                    null,
    transform_operation_stage_id bigint unsigned              null comment 'Этап преобразования материала',
    comment_id                   bigint unsigned              null comment 'Комментарий',
    initial_comment_id           bigint unsigned              null comment 'Начальный комментарий',
    constraint q3w_operation_materials_comment_id_foreign
        foreign key (comment_id) references q3w_operation_material_comments (id),
    constraint q3w_operation_materials_initial_comment_id_foreign
        foreign key (initial_comment_id) references q3w_material_comments (id),
    constraint q3w_operation_materials_material_operation_id_foreign
        foreign key (material_operation_id) references q3w_material_operations (id),
    constraint q3w_operation_materials_standard_id_foreign
        foreign key (standard_id) references q3w_material_standards (id),
    constraint q3w_operation_materials_transform_operation_stage_id_foreign
        foreign key (transform_operation_stage_id) references q3w_transform_operation_stages (id)
)
    collate = utf8mb4_unicode_ci;

create index if not exists q3w_operation_materials_material_operation_id_index
    on q3w_operation_materials (material_operation_id);

create index if not exists q3w_operation_materials_standard_id_index
    on q3w_operation_materials (standard_id);

create index if not exists q3w_operation_materials_transform_operation_stage_id_index
    on q3w_operation_materials (transform_operation_stage_id);

create table if not exists technic_movements
(
    id                         bigint unsigned auto_increment
        primary key,
    technic_movement_status_id bigint unsigned not null comment 'ID статус перемещения техники',
    technic_category_id        bigint unsigned null comment 'ID категории техники',
    technic_id                 bigint unsigned null comment 'ID единицы техники',
    order_start_date           date            null,
    order_end_date             date            null,
    order_comment              varchar(255)    null,
    movement_start_datetime    datetime        null,
    contractor_id              int unsigned    null comment 'ID контрагента перевозчика',
    responsible_id             int unsigned    null comment 'ID отвественного пользователя',
    previous_responsible_id    int unsigned    null comment 'ID предыдущего отвественного пользователя',
    object_id                  int unsigned    not null comment 'ID объекта прибытия',
    previous_object_id         int unsigned    null comment 'ID объекта убытия',
    author_id                  int unsigned    not null comment 'Идентификатор пользователя-автора записи',
    editor_id                  int unsigned    not null comment 'Идентификатор пользователя, который внес последние изменения в запись',
    created_at                 timestamp       null,
    updated_at                 timestamp       null,
    deleted_at                 timestamp       null,
    constraint technic_movements_author_id_foreign
        foreign key (author_id) references users (id),
    constraint technic_movements_contractor_id_foreign
        foreign key (contractor_id) references contractors (id),
    constraint technic_movements_editor_id_foreign
        foreign key (editor_id) references users (id),
    constraint technic_movements_object_id_foreign
        foreign key (object_id) references project_objects (id),
    constraint technic_movements_previous_object_id_foreign
        foreign key (previous_object_id) references project_objects (id),
    constraint technic_movements_previous_responsible_id_foreign
        foreign key (previous_responsible_id) references users (id),
    constraint technic_movements_responsible_id_foreign
        foreign key (responsible_id) references users (id),
    constraint technic_movements_technic_category_id_foreign
        foreign key (technic_category_id) references technic_categories (id),
    constraint technic_movements_technic_id_foreign
        foreign key (technic_id) references our_technics (id),
    constraint technic_movements_technic_movement_status_id_foreign
        foreign key (technic_movement_status_id) references technic_movement_statuses (id)
)
    comment 'Журнал перемещений техники»' collate = utf8mb4_unicode_ci;

create table if not exists timesheet_cards
(
    id                 bigint unsigned auto_increment comment 'Уникальный идентификатор табеля учета рабочего времени'
        primary key,
    employee_id        bigint unsigned                     not null comment 'Идентификатор сотрудника, для которого ведется табель',
    month              int unsigned                        not null comment 'Месяц табеля, с индексированием для ускорения поиска',
    year               int unsigned                        not null comment 'Год табеля, с индексированием для ускорения поиска',
    timesheet_state_id bigint unsigned        default 1    not null comment 'Идентификатор состояния табеля',
    ktu                decimal(8, 2) unsigned default 0.00 not null comment 'Коэффициент трудоемкости (KTU)',
    author_id          int unsigned                        not null comment 'Идентификатор пользователя-автора записи',
    editor_id          int unsigned                        not null comment 'Идентификатор пользователя, который внес последние изменения в запись',
    created_at         timestamp                           null,
    updated_at         timestamp                           null,
    deleted_at         timestamp                           null,
    constraint timesheet_cards_author_id_foreign
        foreign key (author_id) references users (id),
    constraint timesheet_cards_editor_id_foreign
        foreign key (editor_id) references users (id),
    constraint timesheet_cards_employee_id_foreign
        foreign key (employee_id) references employees (id),
    constraint timesheet_cards_timesheet_state_id_foreign
        foreign key (timesheet_state_id) references timesheet_states (id)
)
    comment 'Таблица табелей учета рабочего времени' collate = utf8mb4_unicode_ci;

create table if not exists timesheet
(
    id                bigint unsigned auto_increment comment 'Уникальный идентификатор записи в табеле'
        primary key,
    timesheet_card_id bigint unsigned not null comment 'Идентификатор табеля учета рабочего времени',
    date              date            not null comment 'Дата записи в табеле',
    deal_multiplier   double(8, 2)    null comment 'Множитель для сделок (если применяется)',
    count             int unsigned    not null comment 'Количество (количество часов или метров (для сделок))',
    author_id         int unsigned    not null comment 'Идентификатор пользователя-автора записи',
    editor_id         int unsigned    not null comment 'Идентификатор пользователя, который внес последние изменения в запись',
    created_at        timestamp       null,
    updated_at        timestamp       null,
    deleted_at        timestamp       null,
    constraint timesheet_author_id_foreign
        foreign key (author_id) references users (id),
    constraint timesheet_editor_id_foreign
        foreign key (editor_id) references users (id),
    constraint timesheet_timesheet_card_id_foreign
        foreign key (timesheet_card_id) references timesheet_cards (id)
)
    comment 'Таблица записей в табеле учета рабочего времени' collate = utf8mb4_unicode_ci;

create index if not exists timesheet_date_index
    on timesheet (date);

create table if not exists timesheet_aggregated_salary_summary
(
    id                        bigint unsigned auto_increment comment 'Уникальный идентификатор сводной информации по зарплате в табеле учета рабочего времени'
        primary key,
    timesheet_card_id         bigint unsigned not null comment 'Идентификатор табеля учета рабочего времени',
    employee_id               bigint unsigned not null comment 'Идентификатор сотрудника',
    project_object_id         int unsigned    null comment 'Идентификатор объекта проекта (если применимо)',
    post_id                   int unsigned    not null comment 'Идентификатор должности сотрудника',
    date                      date            not null comment 'Дата сводной информации',
    timesheet_tariffs_type_id bigint unsigned not null comment 'Идентификатор типа тарифа',
    timesheet_post_tariff_id  bigint unsigned not null comment 'Идентификатор тарифа',
    rate                      double(8, 2)    not null comment 'Ставка',
    count                     int             not null comment 'Количество (например, количество часов)',
    summary_salary            double(8, 2)    not null comment 'Суммарная зарплата',
    created_at                timestamp       null,
    updated_at                timestamp       null,
    deleted_at                timestamp       null,
    constraint summary_timesheet_post_tariff_id_foreign
        foreign key (timesheet_post_tariff_id) references timesheet_post_tariffs (id),
    constraint summary_timesheet_tariffs_type_id_foreign
        foreign key (timesheet_tariffs_type_id) references timesheet_tariffs_types (id),
    constraint timesheet_aggregated_salary_summary_employee_id_foreign
        foreign key (employee_id) references employees (id),
    constraint timesheet_aggregated_salary_summary_post_id_foreign
        foreign key (post_id) references `groups` (id),
    constraint timesheet_aggregated_salary_summary_project_object_id_foreign
        foreign key (project_object_id) references project_objects (id),
    constraint timesheet_aggregated_salary_summary_timesheet_card_id_foreign
        foreign key (timesheet_card_id) references timesheet_cards (id)
)
    comment 'Таблица сводной информации по зарплате в табеле учета рабочего времени' collate = utf8mb4_unicode_ci;

create index if not exists timesheet_cards_month_index
    on timesheet_cards (month);

create index if not exists timesheet_cards_year_index
    on timesheet_cards (year);

create table if not exists timesheet_employees_compensations
(
    id                           bigint unsigned auto_increment comment 'Уникальный идентификатор компенсации сотрудника'
        primary key,
    timesheet_card_id            bigint unsigned        not null comment 'Идентификатор табеля учета рабочего времени',
    compensation_type            int unsigned default 1 not null comment 'Тип компенсации: 1 - введенная вручную, 2 - сгенерирована автоматически',
    compensation_value           int unsigned           not null comment 'Значение компенсации',
    compensation_comment         varchar(256)           not null comment 'Комментарий к компенсации',
    prolongation                 tinyint      default 0 not null comment 'Флаг пролонгации',
    prolongation_compensation_id bigint unsigned        null comment 'ID пролонгированной записи',
    author_id                    int unsigned           not null comment 'Идентификатор пользователя-автора записи',
    editor_id                    int unsigned           not null comment 'Идентификатор пользователя, который внес последние изменения в запись',
    created_at                   timestamp              null,
    updated_at                   timestamp              null,
    deleted_at                   timestamp              null,
    constraint timesheet_employees_compensations_author_id_foreign
        foreign key (author_id) references users (id),
    constraint timesheet_employees_compensations_editor_id_foreign
        foreign key (editor_id) references users (id),
    constraint timesheet_employees_compensations_timesheet_card_id_foreign
        foreign key (timesheet_card_id) references timesheet_cards (id)
)
    comment 'Таблица компенсаций сотрудников' collate = utf8mb4_unicode_ci;

create index if not exists prolongation_compensation_id_index
    on timesheet_employees_compensations (prolongation_compensation_id);

create table if not exists timesheet_employees_objects
(
    id                bigint unsigned auto_increment comment 'Уникальный идентификатор связи между сотрудником и объектом проекта в табеле учета рабочего времени'
        primary key,
    project_object_id int unsigned    not null comment 'Идентификатор объекта проекта',
    employee_id       bigint unsigned not null comment 'Идентификатор сотрудника',
    date              date            not null comment 'Дата связи между сотрудником и объектом проекта',
    author_id         int unsigned    not null comment 'Идентификатор пользователя-автора записи',
    editor_id         int unsigned    not null comment 'Идентификатор пользователя, который внес последние изменения в запись',
    created_at        timestamp       null,
    updated_at        timestamp       null,
    deleted_at        timestamp       null,
    constraint timesheet_employees_objects_author_id_foreign
        foreign key (author_id) references users (id),
    constraint timesheet_employees_objects_editor_id_foreign
        foreign key (editor_id) references users (id),
    constraint timesheet_employees_objects_employee_id_foreign
        foreign key (employee_id) references employees (id),
    constraint timesheet_employees_objects_project_object_id_foreign
        foreign key (project_object_id) references project_objects (id)
)
    comment 'Таблица связей между сотрудниками и объектами проектов в табеле учета рабочего времени'
    collate = utf8mb4_unicode_ci;

create index if not exists timesheet_employees_objects_date_index
    on timesheet_employees_objects (date);

create table if not exists timesheet_employees_penalties
(
    id                bigint unsigned auto_increment comment 'Уникальный идентификатор штрафа сотрудника'
        primary key,
    timesheet_card_id bigint unsigned not null comment 'Идентификатор табеля учета рабочего времени',
    penalty_value     int unsigned    not null comment 'Значение штрафа',
    penalty_comment   varchar(255)    null comment 'Комментарий к штрафу',
    author_id         int unsigned    not null comment 'Идентификатор пользователя-автора записи',
    editor_id         int unsigned    not null comment 'Идентификатор пользователя, который внес последние изменения в запись',
    created_at        timestamp       null,
    updated_at        timestamp       null,
    deleted_at        timestamp       null,
    constraint timesheet_employees_penalties_author_id_foreign
        foreign key (author_id) references users (id),
    constraint timesheet_employees_penalties_editor_id_foreign
        foreign key (editor_id) references users (id),
    constraint timesheet_employees_penalties_timesheet_card_id_foreign
        foreign key (timesheet_card_id) references timesheet_cards (id)
)
    comment 'Таблица штрафов сотрудников' collate = utf8mb4_unicode_ci;

create table if not exists timesheet_employees_summary_hours
(
    id                        bigint unsigned auto_increment comment 'Уникальный идентификатор сводных часов сотрудника'
        primary key,
    timesheet_card_id         bigint unsigned not null comment 'Идентификатор табеля учета рабочего времени',
    timesheet_day_category_id bigint unsigned null comment 'Тип часов (например, отработанные, отпускные, больничные)',
    date                      date            not null comment 'Дата сводных часов',
    count                     int             null comment 'Количество часов',
    author_id                 int unsigned    not null comment 'Идентификатор пользователя-автора записи',
    editor_id                 int unsigned    not null comment 'Идентификатор пользователя, который внес последние изменения в запись',
    created_at                timestamp       null,
    updated_at                timestamp       null,
    deleted_at                timestamp       null,
    constraint summary_hours_timesheet_date_category_id_foreign
        foreign key (timesheet_day_category_id) references timesheet_day_categories (id),
    constraint timesheet_employees_summary_hours_author_id_foreign
        foreign key (author_id) references users (id),
    constraint timesheet_employees_summary_hours_editor_id_foreign
        foreign key (editor_id) references users (id),
    constraint timesheet_employees_summary_hours_timesheet_card_id_foreign
        foreign key (timesheet_card_id) references timesheet_cards (id)
)
    comment 'Таблица сводных часов сотрудника' collate = utf8mb4_unicode_ci;

create index if not exists timesheet_employees_summary_hours_date_index
    on timesheet_employees_summary_hours (date);

create table if not exists timesheet_project_objects_bonuses
(
    id                bigint unsigned auto_increment comment 'Уникальный идентификатор бонуса для объекта проекта в табеле учета рабочего времени'
        primary key,
    timesheet_card_id bigint unsigned not null comment 'Идентификатор табеля учета рабочего времени',
    project_object_id int unsigned    not null comment 'Идентификатор объекта проекта',
    name              varchar(255)    null comment 'Наименование бонуса',
    value             double(8, 2)    not null comment 'Значение бонуса',
    author_id         int unsigned    not null comment 'Идентификатор пользователя-автора записи',
    editor_id         int unsigned    not null comment 'Идентификатор пользователя, который внес последние изменения в запись',
    created_at        timestamp       null,
    updated_at        timestamp       null,
    deleted_at        timestamp       null,
    constraint timesheet_project_objects_bonuses_author_id_foreign
        foreign key (author_id) references users (id),
    constraint timesheet_project_objects_bonuses_editor_id_foreign
        foreign key (editor_id) references users (id),
    constraint timesheet_project_objects_bonuses_project_object_id_foreign
        foreign key (project_object_id) references project_objects (id),
    constraint timesheet_project_objects_bonuses_timesheet_card_id_foreign
        foreign key (timesheet_card_id) references timesheet_cards (id)
)
    comment 'Таблица бонусов для объектов проектов в табеле учета рабочего времени' collate = utf8mb4_unicode_ci;

create table if not exists user_permissions
(
    id            int unsigned auto_increment
        primary key,
    user_id       int unsigned null,
    permission_id int unsigned null,
    created_at    timestamp    null,
    updated_at    timestamp    null,
    constraint user_permissions_permission_id_foreign
        foreign key (permission_id) references permissions (id)
            on delete cascade,
    constraint user_permissions_user_id_foreign
        foreign key (user_id) references users (id)
            on delete cascade
)
    collate = utf8mb4_unicode_ci;

create index if not exists users_brigade_id_index
    on users (brigade_id);

create index if not exists users_job_category_id_index
    on users (job_category_id);

create table if not exists users_settings
(
    id         bigint unsigned auto_increment comment 'Уникальный идентификатор'
        primary key,
    user_id    int unsigned not null comment 'Идентификатор пользователя',
    codename   varchar(255) not null comment 'Кодовое наименование настройки',
    value      varchar(255) not null comment 'Значение',
    created_at timestamp    null,
    updated_at timestamp    null,
    deleted_at timestamp    null,
    constraint users_settings_user_id_foreign
        foreign key (user_id) references users (id)
)
    collate = utf8mb4_unicode_ci;

create table if not exists vacations_histories
(
    id               int unsigned auto_increment
        primary key,
    vacation_user_id int unsigned         not null,
    support_user_id  int unsigned         not null,
    from_date        varchar(255)         null,
    by_date          varchar(255)         null,
    return_date      varchar(255)         null,
    is_actual        tinyint(1) default 1 not null,
    change_authority tinyint(1) default 0 not null,
    created_at       timestamp            null,
    updated_at       timestamp            null
)
    collate = utf8mb4_unicode_ci;

create table if not exists vehicle_categories
(
    id          bigint unsigned auto_increment
        primary key,
    user_id     int unsigned not null,
    name        text         not null,
    description text         null,
    created_at  timestamp    null,
    updated_at  timestamp    null,
    deleted_at  timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists vehicle_category_characteristics
(
    id          bigint unsigned auto_increment
        primary key,
    category_id int unsigned         not null,
    name        text                 not null,
    short_name  text                 null,
    unit        text                 null,
    `show`      tinyint(1) default 1 not null,
    required    tinyint(1) default 0 not null,
    created_at  timestamp            null,
    updated_at  timestamp            null,
    deleted_at  timestamp            null
)
    collate = utf8mb4_unicode_ci;

create table if not exists versions
(
    id          int unsigned auto_increment
        primary key,
    description varchar(255) not null,
    date        date         not null,
    version     varchar(255) not null,
    created_at  timestamp    null,
    updated_at  timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists w_v_work_material_complects
(
    id             int unsigned auto_increment
        primary key,
    complect_name  varchar(255) not null,
    work_volume_id int unsigned not null,
    wv_work_id     int unsigned not null,
    created_at     timestamp    null,
    updated_at     timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists work_volume_material_complects
(
    id             int unsigned auto_increment
        primary key,
    wv_material_id int unsigned not null,
    work_volume_id int unsigned not null,
    name           varchar(255) not null,
    created_at     timestamp    null,
    updated_at     timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists work_volume_materials
(
    id                    int unsigned auto_increment
        primary key,
    user_id               int unsigned                   not null,
    work_volume_id        int unsigned                   not null,
    manual_material_id    int unsigned                   not null,
    is_our                tinyint(1)                     not null,
    time                  int unsigned                   null,
    count                 double(10, 3)                  null,
    is_tongue             tinyint(1)   default 1         not null,
    price_per_one         double(15, 2)                  null,
    result_price          double(15, 2)                  null,
    created_at            timestamp                      null,
    updated_at            timestamp                      null,
    security_price_one    double(15, 2)                  null,
    security_price_result double(15, 2)                  null,
    is_used               tinyint(1)   default 0         not null,
    manual_node_id        tinyint(1)                     null,
    combine_id            varchar(255)                   null,
    subcontractor_id      int unsigned                   null,
    is_node               tinyint(1)   default 0         not null,
    complect_id           int unsigned                   null,
    material_type         varchar(255) default 'regular' not null,
    unit                  varchar(20)  default 'шт'      not null
)
    collate = utf8mb4_unicode_ci;

create table if not exists work_volume_request_files
(
    id            int unsigned auto_increment
        primary key,
    request_id    int unsigned         not null,
    is_result     tinyint(1)           not null,
    file_name     varchar(255)         not null,
    original_name varchar(255)         not null,
    created_at    timestamp            null,
    updated_at    timestamp            null,
    is_proj_doc   tinyint(1) default 0 not null
)
    collate = utf8mb4_unicode_ci;

create table if not exists work_volume_requests
(
    id             int unsigned auto_increment
        primary key,
    user_id        int unsigned           not null,
    project_id     int unsigned           not null,
    work_volume_id int unsigned           not null,
    tongue_pile    tinyint(1)             not null,
    status         int unsigned default 0 not null,
    name           varchar(255)           not null,
    description    text                   null,
    result_comment text                   null,
    created_at     timestamp              null,
    updated_at     timestamp              null
)
    collate = utf8mb4_unicode_ci;

create table if not exists work_volume_work_materials
(
    id             int unsigned auto_increment
        primary key,
    wv_work_id     int unsigned not null,
    wv_material_id int unsigned not null,
    created_at     timestamp    null,
    updated_at     timestamp    null
)
    collate = utf8mb4_unicode_ci;

create table if not exists work_volume_works
(
    id                    int unsigned auto_increment
        primary key,
    user_id               int unsigned              not null,
    work_volume_id        int unsigned              not null,
    manual_work_id        int unsigned              not null,
    count                 double(10, 3)             null,
    term                  int unsigned              null,
    is_tongue             tinyint(1)                not null,
    price_per_one         double(15, 2)             null,
    result_price          double(15, 2)             null,
    subcontractor_file_id int unsigned              null,
    created_at            timestamp                 null,
    updated_at            timestamp                 null,
    gantt_prior           int unsigned              null,
    is_hidden             tinyint(1)   default 0    not null,
    `order`               int unsigned default 1    not null,
    unit                  varchar(20)  default 'шт' not null
)
    collate = utf8mb4_unicode_ci;

create table if not exists work_volumes
(
    id             int unsigned auto_increment
        primary key,
    user_id        int unsigned                        not null,
    project_id     int unsigned                        not null,
    version        int unsigned default 1              not null,
    status         int unsigned default 1              not null,
    created_at     timestamp                           null,
    updated_at     timestamp                           null,
    is_save_tongue int unsigned default 0              not null,
    is_save_pile   int unsigned default 0              not null,
    depth          varchar(255)                        null,
    type           int unsigned                        not null,
    deleted_at     timestamp                           null,
    `option`       varchar(255) default 'По умолчанию' not null
)
    collate = utf8mb4_unicode_ci;


