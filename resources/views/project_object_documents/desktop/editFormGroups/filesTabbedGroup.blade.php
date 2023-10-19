@include('project_object_documents.desktop.editFormGroups.fileUploadingVariablesAndFunctions')

<script>
    const filesTabbedGroup = {
                                tabTemplate (data, index, element){
                                    return '<div style="display: flex; align-items:center"><span class="fa fa-file" style="padding-top: 1px;"></span><span style="margin-left:6px">Файлы</span></div>'
                                },
                                items: [
                                    {
                                        item: 'simple',
                                        template: (data, itemElement) => {
                                            itemElement.append('<div id="dropZoneExternal" style="width:0px; height:0px; display:none">')
                                        }
                                    },

                                    {
                                        itemType: 'simple',
                                        template: (data, itemElement) => {

                                            const fileUploader = $('<div>').dxFileUploader({
                                                selectButtonText: 'Загрузить файл',
                                                dialogTrigger: '#dropZoneExternal',
                                                multiple: true,
                                                visible: false,
                                                labelText: '',
                                                uploadMode: 'instantly',
                                                uploadUrl: "{{route('projectObjectDocument.uploadFiles')}}"  + '?id=' + 0,
                                                uploadHeaders: {
                                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                                }, 
                                                onUploadStarted(e) {

                                                    const newFileDiv = 
                                                        $('<div>')
                                                            .addClass('newFileDivCSS')

                                                    const progressBarDiv = 
                                                        $('<div>')
                                                            .css({'width': '90%'})

                                                    uploadingFiles.push(e);
                                                    let newFile = newFileDiv.attr('id', 'newFile' + uploadingFiles.length)
                                                    let progressBar = progressBarDiv.attr('id', 'progressBar' + uploadingFiles.length).addClass('progressBar')
                                                    $(newFile).append(progressBar)
                                                    $('#newFilesListWrapper').append(newFile)
                                                    
                                                },

                                                onProgress(e) {
                                                    bars = $('.progressBar');
                                                    if(bars.length) {
                                                        for (let index = 0; index < bars.length; index++) {
                                                            const element = bars[index];
                                                            let newFileUploadProgressBar = $('#'+ element.id).dxProgressBar(progressBarSettings).dxProgressBar('instance');
                                                            newFileUploadProgressBar.option('value', (e.bytesLoaded / e.bytesTotal) * 100);
                                                        }
                                                    }
                                                },

                                                onUploaded(e) {
                                                    let newFileEntryId = JSON.parse(e.request.response).fileEntryId
                                                    let newFileName = JSON.parse(e.request.response).filename
                                                    let isFileImg = e.file.type.includes('image')
                                                    let isFilePdf = e.file.type.includes('pdf')
                                                    // let isFileImg = e.file.name.includes('.jpg') || e.file.name.includes('.jpeg') || e.file.name.includes('.png');
                                                    // let isFilePdf = e.file.name.includes('.pdf')
                                                    
                                                    newAttachments.push(newFileEntryId)

                                                    $('#newFile' + uploadingFiles.length).remove()
                                                    
                                                    const { file } = e;
                                                    const fileReader = new FileReader();
                                                    fileReader.readAsDataURL(file);
                                                    let i=0;                                                    
                                                    fileReader.onload = function () {
                                                        $('#newFile' + uploadingFiles.length).remove() 

                                                        // if(isFileImg || isFilePdf){
                                                        if(isFileImg){
                                                            newFileURL = 'storage/docs/project_object_documents/' + newFileName
                                                        // } else if(!isFilePdf) {
                                                        } else {
                                                            if(e.file.name.includes('.xls') || e.file.name.includes('.xlsx'))
                                                            newFileURL = 'img/fileIcons/xls-icon.png'

                                                            if(e.file.name.includes('.doc') || e.file.name.includes('.docx'))
                                                            newFileURL = 'img/fileIcons/doc-icon.png'

                                                            if(e.file.name.includes('.pdf'))
                                                            newFileURL = 'img/fileIcons/pdf-icon.png'
                                                        } 
                                                                                                              
                                                        if(isFileImg) {
                                                            // newFileImgWrapper = $('<a>').addClass('newFileDivCSS').attr('id', 'newFileImgWrapper' + uploadingFiles.length).attr('href', newFileURL)
                                                            newFileImgWrapper = $('<div>').css({'cursor': 'pointer'}).addClass('newFileImgWrapper newFileDivCSS')
                                                        } else {
                                                            newFileImgWrapper = $('<div>').addClass('newFileDivCSS').css({'padding': '10px', 'height': 'auto'})
                                                        }
                                                        
                                                        newFileImgWrapper.attr('id', 'fileId-' + newFileEntryId).addClass('attachmentFileWrapper')

                                                        // if(!isFilePdf) {
                                                            let newFileImg = $('<img>').attr('src', newFileURL).css({'width': '100%', 'object-fit': 'cover', 'border-radius': '10px'})
                                                            if(isFileImg)
                                                            newFileImg.addClass('newFileImg')

                                                            $(newFileImgWrapper).css({'border': 0}).append(newFileImg)
                                                            $('<div>').text(e.file.name)
                                                                .attr('title', e.file.name)
                                                                .addClass('attachmentFileName')
                                                                .appendTo(newFileImgWrapper)
                                                        // } else {
                                                        //     newFileImgWrapper.css({'position': 'relative', 'height': '120px', 'border': 0, 'justify-content': 'top'})
                                                        //     let newFileImg = $('<iframe>')
                                                        //         .attr({'src': newFileURL}).css({'width': '100%', 'height': '60px', 'object-fit': 'cover', 'border-radius': '5px'})

                                                        //     let fakeCoverLayout = $('<div class="fakeCoverPDF">')
                                                        //         .attr({'src': newFileURL, 'data-iframe': true, 'data-src': newFileURL})
                                                        //         .addClass('fakeCoverPdfOnNewFiles')

                                                        //     $(newFileImgWrapper).append(newFileImg)
                                                        //     $(newFileImgWrapper).append(fakeCoverLayout)
                                                        //     $('<div>').text(e.file.name)
                                                        //         .attr('title', e.file.name)
                                                        //         .addClass('attachmentFileName')
                                                        //         .appendTo(newFileImgWrapper)
                                                        // }

                                                        if(isFileImg) {
                                                            $(newFilesListWrapper).append(newFileImgWrapper) 
                                                        } else {
                                                            $(newFilesNotImgListWrapper).append(newFileImgWrapper) 
                                                        }
                                                        
                                                        uploadingFiles.pop()  
                                                        
                                                        i++;     
                                                        if(!uploadingFiles.length && i>0) {
                                                            // addLightGallery('newFilesListWrapper') 
                                                            let images = document.querySelectorAll('.newFileImg')
                                                            if(images)
                                                            for (let index = 0; index < images.length; index++) {
                                                                const element = images[index];
                                                                element.addEventListener('click',(e) =>{ createDynamicLightGalleryData(e)})
                                                            }

                                                            // Всплывающие элементы над файлами - кнопка удаления

                                                            $('.newFileDivCSS').hover(

                                                                
                                                                function(){
                                                                   
                                                                    let deleteButton = $('<div id="attachmentNewHoverDeleteButton">').dxButton({
                                                                        icon: "fas fa-trash",
                                                                        hint: "Удалить",
                                                                        elementAttr: { class: 'attachmentHoverDeleteButton attachmentNewHoverDeleteButton'},
                                                                        onClick(e) {
                                                                            let fileId = e.element.closest('.newFileDivCSS')[0]?.getAttribute("id")?.split('-')[1];
                                                                            deletedAttachments.push(fileId)
                                                                            e.element.closest('.newFileDivCSS').remove()
                                                                        },
                                                                        onInitialized(e) {
                                                                            // переключаем кликабельность картинки
                                                                            // чтобы не было конфликта при клике по чекбоксу / кнопке / картинке
                                                                            $(e.element).hover(
                                                                                () => $(e.element).parent().on('click', ()=>{return false}),
                                                                                () => $(e.element).parent().off('click')
                                                                            )
                                                                        }
                                                                    })

                                                                    $( this ).append( $( deleteButton ) );
                                                                }, 
                                                                function(){
                                                                    $('#attachmentNewHoverDeleteButton').remove()
                                                                }
                                                            )
                                                            

                                                            let fakeCoverPdfElems =  document.getElementById('newFilesNotImgListWrapper')?.querySelectorAll('.fakeCoverPDF');
                                                            if(fakeCoverPdfElems) {
                                                                for (let index = 0; index <= fakeCoverPdfElems.length; index++) {
                                                                    const element = fakeCoverPdfElems[index];
                                                                    lightGallery(element, {
                                                                        selector: 'this',
                                                                    }); 
                                                                }
                                                            }
                                                        }
                                                         


                                                    
                                                        
                                                    }
                                                    

                                                }
                                                                                               
                                            });

                                            const buttonsWrapper = 
                                                $('<div>')
                                                    .attr('id', 'buttonsWrapper')
                                                    .css({
                                                        'position': 'absolute', 
                                                        'top': '10px',
                                                        'right': '10px',
                                                        'display': 'flex'
                                                    });

                                            const downloadFilesButton = 
                                                $('<div>')
                                                    .attr('id', 'downloadFilesButton')
                                                    .css({'marginRight': '10px'})
                                                    .dxButton({
                                                        text: "Скачать файлы",
                                                        icon: 'download',
                                                        disabled: true,
                                                        onClick(e){
                                                            checkedCheckboxes = getCheckedCheckboxesFilesToDownload()
                                                            if(!checkedCheckboxes.length) {
                                                                return;
                                                            }
                                                            const filesIdsToDownload = [];
                                                            checkedCheckboxes.forEach(checkbox=>{
                                                                if(checkbox.value == 'true') {
                                                                    let choosedFileId = checkbox.closest('.attachmentFileWrapper').id.split('-')[1];
                                                                    filesIdsToDownload.push(choosedFileId);
                                                                } 
                                                            })
                                                            
                                                            downloadAttachments(filesIdsToDownload)
                                                        }
                                                    }).appendTo(buttonsWrapper);
                                            
                                            const fileUploadButton = 
                                                $('<div>')
                                                    .attr('id', 'fileUploadButton')
                                                    .dxButton({
                                                        text: "Загрузить файлы",
                                                        icon: 'upload',
                                                        onClick(){
                                                            dropZoneExternal.click()
                                                        }
                                                    }).appendTo(buttonsWrapper);

                                            const newFilesListWrapper = 
                                                $('<div>')
                                                    .attr('id', 'newFilesListWrapper')
                                                    .addClass('filesGroupWrapperClass')
                                                    .css({
                                                        'width': '100%', 
                                                        // 'height': '20vh', 
                                                        // 'margin-top': '40px',
                                                        'overflow-y': 'auto'
                                                    });

                                            const newFilesNotImgListWrapper = 
                                                $('<div>')
                                                    .attr('id', 'newFilesNotImgListWrapper')
                                                    .css({
                                                        'width': '100%', 
                                                        // 'height': '20vh', 
                                                        // 'margin-top': '40px',
                                                        'overflow-y': 'auto'
                                                    });
                                            
                                            itemElement.css('padding-top', '30px');
                                            itemElement.append(buttonsWrapper)
                                            itemElement.append(fileUploader);
                                            itemElement.append(newFilesListWrapper);
                                            itemElement.append(newFilesNotImgListWrapper);
                                            

                                        }
                                    },

                                    {
                                        item: 'simple',
                                        template: (data, itemElement) => {

                                            const filesOnServerListWrapper = 
                                                $('<div>')
                                                    .attr('id', 'filesOnServerListWrapper')
                                                    .css({
                                                        'width': '100%', 
                                                        'height': '50vh',
                                                        // 'overflow-y': 'auto'
                                                    });
                                            
                                                    if(projectObjectDocumentInfoByID.items()[0]) {
                                                        const filesDataArr = projectObjectDocumentInfoByID.items()[0]?.attachments.original

                                                        if(filesDataArr.length === 0) {
                                                            $(itemElement).append('<span class="popup-field-nodata">Нет данных</span>')
                                                            return
                                                        }

                                                        itemElement.append(filesOnServerListWrapper);

                                                        let i = 0;
                                                        Object.keys(filesDataArr).forEach(group=>{
                                                            i++;

                                                            $(filesOnServerListWrapper).append(`<div class="files-group-header">${group}</div>`)
                                                            let filesGroupWrapper = $('<div>').attr('id', 'filesGroupWrapper' + i).addClass('filesGroupWrapperClass');
                                                            let filesNotImgGroupWrapper = $('<div>').attr('id', 'filesNotImgGroupWrapper' + i);
                                                            const filesArr = filesDataArr[group]

                                                            handleFilesDataArr(filesArr, filesGroupWrapper, filesNotImgGroupWrapper, 'filesTab') 

                                                            $(filesOnServerListWrapper).append(filesGroupWrapper)
                                                            $(filesOnServerListWrapper).append(filesNotImgGroupWrapper)                                                         
                                                            
                                                        })
                                                    } 
                                                    else 
                                                    $(itemElement).append('<span class="popup-field-nodata">Нет данных</span>')

                                                    
                                        }

                                            // Старые кнопки управления

                                            //         // $('<div />').dxButton({
                                            //         //     icon: "download",
                                            //         //     hint: "Скачать",
                                            //         //     onClick() {
                                            //         //         let a = document.createElement("a");
                                            //         //         a.href = file.filename;
                                            //         //         a.download = file.original_filename;
                                            //         //         a.click();
                                            //         //     },
                                            //         // }).appendTo(fileOnServerDivWrapper);
                                            //         // $('<div />').dxButton({
                                            //         //     icon: "fullscreen",
                                            //         //     hint: "Открыть",
                                            //         //     visible: file.original_filename.includes('.png') || file.original_filename.includes('.jpg') || file.original_filename.includes('.jpeg') || file.original_filename.includes('.pdf'),
                                            //         //     onClick() {
                                            //         //         window.open(file.filename);
                                            //         //     },
                                            //         // }).appendTo(fileOnServerDivWrapper);

                                            //         // $('<div />').dxButton({
                                            //         //     icon: "remove",
                                            //         //     hint: "Удалить",
                                            //         //     onClick(e) {
                                            //         //         deletedAttachments.push(file.id)
                                            //         //         e.element.closest('tr').remove()
                                            //         //     },
                                            //         // }).appendTo(fileOnServerDivWrapper);

                                            
                                         
                                    },
                                    
                                    // {
                                    //     name: "attachmentsGrid",
                                    //     editorType: "dxDataGrid",
                                    //     editorOptions: {
                                    //         visible: true,
                                    //         dataSource: projectObjectAttachmentsDataSource,
                                    //         wordWrapEnabled: true,
                                    //         height: '20vh',
                                    //         columnHidingEnabled: true,
                                    //         columns: [
                                    //         {
                                    //             caption: "Добавлен",
                                    //             dataField: "created_at",
                                    //             calculateCellValue: function(rowData) {
                                    //                 return new Date(rowData.created_at).toLocaleString()
                                    //             },
                                    //             hidingPriority: 2
                                    //         },
                                    //         {
                                    //             caption: "Автор",
                                    //             dataField: "author.full_name",
                                    //             hidingPriority: 1
                                    //         },
                                    //         {
                                    //             caption: "Файл",
                                    //             dataField: "original_filename",
                                    //         },
                                    //         {
                                    //             cellTemplate(container, options) {
                                    //                 container.addClass('file-operations-buttons');
                                    //                 $('<div />').dxButton({
                                    //                     icon: "download",
                                    //                     hint: "Скачать",
                                    //                     onClick() {
                                    //                         let a = document.createElement("a");
                                    //                         a.href = options.row.data.filename;
                                    //                         a.download = options.row.data.original_filename;
                                    //                         a.click();
                                    //                     },
                                    //                 }).appendTo(container);
                                    //                 $('<div />').dxButton({
                                    //                     icon: "fullscreen",
                                    //                     hint: "Открыть",
                                    //                     visible: options.row.data.original_filename.includes('.png') || options.row.data.original_filename.includes('.jpg') || options.row.data.original_filename.includes('.jpeg') || options.row.data.original_filename.includes('.pdf'),
                                    //                     onClick() {
                                    //                         window.open(options.row.data.filename);
                                    //                     },
                                    //                 }).appendTo(container);
                                    //                 $('<div />').dxButton({
                                    //                     icon: "remove",
                                    //                     hint: "Удалить",
                                    //                     onClick(e) {
                                    //                         deletedAttachments.push(options.row.data.id)
                                    //                         e.element.closest('tr').remove()
                                    //                     },
                                    //                 }).appendTo(container);
                                    //             },
                                    //         },
                                            
                                                                                       
                                    //     ]
                                    //     },
                                        
                                    // },

                                    // {
                                    //     label: {
                                    //         text: "Добавить вложение",
                                    //     },
                                    //     dataField: "files",
                                    //     editorType: "dxFileUploader",
                                    //     editorOptions: {
                                    //         multiple: false,
                                    //         accept: '*',
                                    //         value: [],
                                    //         uploadMode: 'instantly',
                                    //         onUploadStarted(){
                                    //             document.querySelector('[aria-label="Сохранить"]').hidden = true
                                    //             document.querySelector('.dx-fileuploader-content').style.display = 'flex'
                                    //             document.querySelector('.dx-fileuploader-file-container').style.boxShadow = 'none'
                                    //             document.querySelector('.dx-fileuploader-files-container').style.padding = '5px 10px 0'
                                    //         },
                                    //         onUploaded(e){
                                    //             let newFileEntryId = JSON.parse(e.request.response).fileEntryId
                                    //             newAttachments.push(newFileEntryId)
                                    //             console.log(newAttachments);
                                    //             e.component.option('disabled', 'true')
                                    //             setTimeout(()=>{
                                    //                     document.querySelector('[aria-label="Сохранить"]').hidden = false
                                    //             }, 1500)
                                    //         },
                                    //         uploadUrl: "{{route('projectObjectDocument.uploadFile')}}"  + '?id=' + editingRowId,
                                    //         uploadHeaders: {
                                    //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    //         }, 
                                    //         // height: 32, 
                                    //     }
                                                                     
                                    // },
                                ],

                        }
</script>