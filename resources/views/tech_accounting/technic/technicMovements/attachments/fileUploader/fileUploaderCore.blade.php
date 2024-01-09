<script>

    const renderFileUploader = (wrapperElement) => {
        wrapperElement.append('<div id="dropZone" >')
        wrapperElement.append('<div id="fileUploaderAnchorDiv" >')
        
        const filesUploadDownloadButtonsWrapper = $('<div id="filesUploadDownloadButtonsWrapper">')
        filesUploadDownloadButtonsWrapper.append('<div id="fileUploaderNewFileButton" >')
        filesUploadDownloadButtonsWrapper.append('<div id="downloadFilesButton">')
        wrapperElement.append(filesUploadDownloadButtonsWrapper)
        
        wrapperElement.append('<div id="newFilesListWrapper" class="filesGroupWrapperClass">')
        wrapperElement.append('<div id="newFilesNotImgListWrapper" class="filesGroupWrapperClass">')
        wrapperElement.append('<div id="newVideoFilesWrapper" class="filesGroupWrapperClass newVideoFiles">')

        let checkDropZoneIsAvailable = setInterval(() => {

                if (
                    document.getElementById('dropZone')
                    && document.getElementById('fileUploaderAnchorDiv')
                ) {
                    clearInterval(checkDropZoneIsAvailable);

                    $('#fileUploaderAnchorDiv').dxFileUploader({
                        dialogTrigger: '#dropZone',
                        multiple: true,
                        visible: false,
                        uploadMode: 'instantly',
                        uploadUrl: "{{route($routeNameFixedPart.'uploadFile')}}" + '?id=' + 0,
                        uploadHeaders: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },

                        onUploadStarted(e) {
                            handleOnUploadStarted(e)
                        },
                        onProgress(e) {
                            handleOnProgress(e)
                        },
                        onUploaded(e) {
                            handleOnUploaded(e)
                        }
                    })

                    $('#fileUploaderNewFileButton')
                        .dxButton({
                            text: "Загрузить",
                            icon: 'upload',
                            onClick() {
                                dropZone.click()
                            }
                        })

                    $('#downloadFilesButton')
                        .dxButton({
                            text: "Скачать",
                            icon: 'download',
                            disabled: true,
                            onClick() {
                                handleDownloadFilesButtonClicked()
                            }
                        })


                }
            }
            , 100);
    }

</script>
