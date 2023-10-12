<script>
    const renderFileDisplayer = (wrapperElement) => {

        if(!editingRowId) {
            wrapperElement.append('<div id="noAttachmentsNotification" class="documentElemMobile"><div class="popup-field-nodata">Нет сохраненных файлов</div></div>')
            return
        }

        $('<div id="filesOnServerListWrapper" >').appendTo(wrapperElement);
        let loadIndicator = $('<div>')
        .dxLoadIndicator({
            elementAttr:{
                id: 'loadIndicatorFilesOnServer'
            },
            height: 50,
            width: 50,
        }).appendTo(wrapperElement);

        if (externalEntityInfoByID.isLoaded()) {
            renderLoadedAttachments(wrapperElement)
        } else {
            externalEntityInfoByID.load()
            let checkexternalEntityInfoByIDAvailable = setInterval(() => {
                if (externalEntityInfoByID.isLoaded()) {
                    clearInterval(checkexternalEntityInfoByIDAvailable);
                    renderLoadedAttachments(wrapperElement)
                }
            }, 100)
        }
    }

    const renderLoadedAttachments = (filesOnServerListWrapper) => {
            
        $('#loadIndicatorFilesOnServer').remove()

        let filesDataset = externalEntityInfoByID.store()?.__rawData?.attachments

        if (filesDataset.length === 0) {
            filesOnServerListWrapper.append('<div id="noAttachmentsNotification" class="documentElemMobile"><div class="popup-field-nodata">Нет сохраненных файлов</div></div>')
            return
        }

        Object.keys(filesDataset).forEach(group => renderFilesGroup(group, filesDataset[group])) 
        addLightgalleryListenersImg()
        addLightgalleryListenersVideo()
    }

    const renderFilesGroup = (group, groupItems) => {
        $('#filesOnServerListWrapper').append(`<div class="files-group-header">${group}</div>`)

        let filesGroupWrapper = $('<div>').addClass('filesGroupWrapperClass')
            // .appendTo(filesOnServerListWrapper);
        let filesNotImgGroupWrapper = $('<div>').addClass('filesGroupWrapperClass')
            // .appendTo(filesOnServerListWrapper);
        let filesVideoGroupWrapper = $('<div>').addClass('filesGroupWrapperClass videoFilesOnServer')
            // .appendTo(filesOnServerListWrapper);

        handleFilesDataArr(groupItems, filesGroupWrapper, filesNotImgGroupWrapper, filesVideoGroupWrapper)

        $('#filesOnServerListWrapper').append(filesGroupWrapper)
        $('#filesOnServerListWrapper').append(filesNotImgGroupWrapper)
        $('#filesOnServerListWrapper').append(filesVideoGroupWrapper)
    }

    const handleFilesDataArr = (groupItems, filesGroupWrapper, filesNotImgGroupWrapper, filesVideoGroupWrapper) => {
        let i = 0;
        groupItems.forEach(file => {

            i++;

            let isFileImg = file.mime.includes('image')
            let isFilePdf = file.mime.includes('pdf')
            let isFileVideo = file.mime.includes('video')

            if (isFileImg) {
                
                fileOnServerDivWrapper = $('<div>').css({
                    'cursor': 'pointer'
                }).addClass('fileOnServerDivWrapper')

                fileSrc = window.location.protocol + '//' + window.location.host + '/' + file.filename

            } else {
                fileOnServerDivWrapper = $('<a>')
                    .attr({
                        'href': file.filename,
                        'target': '_blanc'
                    })
                    .addClass('fileOnServerDivWrapper')

                if (file.original_filename.includes('.xls') || file.original_filename.includes('.xlsx'))
                    fileSrc = window.location.protocol + '//' + window.location.host + '/img/fileIcons/xls-icon.png'

                if (file.original_filename.includes('.doc') || file.original_filename.includes('.docx'))
                    fileSrc = window.location.protocol + '//' + window.location.host + '/img/fileIcons/doc-icon.png'

                if (file.original_filename.includes('.pdf'))
                    fileSrc = window.location.protocol + '//' + window.location.host + '/img/fileIcons/pdf-icon.png'

                if (isFileVideo)
                    fileSrc = window.location.protocol + '//' + window.location.host + '/img/fileIcons/video-icon.png'
            }

            $(fileOnServerDivWrapper).attr('id', 'fileId-' + file.id).addClass('attachmentFileWrapper')

            let fileImg = $('<img>').attr({
                'src': fileSrc
            }).css({
                'width': '100px',
                'height': '80px',
                'border-radius': '5px'
            })

            if (isFileImg) {
                fileImg.css('object-fit', 'cover')
                fileImg.addClass('fileImg')
            } else {
                fileImg.css('object-fit', 'scale-down')
            }


            $(fileOnServerDivWrapper).append(fileImg)
            $('<div>').text(file.original_filename)
                .attr('title', file.original_filename)
                .addClass('attachmentFileName')
                .appendTo(fileOnServerDivWrapper)


            if (isFileImg) {
                $(filesGroupWrapper).append(fileOnServerDivWrapper)
            } 
            else if(!isFileVideo) {
                $(filesNotImgGroupWrapper).append(fileOnServerDivWrapper)
            }
            else {
                $(filesVideoGroupWrapper).append(fileOnServerDivWrapper)
            }
        })
    }

    function addLightgalleryListenersImg() {
        let images = document.querySelectorAll('.fileImg')
        if (images)
            for (let index = 0; index < images.length; index++) {
                const element = images[index];
                element.addEventListener('click', (e) => {
                    createDynamicLightGalleryData(e.target)
                })
            }
    }

    function addLightgalleryListenersVideo() {
        const videoFilesOnServer = document.querySelectorAll('.videoFilesOnServer')
    }


</script>