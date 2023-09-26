<script>

    const uploadingFiles = [];

    const newFileDivCSS = {
            
        }
    
    const fileOnServerDivCSS = {

        }
    
    const progressBarSettings = {
            min: 0,
            max: 100,
            width: '90%',
            showStatus: false,
            visible: true,
        }

        function handleFilesDataArr(filesArr, filesGroupWrapper, filesNotImgGroupWrapper, context=null) {

            let i=0;
            filesArr.forEach(file=>{

                i++;
                let isFileImg = file.original_filename.includes('.jpg') || file.original_filename.includes('.jpeg') || file.original_filename.includes('.png');
                let isFilePdf = file.original_filename.includes('.pdf')

                if(isFileImg) {
                    if(!context)
                        fileOnServerDivWrapper = $('<a>').attr('href', file.filename).addClass(context ? 'fileOnServerDivWrapper' : 'fileOnServerDivWrapperInfoTab')
                    else
                        fileOnServerDivWrapper = $('<div>').css({'cursor': 'pointer'}).addClass('fileOnServerDivWrapper')
                    fileSrc = file.filename
                } else if(!isFilePdf) {
                    fileOnServerDivWrapper = $('<div>').addClass(context ? 'fileOnServerDivWrapper' : 'fileOnServerDivWrapperInfoTab')
                    
                    if(file.original_filename.includes('.xls') || file.original_filename.includes('.xlsx'))
                    fileSrc = 'img/fileIcons/XLS_icon.jpg'

                    if(file.original_filename.includes('.doc') || file.original_filename.includes('.docx'))
                    fileSrc = 'img/fileIcons/WORD_icon.jpg'
                } else if(isFilePdf) {
                    fileSrc = file.filename
                    fileOnServerDivWrapper = $('<div>').addClass(context ? 'fileOnServerDivWrapper' : 'fileOnServerDivWrapperInfoTab')
                }

                $(fileOnServerDivWrapper).attr('id', 'fileId-' + file.id).addClass('attachmentFileWrapper')

                if(!isFilePdf) {
                    let fileImg = $('<img>').attr({'src': fileSrc}).css({'width': '100%', 'height': '100%', 'object-fit': 'cover', 'border-radius': '5px'})
                    if(isFileImg && context)
                        fileImg.addClass('fileImg')

                    $(fileOnServerDivWrapper).append(fileImg)
                    $('<div>').text(file.original_filename)
                        .attr('title', file.original_filename)
                        .addClass('attachmentFileName')
                        .appendTo(fileOnServerDivWrapper)
                } else {
                    fileOnServerDivWrapper.css({'position': 'relative', 'height': '170px'})
                    let fileImg = $('<iframe>')
                        .attr({'src': fileSrc}).css({ 'width': '100%', 'height': '120px', 'object-fit': 'cover', 'border-radius': '5px'})

                    let fakeCoverLayout = $('<div class="fakeCoverPDF">')
                        .attr({'src': fileSrc, 'data-iframe': true, 'data-src': fileSrc})
                        .addClass('fakeCoverPdfOnServerFilesTab')

                    $(fileOnServerDivWrapper).append(fileImg)
                    $(fileOnServerDivWrapper).append(fakeCoverLayout)
                    $('<div>').text(file.original_filename)
                        .attr('title', file.original_filename)
                        .addClass('attachmentFileName')
                        .appendTo(fileOnServerDivWrapper)
                }

                if(isFileImg) {
                    $(filesGroupWrapper).append(fileOnServerDivWrapper)
                } else {
                    $(filesNotImgGroupWrapper).append(fileOnServerDivWrapper)
                }

            })     
        }

</script>