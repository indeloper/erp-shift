<script>

    const renderFileUploader = (wrapperElement) => {
        wrapperElement.append('<div id="dropZoneExternal" >')
        wrapperElement.append('<div id="fileUploaderAnchorDiv" >')
        wrapperElement.append('<div id="fileUploaderNewFileButtonAnchorDiv" >')
        wrapperElement.append('<div id="newFilesListWrapper" class="filesGroupWrapperClass">')
        wrapperElement.append('<div id="newFilesNotImgListWrapper" class="filesGroupWrapperClass">')
        wrapperElement.append('<div id="newVideoFilesWrapper" class="filesGroupWrapperClass newVideoFiles">')
        
        let checkDropZoneIsAvailable = setInterval(() => {

            if (
                    document.getElementById('dropZoneExternal')
                    && document.getElementById('fileUploaderAnchorDiv')
                ) 
                {
                    clearInterval(checkDropZoneIsAvailable);

                    $('#fileUploaderAnchorDiv').dxFileUploader({
                        dialogTrigger: '#dropZoneExternal',
                        multiple: true,
                        visible: false,
                        uploadMode: 'instantly',
                        uploadUrl: "{{route('building::tech_acc::fuel::fuelFlow::'.'uploadFile')}}"  + '?id=' + 0,
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

                    $('#fileUploaderNewFileButtonAnchorDiv')
                        .dxButton({
                            text: "Загрузить файлы",
                            icon: 'upload',
                            onClick(){
                                dropZoneExternal.click()
                            }
                        })


                }
        }
        , 100);
    }

</script>