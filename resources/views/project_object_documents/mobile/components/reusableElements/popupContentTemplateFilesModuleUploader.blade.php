<script>
    const progressBarSettings = {
        min: 0,
        max: 100,
        width: '90%',
        showStatus: false,
        visible: true,
    }

    let uploadingFiles = [];

    const renderFilesUploader = (containerScrollableWrapper) => {
        const fileUploadButton =
            $('<div style="margin-bottom: 20px">')
                .attr('id', 'fileUploadButton')
                .dxButton({
                    text: "Загрузить файлы",
                    icon: 'upload',
                    elementAttr: {
                        width: '100%',
                    },
                    onClick() {
                        dropZoneExternal.click()
                    }
                }).appendTo(containerScrollableWrapper);

        $('<div id="newFilesListWrapper" class="filesGroupWrapperClass">').appendTo(containerScrollableWrapper)
        $('<div id="newFilesNotImgListWrapper" class="filesGroupWrapperClass">').appendTo(containerScrollableWrapper)
        $('<div id="dropZoneExternal" style="display:none">').appendTo(containerScrollableWrapper)

        appendFileUploader(containerScrollableWrapper)
    }

    const appendFileUploader = (containerScrollableWrapper) => {
        $('<div>').dxFileUploader({
            dialogTrigger: '#dropZoneExternal',
            multiple: true,
            visible: false,
            uploadMode: 'instantly',
            uploadUrl: "{{route('projectObjectDocument.uploadFiles')}}" + '?id=' + 0,
            uploadHeaders: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            onUploadStarted(e) {
                $('#noAttachmentsNotification').remove()

                const newFileDiv =
                    $('<div>')
                        .addClass('newFileDivCSS')

                const progressBarDiv =
                    $('<div>')
                        .css({
                            'width': '90%'
                        })

                uploadingFiles.push(e);

                let newFile = newFileDiv.attr('id', 'newFile' + uploadingFiles.length)
                let progressBar = progressBarDiv.attr('id', 'progressBar' + uploadingFiles.length).addClass('progressBar')
                $(newFile).append(progressBar)
                $('#newFilesListWrapper').append(newFile)
            },

            onProgress(e) {
                bars = $('.progressBar');
                if (bars.length) {
                    for (let index = 0; index < bars.length; index++) {
                        const element = bars[index];
                        let newFileUploadProgressBar = $('#' + element.id).dxProgressBar(progressBarSettings).dxProgressBar('instance');
                        newFileUploadProgressBar.option('value', (e.bytesLoaded / e.bytesTotal) * 100);
                    }
                }
            },

            onUploaded(e) {
                let newFileEntryId = JSON.parse(e.request.response).fileEntryId
                let newFileName = JSON.parse(e.request.response).filename

                let isFileImg = e.file.type.includes('image')
                let isFilePdf = e.file.type.includes('pdf')

                newAttachments.push(newFileEntryId)

                $('#newFile' + uploadingFiles.length).remove()

                const {file} = e;
                const fileReader = new FileReader();
                fileReader.readAsDataURL(file);

                let i = 0;
                fileReader.onload = function () {
                    $('#newFile' + uploadingFiles.length).remove()

                    if (isFileImg) {
                        newFileURL = 'storage/docs/project_object_documents/' + newFileName
                    } else {
                        if (e.file.name.includes('.xls') || e.file.name.includes('.xlsx'))
                            newFileURL = 'img/fileIcons/xls-icon.png'

                        if (e.file.name.includes('.doc') || e.file.name.includes('.docx'))
                            newFileURL = 'img/fileIcons/doc-icon.png'

                        if (e.file.name.includes('.pdf'))
                            newFileURL = 'img/fileIcons/pdf-icon.png'
                    }

                    if (isFileImg) {
                        newFileImgWrapper = $('<div>').css({
                            'cursor': 'pointer'
                        }).addClass('newFileImgWrapper')
                    } else {
                        newFileImgWrapper = $('<div>')
                    }

                    newFileImgWrapper.attr('id', 'fileId-' + newFileEntryId).addClass('attachmentFileWrapper')

                    let newFileImg = $('<img>')
                        .attr('src', newFileURL)
                        .css({
                            'width': '150px',
                            'height': '130px',
                            'border-radius': '5px'
                        })

                    if (isFileImg)
                        newFileImg.addClass('newFileImg').css('object-fit', 'cover')
                    else
                        newFileImg.css('object-fit', 'scale-down')

                    $(newFileImgWrapper).css({
                        'border': 0
                    }).append(newFileImg)

                    $('<div>').text(e.file.name)
                        .attr('title', e.file.name)
                        .addClass('attachmentFileName')
                        .appendTo(newFileImgWrapper)

                    if (isFileImg) {
                        $(newFilesListWrapper).append(newFileImgWrapper)
                    } else {
                        $(newFilesNotImgListWrapper).append(newFileImgWrapper)
                    }

                    uploadingFiles.pop()

                    i++;
                    if (!uploadingFiles.length && i > 0) {
                        let images = document.querySelectorAll('.newFileImg')
                        if (images)
                            for (let index = 0; index < images.length; index++) {
                                const element = images[index];
                                element.addEventListener('click', (e) => {
                                    createDynamicLightGalleryData(e)
                                })
                            }
                    }
                }
            }

        }).appendTo(containerScrollableWrapper);
    }
</script>
