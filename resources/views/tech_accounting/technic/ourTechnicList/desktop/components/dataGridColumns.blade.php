<script>
    const dataGridColumns = [

        {
            visible: false,
            dataField: "exploitation_start",
            caption: "Начало эксплуатации",
            dataType: "date",
        },


        {
            visible: false,
            caption: "Компания",
            dataField: "company_id",
            lookup: {
                dataSource: additionalResources.companies,
                valueExpr: "id",
                displayExpr: "name"
            },
        },
        {
            visible: false,
            caption: "Контрагент",
            dataField: "contractor_id",
            lookup: {
                dataSource: additionalResources.contractors,
                valueExpr: "id",
                displayExpr: "short_name"
            },
        },
        {
            visible: false,
            caption: "Год выпуска",
            dataField: "manufacture_year",
            dataType: "number",
        },
        {
            visible: false,
            caption: "Заводской номер",
            dataField: "serial_number",
        },
        {
            visible: false,
            caption: "Гос. номер",
            dataField: "registration_number",
        },
        {
            caption: "Сторонняя техника",
            dataField: "third_party_mark",
            dataType: "boolean",
            // editorType: 'dxCheckBox',
            editorOptions: {
                enableThreeStateBehavior: false
            },
            width: 75,
        },
        {
            visible: false,
            caption: "Идентификатор",
            dataField: "id",
            width: 75,
        },
        {
            dataField: "inventory_number",
            caption: "Бортовой номер",
            width: 75,
        },
        {
            visible: false,
            caption: "Категория",
            dataField: "technic_category_id",
            lookup: {
                dataSource: additionalResources.technicCategories,
                valueExpr: "id",
                displayExpr: "name"
            },
        },
        {
            visible: false,
            caption: "Ответственный",
            dataField: "responsible_id",
            lookup: {
                dataSource: additionalResources.technicResponsibles,
                valueExpr: "id",
                displayExpr: "user_full_name"
            },
        },
        {
            visible: false,
            caption: "Марка",
            dataField: "technic_brand_id",
            lookup: {
                dataSource: additionalResources.technicBrands,
                valueExpr: "id",
                displayExpr: "name"
            },
        },

        {
            visible: false,
            caption: "Модель",
            dataField: "technic_brand_model_id",
            lookup: {
                dataSource: additionalResources.technicModels,
                valueExpr: "id",
                displayExpr: "name"
            },
        },

        {
            caption: "Наименование",
            dataField: "name",
        },

        {
            caption: "Объект",
            dataField: "object_id",
            useTagBoxRowFilter: true,
            lookup: {
                dataSource: additionalResources.objects,
                valueExpr: "id",
                displayExpr: "short_name",
            },
            cellTemplate(container, options) {
                let cellContent
                if(options.row.data.status_slug === 'inProgress') {
                    const objectFrom = additionalResources.objects.find(el=>el.id === options.row.data.previous_object_id)?.short_name
                    const objectTo = additionalResources.objects.find(el=>el.id === options.row.data.object_id)?.short_name
                    const arrowDiv = '<div style="width:100%; display:flex; justify-content:center; font-size:200%">⇩</div>'
                    cellContent = `<div>${objectFrom}</div> ${arrowDiv} <div>${objectTo}</div>`
                }
                if(options.row.data.status_slug === 'completed') {
                    cellContent = additionalResources.objects.find(el=>el.id === options.row.data.object_id)?.short_name
                }
                $('<div>').html(cellContent).appendTo(container)
            },
            width: 400
        },

        {
            visible: userPermissions.technics_create_update_delete,
            type: "buttons",
            buttons: [
                'edit',
                'delete'
            ]
        }


    ];
</script>
