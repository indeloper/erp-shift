<script>

    const renderFileDisplayer = (wrapperElement) => {

        if (!editingRowId) {
            wrapperElement.append('<div id="noAttachmentsNotification" class="documentElemMobile"><div class="popup-field-nodata">Нет сохраненных файлов</div></div>')
            return
        }

        $('<div id="filesOnServerListWrapper" >').appendTo(wrapperElement);
        let loadIndicator = $('<div>')
            .dxLoadIndicator({
                elementAttr: {
                    id: 'loadIndicatorFilesOnServer'
                },
                height: 50,
                width: 50,
            }).appendTo(wrapperElement);

        if (entityInfoByID.isLoaded()) {
            renderLoadedAttachments(wrapperElement)
        } else {
            entityInfoByID.load()
            let checkEntityInfoByIDAvailable = setInterval(() => {
                if (entityInfoByID.isLoaded()) {
                    clearInterval(checkEntityInfoByIDAvailable);
                    renderLoadedAttachments(wrapperElement)
                }
            }, 100)
        }
    }

    const renderLoadedAttachments = (filesOnServerListWrapper) => {

        $('#loadIndicatorFilesOnServer').remove()

        let filesDataset = entityInfoByID.store()?.__rawData?.attachments

        if (filesDataset.length === 0) {
            filesOnServerListWrapper.append('<div id="noAttachmentsNotification" class="documentElemMobile"><div class="popup-field-nodata">Нет сохраненных файлов</div></div>')
            return
        }

        Object.keys(filesDataset).forEach(group => renderFilesGroup(group, filesDataset[group]))
        addLightgalleryListenersImg('fileImg')
        addLightgalleryListenersVideo('videoFilesOnServer')
        addHoverAttachmentElements('fileOnServerDivWrapper')
    }

    const renderFilesGroup = (group, groupItems) => {
        $('#filesOnServerListWrapper').append(`<div class="files-group-header">${group}</div>`)

        let filesGroupWrapper = $('<div>').addClass('filesGroupWrapperClass')
        let filesNotImgGroupWrapper = $('<div>').addClass('filesGroupWrapperClass')
        let filesVideoGroupWrapper = $('<div>').addClass('filesGroupWrapperClass videoFilesOnServer')

        handleFilesDataArr(groupItems, filesGroupWrapper, filesNotImgGroupWrapper, filesVideoGroupWrapper)

        $('#filesOnServerListWrapper').append(filesGroupWrapper)
        $('#filesOnServerListWrapper').append(filesNotImgGroupWrapper)
        $('#filesOnServerListWrapper').append(filesVideoGroupWrapper)
    }

    const handleFilesDataArr = (groupItems, filesGroupWrapper, filesNotImgGroupWrapper, filesVideoGroupWrapper, deviceType = 'desktop', fileDisplayContext = 'entityFilesOnServer') => {
        let i = 0;

        groupItems.forEach(file => {

            i++;

            let fileType = 'any'

            if (file.mime.includes('image')) {
                fileType = 'img'
            } else if (file.mime.includes('video')) {
                fileType = 'video'
            }

            const fileLableWrapper = getFileLableWrapper(fileType, deviceType, fileDisplayContext, file)
                .addClass('fileOnServerDivWrapper')

            let fileImg = $('<img>').css({
                'width': '100px',
                'height': '80px',
                'border-radius': '5px'
            })

            if (fileType === 'img') {
                fileImg.css('object-fit', 'cover')
                fileImg.addClass('fileImg')
                imgSrc = window.location.protocol + '//' + window.location.host + '/' + file.filename
                filesGroupWrapper.append(fileLableWrapper)

            } else {

                fileImg.css('object-fit', 'scale-down')

                if (fileType === 'video') {

                    imgSrc = window.location.protocol + '//' + window.location.host + '/img/fileIcons/video-icon.png'
                    filesVideoGroupWrapper.attr('id', 'filesVideoGroupWrapper' + file.id).append(fileLableWrapper)

                } else {
                    $(filesNotImgGroupWrapper).append(fileLableWrapper)

                    if (file.original_filename.includes('.xls') || file.original_filename.includes('.xlsx'))
                        imgSrc = window.location.protocol + '//' + window.location.host + '/img/fileIcons/xls-icon.png'

                    if (file.original_filename.includes('.doc') || file.original_filename.includes('.docx'))
                        imgSrc = window.location.protocol + '//' + window.location.host + '/img/fileIcons/doc-icon.png'

                    if (file.original_filename.includes('.pdf'))
                        imgSrc = window.location.protocol + '//' + window.location.host + '/img/fileIcons/pdf-icon.png'
                }

            }

            $(fileLableWrapper).attr('id', 'fileId-' + file.id).addClass('fileLableWrapper')

            fileImg.attr({
                'src': imgSrc
            })

            fileLableWrapper.append(fileImg)

            $('<div>').text(file.original_filename)
                .attr('title', file.original_filename)
                .addClass('attachmentFileName')
                .appendTo(fileLableWrapper)

        })
    }

</script>
