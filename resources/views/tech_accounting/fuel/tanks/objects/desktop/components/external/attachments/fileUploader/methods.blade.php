<script>
    const handleOnUploadStarted = (e) => {
        const newFileDiv = $('<div>').addClass('newFileDivCSS')
        const progressBarDiv = $('<div>').css({
            'width': '90%'
        })
        externalUploadingFiles.push(e);
        let newFile = newFileDiv.attr('id', 'newFile' + externalUploadingFiles.length)
        let progressBar = progressBarDiv.attr('id', 'progressBar' + externalUploadingFiles.length).addClass('progressBar')
        $(newFile).append(progressBar)
        $('#newFilesListWrapper').append(newFile)
    }

    const handleOnProgress = (e) => {
        bars = $('.progressBar');
        if (bars.length) {
            for (let index = 0; index < bars.length; index++) {
                const element = bars[index];
                let newFileUploadProgressBar = $('#' + element.id).dxProgressBar(externalProgressBarSettings).dxProgressBar('instance');
                newFileUploadProgressBar.option('value', (e.bytesLoaded / e.bytesTotal) * 100);
            }
        }
    }

    const handleOnUploaded = (e) => {
        let newFileEntryId = JSON.parse(e.request.response).fileEntryId
        let newFileName = JSON.parse(e.request.response).filename
        let isFileImg = e.file.type.includes('image')
        let isFilePdf = e.file.type.includes('pdf')
       
        externalNewAttachments.push(newFileEntryId)

        $('#newFile' + externalUploadingFiles.length).remove()

        const {file} = e;
        const fileReader = new FileReader();
        fileReader.readAsDataURL(file);
        let i = 0;
        fileReader.onload = function() {
            $('#newFile' + externalUploadingFiles.length).remove()

            if (isFileImg) {
                newFileURL = JSON.parse(e.request.response).fileEntry.filename
                newFileImgWrapper = $('<div>').css({'cursor': 'pointer'}).addClass('newFileImgWrapper newFileDivCSS')
            } else {
                if (e.file.name.includes('.xls') || e.file.name.includes('.xlsx'))
                    newFileURL = 'img/fileIcons/xls-icon.png'

                if (e.file.name.includes('.doc') || e.file.name.includes('.docx'))
                    newFileURL = 'img/fileIcons/doc-icon.png'

                if (e.file.name.includes('.pdf'))
                    newFileURL = 'img/fileIcons/pdf-icon.png'

                newFileImgWrapper = $('<div>').addClass('newFileDivCSS').css({
                    'padding': '10px',
                    'height': 'auto'
                })
            }

            newFileImgWrapper.attr('id', 'fileId-' + newFileEntryId).addClass('attachmentFileWrapper')
            
            let imgSrc = window.location.protocol + '//' + window.location.host + '/' + newFileURL
            let newFileImg = $('<img src ="http://erp.loc/storage/docs/fuel_flow/file-65202ce390625.jpg">')
            .attr('src', imgSrc)
            .css({
                'width': '100%',
                'object-fit': 'cover',
                'border-radius': '10px'
            })
            if (isFileImg)
                newFileImg.addClass('newFileImg')

            $(newFileImgWrapper).css({'border': 0}).append(newFileImg)
            
            $('<div>').text(e.file.name)
                .attr('title', e.file.name)
                .addClass('attachmentFileName')
                .appendTo(newFileImgWrapper)

            if (isFileImg) {
                $(newFilesListWrapper).append(newFileImgWrapper)
            } else {
                $(newFilesNotImgListWrapper).append(newFileImgWrapper)
            }

            externalUploadingFiles.pop()

            i++;
            if (!externalUploadingFiles.length && i > 0) {

                // Добавление Lightgallery

                let images = document.querySelectorAll('.newFileImg')
                if (images)
                    for (let index = 0; index < images.length; index++) {
                        const element = images[index];
                        element.addEventListener('click', (e) => {
                            createDynamicLightGalleryData(e.target)
                        })
                    }

                // Всплывающие элементы над файлами - кнопка удаления

                $('.newFileDivCSS').hover(

                    function() {

                        let deleteButton = $('<div id="attachmentNewHoverDeleteButton">').dxButton({
                            icon: "fas fa-trash",
                            hint: "Удалить",
                            elementAttr: {
                                class: 'attachmentHoverDeleteButton attachmentNewHoverDeleteButton'
                            },
                            onClick(e) {
                                let fileId = e.element.closest('.newFileDivCSS')[0]?.getAttribute("id")?.split('-')[1];
                                externalDeletedAttachments.push(fileId)
                                e.element.closest('.newFileDivCSS').remove()
                            },
                        })

                        $(this).append($(deleteButton));
                    },
                    function() {
                        $('#attachmentNewHoverDeleteButton').remove()
                    }
                )

            }

        }
    }

    
</script>