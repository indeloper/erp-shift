<script>
    const handleOnUploadStarted = (e) => {
        const newFileDiv = $('<div>').addClass('newFileDivWrapper')
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
                let newFileUploadProgressBar = $('#' + element.id).dxProgressBar(progressBarSettings).dxProgressBar('instance');
                newFileUploadProgressBar.option('value', (e.bytesLoaded / e.bytesTotal) * 100);
            }
        }
    }

    const handleOnUploaded = (e) => {
        let newFileEntryId = JSON.parse(e.request.response).fileEntryId
        let newFileName = JSON.parse(e.request.response).filename
   
        externalNewAttachments.push(newFileEntryId)

        $('#newFile' + externalUploadingFiles.length).remove()

        let fileType = 'any'

        if(e.file.type.includes('image')) {
            fileType = 'img'
        } 
        else if(e.file.type.includes('video')) {
            fileType = 'video'
        }

        deviceType='desktop'
        fileDisplayContext='entityNewFiles'

        const fileLableWrapper = getFileLableWrapper(fileType, deviceType, fileDisplayContext, e.file).addClass('attachmentFileWrapper newFileDivWrapper')
        let newFileImg = $('<img>')
   
        const {file} = e;
        const fileReader = new FileReader();
        fileReader.readAsDataURL(file);
        let i = 0;

        fileReader.onload = function() {

            $('#newFile' + externalUploadingFiles.length).remove()
            
            fileLableWrapper
                .attr('id', 'fileId-' + newFileEntryId)
                .css({'border': 0})
                .append(newFileImg)

            if (fileType === 'img') {

                imgSrc = window.location.protocol + '//' + window.location.host + '/' + JSON.parse(e.request.response).fileEntry.filename
                newFileImg.addClass('newFileImg')
                $(newFilesListWrapper).append(fileLableWrapper)

            } else {
                if (fileType === 'video') {
                    imgSrc = window.location.protocol + '//' + window.location.host + '/img/fileIcons/video-icon.png'
                    $(newVideoFilesWrapper).append(fileLableWrapper)
                }
                else {
                    if (e.file.name.includes('.xls') || e.file.name.includes('.xlsx'))
                    imgSrc = window.location.protocol + '//' + window.location.host + '/' + 'img/fileIcons/xls-icon.png'

                    if (e.file.name.includes('.doc') || e.file.name.includes('.docx'))
                        nimgSrc = window.location.protocol + '//' + window.location.host + '/' + 'img/fileIcons/doc-icon.png'

                    if (e.file.name.includes('.pdf'))
                        imgSrc = window.location.protocol + '//' + window.location.host + '/' + 'img/fileIcons/pdf-icon.png'

                    $(newFilesNotImgListWrapper).append(fileLableWrapper)
                }    
                
                newFileImg.addClass('newAttachmentIcon')
                
                fileLableWrapper
                    .css({
                        'padding': '10px',
                        'height': 'auto'
                    })
            }

            newFileImg.attr('src', imgSrc)

            $('<div>').text(e.file.name)
                .attr('title', e.file.name)
                .addClass('attachmentFileName')
                .appendTo(fileLableWrapper)

                externalUploadingFiles.pop()

            i++;
            if (!externalUploadingFiles.length && i > 0) {
                // Добавление Lightgallery и пр на последней итерации
                addLightgalleryListenersImg('newFileImg')
                addLightgalleryListenersVideo('newVideoFiles')
                addHoverAttachmentElements('newFileDivWrapper')

            }

        }
    }

    
</script>