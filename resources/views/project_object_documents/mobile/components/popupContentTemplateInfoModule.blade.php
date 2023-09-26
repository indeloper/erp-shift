<script>
    const renderInfoTemplate = () => {
        // $('.dx-toolbar-center .dx-item-content').html('<div>Инфо</div>')
        const container = $('#popupContainer')
        container.html('')
        const containerScrollableWrapper = $('<div id="containerScrollableWrapper">').appendTo(container)
        
        $('<div>')
            .addClass('documentElemMobile')
            .html('<b>Объект:</b><br>' + choosedDocumentItemData.project_object_short_name)
            .appendTo(containerScrollableWrapper)
        
        $('<div>')
            .addClass('documentElemMobile')
            .html('<b>Документ:</b><br>' + choosedDocumentItemData.document_name)
            .appendTo(containerScrollableWrapper)
        

        /// Дата и Тип
        const dateAndTypeWrapper = $('<div>').css({
            'display': 'flex', 
            'width': '100%',
            'justify-content': 'space-between'
        }).appendTo(containerScrollableWrapper)
        
        const document_date = choosedDocumentItemData.document_date ? choosedDocumentItemData.document_date : 'Не указана'
        $('<div>')
            .css({'width': '48%', 'margin': 0})
            .addClass('documentElemMobile')
            .html('<b>Дата:</b><br>' + document_date)
            .appendTo(dateAndTypeWrapper)
        
        $('<div>')
            .css({'width': '48%', 'margin': 0})
            .addClass('documentElemMobile')
            .html('<b>Тип:</b><br>' + choosedDocumentItemData.type.name)
            .appendTo(dateAndTypeWrapper)

        /// Статус и опции
        const statusAndOptionsWrapper = $('<div>')
            .attr('id', 'statusAndOptionsWrapper')
            .addClass('documentElemMobile')
            .html('<b>Статус и опции:</b><br>')
            .appendTo(containerScrollableWrapper)

        renderStatusSelectBox(statusAndOptionsWrapper)
        renderStatusOptions(statusAndOptionsWrapper)


        /// Ответственные
        ///////////////////////////////////
        let responsiblesPtoDiv = $('<div>')
            .attr('id', 'responsiblesPto')
            .addClass('documentElemMobile')
            .html('<b>Ответственные ПТО:</b><br>')
            .appendTo(containerScrollableWrapper)

        let responsiblesPtoList = projectObjectsStore?.__rawData?.filter(el=>el.id === choosedDocumentItemData.project_object_id)[0]?.tongue_pto_engineer_full_names
        responsiblesPtoDiv.append(responsiblesPtoList)
        ///////////////////////////////////
        let responsiblesForemanDiv = $('<div>')
            .attr('id', 'responsiblesForeman')
            .addClass('documentElemMobile')
            .html('<b>Ответственные прорабы:</b><br>')
            .appendTo(containerScrollableWrapper)

        let responsiblesForemanList = projectObjectsStore?.__rawData?.filter(el=>el.id === choosedDocumentItemData.project_object_id)[0]?.tongue_foreman_full_names
        responsiblesForemanDiv.append(responsiblesForemanList)
        ///////////////////////////////////
        let responsiblesProjectManagerDiv = $('<div>')
            .attr('id', 'responsiblesProjectManager')
            .addClass('documentElemMobile')
            .html('<b>Ответственные РП:</b><br>')
            .appendTo(containerScrollableWrapper)

        let responsiblesProjectManagerList = projectObjectsStore?.__rawData?.filter(el=>el.id === choosedDocumentItemData.project_object_id)[0]?.tongue_project_manager_full_names
        responsiblesProjectManagerDiv.append(responsiblesProjectManagerList)
        ///////////////////////////////////

        
    }
</script>