<script>
    // Шаблон существующего - редактируемого документа
    const popupContentTemplate = function() {
        const popupContentWrapper = $('<div id="popupContentWrapper">')
            .css({
                'width': '100%',
                'height': '100%',
                'positon': 'relative'
            })

        $('<div id="popupContainer">')
            .appendTo(popupContentWrapper)

        // $('<div id="menuButtons" style="position:absolute; z-index: 1000; bottom: 0; left:0; width:100%; background:#ededed">')
        //     .dxButtonGroup({
        //         items: [{
        //                 text: 'Инфо',
        //                 template: '<div style="display: flex; align-items:center; font-size: 18px"><div class="fa fa-info-circle info-circle-icon-color" style="padding-top: 1px; color: #725fdb; "></div><div style="margin-left:6px">Инфо</div></div>',
        //                 elementAttr: {
        //                     width: '33.5%',
        //                     height: '60px',
        //                     id: 'menuInfoButton'
        //                 }
        //             },
        //             {
        //                 text: 'История',
        //                 template: '<div style="display: flex; align-items:center; font-size: 18px"><span class="fa fa-comment comment-icon-color" style="padding-top: 1px; color: #1b91d7;"></span><span style="margin-left:6px">История</span></div>',
        //                 elementAttr: {
        //                     width: '33.5%',
        //                     height: '60px'
        //                 }
        //             },
        //             {
        //                 text: 'Файлы',
        //                 template: '<div style="display: flex; align-items:center; font-size: 18px"><span class="fa fa-file" style="padding-top: 1px;"></span><span style="margin-left:6px">Файлы</span></div>',
        //                 elementAttr: {
        //                     width: '33.5%',
        //                     height: '60px'
        //                 }
        //             },
        //         ],
        //         stylingMode: 'outlined',
        //         elementAttr: {
        //             positon: 'absolute'
        //         },
        //         keyExpr: 'text',
        //         onItemClick(e) {
        //             if (e.itemData.text === 'Инфо')
        //                 renderInfoTemplate()
        //             if (e.itemData.text === 'История')
        //                 renderHistoryTemplate()
        //             if (e.itemData.text === 'Файлы')
        //                 renderFilesTemplate()
        //         },
        //     }).appendTo(popupContentWrapper)

        return popupContentWrapper;
    }

</script>