<script>
    const uploadingFiles = [];
    const newFileDivCSS = {}
    const fileOnServerDivCSS = {}
    const progressBarSettings = {
        min: 0,
        max: 100,
        width: '90%',
        showStatus: false,
        visible: true,
    }

    function handleFilesDataArr(filesArr, filesGroupWrapper, filesNotImgGroupWrapper, context = null) {
        let i = 0;
        filesArr.forEach(file => {
            i++;
            let isFileImg = file.mime.includes('image')
            let isFilePdf = file.mime.includes('pdf')

            if (isFileImg) {
                if (!context)
                    fileOnServerDivWrapper = $('<a>').attr('href', file.filename).addClass(context ? 'fileOnServerDivWrapper' : 'fileOnServerDivWrapperInfoTab')
                else
                    fileOnServerDivWrapper = $('<div>').css({
                        'cursor': 'pointer'
                    }).addClass('fileOnServerDivWrapper')
                fileSrc = file.filename
            } else {
                fileOnServerDivWrapper = $('<a>')
                    .attr({
                        'href': file.filename,
                        'target': '_blanc'
                    })
                    .addClass(context ? 'fileOnServerDivWrapper' : 'fileOnServerDivWrapperInfoTab')

                if (file.original_filename.includes('.xls') || file.original_filename.includes('.xlsx'))
                    fileSrc = 'img/fileIcons/xls-icon.png'

                if (file.original_filename.includes('.doc') || file.original_filename.includes('.docx'))
                    fileSrc = 'img/fileIcons/doc-icon.png'

                if (file.original_filename.includes('.pdf'))
                    fileSrc = 'img/fileIcons/pdf-icon.png'
            }

            $(fileOnServerDivWrapper).attr('id', 'fileId-' + file.id).addClass('attachmentFileWrapper')

            let fileImg = $('<img>').attr({
                'src': fileSrc
            })

            if (isFileImg) {
                fileImg.css('object-fit', 'cover')

                if (context)
                    fileImg.addClass('fileImg')
            } else {
                fileImg.css('object-fit', 'scale-down')
            }

            if (context)
                fileImg.addClass('file-files-tab')
            else
                fileImg.addClass('file-info-tab')

            $(fileOnServerDivWrapper).append(fileImg)
            $('<div>').text(file.original_filename)
                .attr('title', file.original_filename)
                .addClass('attachmentFileName')
                .appendTo(fileOnServerDivWrapper)

            if (isFileImg) {
                $(filesGroupWrapper).append(fileOnServerDivWrapper)
            } else {
                $(filesNotImgGroupWrapper).append(fileOnServerDivWrapper)
            }
        })
    }
</script>
