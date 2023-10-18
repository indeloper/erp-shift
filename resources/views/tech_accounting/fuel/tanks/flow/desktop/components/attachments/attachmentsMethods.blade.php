<script>
    function getFileLableWrapper(fileType, deviceType, fileDisplayContext, file) {
        if (deviceType === 'desktop') {

            if (fileType === 'img') {
                return $('<div>').css({'cursor': 'pointer'})
            }

            const filePathBase = window.location.protocol + '//' + window.location.host + '/'
            const filePathTail = file.filename ? file.filename : 'storage/docs/fuel_flow/' + file.name
            const filePath = filePathBase + filePathTail

            if (fileType === 'video') {
                const fileLableWrapper = $('<a>').css({'cursor': 'pointer'})
        
                const dataAttributes = {
                    "source": [{
                        src: filePath,
                        type: file.mime ? file.mime : file.type
                    }],
                    "attributes": {
                        "preload": false,
                        "controls": true
                    }
                }

                fileLableWrapper.attr('data-video', JSON.stringify(dataAttributes))

                return fileLableWrapper

            }

            return $('<a>')
                .css({'cursor': 'pointer'})
                .attr({
                    href: filePath,
                    target: '_blanc'
                })
        }
    }

    function addLightgalleryListenersImg(fileImgClass) {
        let images = document.querySelectorAll('.'+fileImgClass)
        if (images)
            for (let index = 0; index < images.length; index++) {
                const element = images[index];
                element.addEventListener('click', (e) => {
                    createDynamicLightGalleryData(e.target)
                })
            }
    }

    function addLightgalleryListenersVideo(videosListWrapperClass) {
        document.querySelectorAll('.' + videosListWrapperClass).forEach(el=>{

            lightGallery(document.getElementById(el.id), {
                plugins: [lgVideo],
                videojs: true,
                videojsOptions: {
                    muted: true,
                },
            });
        })

    }

    function createDynamicLightGalleryData(target) {
        let clickedElemSrc = target.attributes.src.value
        let clickedElemIndex = 0
        let lightGalleryElemsArr = []

        let galleryElemsWrapper = target.closest('div.filesGroupWrapperClass')
        let galleryElems = galleryElemsWrapper.querySelectorAll('img')

        for (let index = 0; index < galleryElems.length; index++) {
            const element = galleryElems[index];

            if (element.src.includes(target.attributes.src.value))
                clickedElemIndex = index

            lightGalleryElemsArr.push({
                src: element.src,
                thumb: element.src,
            })

            if (index === galleryElems.length - 1)
                openDynamicLightGallery(galleryElemsWrapper, lightGalleryElemsArr, clickedElemIndex)
        }

    }

    function openDynamicLightGallery(rootElem, elemsArr, elemIndex) {
        const dynamicGallery = window.lightGallery(rootElem, {
            dynamic: true,
            dynamicEl: elemsArr,
            plugins: [lgZoom, lgThumbnail, lgRotate],
            licenseKey: "0000-0000-000-0000",
            speed: 500,
        });
        dynamicGallery.openGallery(elemIndex)
    }

    function addHoverAttachmentElements(elementClass) {
        $('.'+elementClass).hover(
                function() {
                    if (!$(this).find('.dx-checkbox').length) {
                        let checkBox = $('<div>').dxCheckBox({
                            hint: "Скачать",
                            elementAttr: {
                                class: 'attacmentHoverCkeckbox'
                            },
                            onValueChanged(e) {
                                if(e.value) {
                                    $('#downloadFilesButton').dxButton({
                                        disabled: false
                                    })
                                } else {
                                    // getCheckedCheckboxesFilesToDownload() в данном месте возвращает не отмеченные чекбоксы, а все
                                    // поэтому проверка события отмены выбора при единственном имеющемся чекбоксе
                                    if(getCheckedCheckboxesFilesToDownload().length<2) {
                                        $('#downloadFilesButton').dxButton({
                                            disabled: true
                                        })
                                    }
                                }
                            }
                        })
                        $(this).append($(checkBox));
                    }

                    if (permissions.can_delete_project_object_document_files) {
                        let deleteButton = $('<div />').dxButton({
                            icon: "fas fa-trash",
                            hint: "Удалить",
                            elementAttr: {
                                class: 'attacmentHoverDeleteButton'
                            },
                            onClick(e) {
                                deleteAttachment(e.element);
                            },
                        })
                        $(this).append($(deleteButton));
                    }
                },
                function() {
                    let checkBox = $(this).find('.dx-checkbox').dxCheckBox('instance')
                    if (!checkBox.option('value'))
                        $(this).find('.attacmentHoverCkeckbox').last().remove();
                    $(this).find('.attacmentHoverDeleteButton').last().remove();
                }
            )
    }

    function deleteAttachment(element) {     

        let elemParent = element.closest('.fileLableWrapper');
        let fileId = elemParent[0]?.getAttribute("id")?.split('-')[1];

        customConfirmDialog("Вы уверены, что хотите удалить файл?")
            .show().then( dialogResult => {if(dialogResult) deleteFile()} )

        function deleteFile() {
            deletedAttachments.push(fileId)
            elemParent.remove()            
        }
    }

    function handleDownloadFilesButtonClicked() {
        checkedCheckboxes = getCheckedCheckboxesFilesToDownload();
        if(!checkedCheckboxes.length) return;
        const filesIdsToDownload = [];
        checkedCheckboxes.forEach(checkbox=>{
            if(checkbox.value == 'true') 
                filesIdsToDownload.push(checkbox.closest('.fileLableWrapper').id.split('-')[1]);
        })
        
        downloadAttachments(filesIdsToDownload)
    }

    function getCheckedCheckboxesFilesToDownload() {
        const attachmentsWrapper = document.getElementById('filesOnServerListWrapper')
        const checkboxes = attachmentsWrapper.querySelectorAll('input');
        
        if(!checkboxes.length)
        return [];

        const checkedCheckboxes = [];
        checkboxes.forEach(el=>{if(el.value) checkedCheckboxes.push(el)})

        return checkedCheckboxes;
    }

    async function downloadAttachments(filesIdsToDownload) {
        let fliesIds = {
            fliesIds: filesIdsToDownload
        }

        let response = await fetch("{{route('fileEntry.downloadAttachments')}}", {
            // let response = await fetch("{{route('projectObjectDocument.downloadAttachments')}}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json;charset=utf-8',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: JSON.stringify(fliesIds)
        });

        let result = await response.json();
        let a = document.createElement("a");
        a.href = result.zipFileLink
        a.download = 'filesArchive';
        a.click();
    }

</script>