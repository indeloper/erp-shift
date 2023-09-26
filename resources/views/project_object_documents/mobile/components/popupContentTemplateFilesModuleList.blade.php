<script>
    const renderFilesList = (containerScrollableWrapper) => {

        const filesOnServerListWrapper = $('<div id="filesOnServerListWrapper">').appendTo(containerScrollableWrapper)

        popupLoadPanel.option('visible', false)
        let filesDataset = projectObjectDocumentInfoByID.items()[0]?.attachments.original

        if (filesDataset.length === 0) {
            filesOnServerListWrapper.append('<div id="noAttachmentsNotification" class="documentElemMobile"><div class="popup-field-nodata">Нет данных</div></div>')
            return
        }

        Object.keys(filesDataset).forEach(group => renderFilesGroup(group, filesDataset[group], filesOnServerListWrapper))

        addLightgalleryListenersImg()
        addLightgalleryListenersPdf()
    }

    const renderFilesGroup = (group, groupItems, filesOnServerListWrapper) => {

        $(filesOnServerListWrapper)
            .append(`<div class="files-group-header">${group}</div>`)

        let filesGroupWrapper = $('<div>').addClass('filesGroupWrapperClass');
        let filesNotImgGroupWrapper = $('<div>').addClass('filesGroupWrapperClass');

        handleFilesDataArr(groupItems, filesGroupWrapper, filesNotImgGroupWrapper, 'filesTab')

        $(filesOnServerListWrapper).append(filesGroupWrapper)
        $(filesOnServerListWrapper).append(filesNotImgGroupWrapper)
    }

    const context = 'filesTab'

    const handleFilesDataArr = (groupItems, filesGroupWrapper, filesNotImgGroupWrapper, context) => {
        let i = 0;
        groupItems.forEach(file => {

            i++;

            let isFileImg = file.mime.includes('image')
            let isFilePdf = file.mime.includes('pdf')
            // let isFileImg = file.original_filename.includes('.jpg') || file.original_filename.includes('.jpeg') || file.original_filename.includes('.png');
            // let isFilePdf = file.original_filename.includes('.pdf')

            if (isFileImg) {
                if (!context)
                    fileOnServerDivWrapper = $('<a>').attr('href', file.filename).addClass(context ? 'fileOnServerDivWrapper' : 'fileOnServerDivWrapperInfoTab')
                else
                    fileOnServerDivWrapper = $('<div>').css({
                        'cursor': 'pointer'
                    }).addClass('fileOnServerDivWrapper')
                fileSrc = file.filename
            // } else if(!isFilePdf) {
            } else {
                    // fileOnServerDivWrapper = $('<div>').addClass(context ? 'fileOnServerDivWrapper' : 'fileOnServerDivWrapperInfoTab')
                    fileOnServerDivWrapper = $('<a>')
                    .attr({'href':file.filename, 'target':'_blanc'})
                    .addClass(context ? 'fileOnServerDivWrapper' : 'fileOnServerDivWrapperInfoTab')
                    
                    if(file.original_filename.includes('.xls') || file.original_filename.includes('.xlsx'))
                    fileSrc = 'img/fileIcons/xls-icon.png'

                    if(file.original_filename.includes('.doc') || file.original_filename.includes('.docx'))
                    fileSrc = 'img/fileIcons/doc-icon.png'

                    if(file.original_filename.includes('.pdf'))
                    fileSrc = 'img/fileIcons/pdf-icon.png'
            // } else if (isFilePdf) {
            //     fileSrc = file.filename
            //     fileOnServerDivWrapper = $('<div>').addClass(context ? 'fileOnServerDivWrapper' : 'fileOnServerDivWrapperInfoTab')
            }

            $(fileOnServerDivWrapper).attr('id', 'fileId-' + file.id).addClass('attachmentFileWrapper')

            // if (!isFilePdf) {
                let fileImg = $('<img>').attr({
                    'src': fileSrc
                }).css({
                    'width': '150px',
                    'height': '130px',
                    // 'object-fit': 'cover',
                    'border-radius': '5px'
                })
                // if (isFileImg && context)
                //     fileImg.addClass('fileImg')
                if (isFileImg){
                    fileImg.css('object-fit', 'cover')

                    if(context)
                        fileImg.addClass('fileImg')
                }
                else {
                    fileImg.css('object-fit', 'scale-down')
                }
                    

                // if(isFilePdf)
                //     fileImg.addClass('fakeCoverPDF')
                //     .attr({
                //         'data-iframe': true,
                //         'data-src': file.filename
                //     })


                $(fileOnServerDivWrapper).append(fileImg)
                $('<div>').text(file.original_filename)
                    .attr('title', file.original_filename)
                    .addClass('attachmentFileName')
                    .appendTo(fileOnServerDivWrapper)
            // } else {
            //     fileOnServerDivWrapper.css({
            //         'position': 'relative',
            //         'height': '170px'
            //     })
            //     let fileImg = $('<iframe>')
            //         .attr({
            //             'src': fileSrc
            //         }).css({
            //             'width': '100%',
            //             'height': '120px',
            //             'object-fit': 'cover',
            //             'border-radius': '5px'
            //         })

            //     let fakeCoverLayout = $('<div class="fakeCoverPDF">')
            //         .attr({
            //             'src': fileSrc,
            //             'data-iframe': true,
            //             'data-src': fileSrc
            //         })
            //         .addClass('fakeCoverPdfOnServerFilesTab')

            //     $(fileOnServerDivWrapper).append(fileImg)
            //     $(fileOnServerDivWrapper).append(fakeCoverLayout)
            //     $('<div>').text(file.original_filename)
            //         .attr('title', file.original_filename)
            //         .addClass('attachmentFileName')
            //         .appendTo(fileOnServerDivWrapper)
            // }


            if (isFileImg) {
                $(filesGroupWrapper).append(fileOnServerDivWrapper)
            } else {
                $(filesNotImgGroupWrapper).append(fileOnServerDivWrapper)
            }

        })
    }

    function addLightgalleryListenersImg() {
        let images = document.querySelectorAll('.fileImg')
        if (images)
            for (let index = 0; index < images.length; index++) {
                const element = images[index];
                element.addEventListener('click', (e) => {
                    createDynamicLightGalleryData(e)
                })
            }
    }

    // function addLightgalleryListenersPdf() {
    //     let fakeCoverPdfElems = document.querySelectorAll('.fakeCoverPDF');
    //     if (fakeCoverPdfElems) {
    //         for (let index = 0; index <= fakeCoverPdfElems.length; index++) {
    //             const element = fakeCoverPdfElems[index];
                
    //             lightGallery(element, {
    //                 selector: 'this',
    //             });
    //         }
    //     }
    // }
</script>