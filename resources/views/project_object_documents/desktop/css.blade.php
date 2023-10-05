<style>

    /* Common */

        /* Панель подключаем для информации о фильтре в скачиваемом файле, но не показываем */
        .dx-datagrid-filter-panel {
            display: none !important;
        }

        .dx-placeholder::before {
            padding: 0 0 2px 5px !important;
        }


    /* Common */

    /* Popup */

        /* .dx-popup-content {
            padding-bottom: 0 !important;
        } */

        /* .dx-tabpanel{
            margin-top: 20px;
        } */

        /* .leftBorderCloseButton{
            border-right: 1px solid #d3c9c9;
            padding-right: 8px;
        } */

        /* file uploader */

            /* .dx-fileuploader-container{
                margin-top: -1.3em;
            }

            .dx-fileuploader-input {
                display: none;
                padding: 0;
            }

            .dx-fileuploader-show-file-list .dx-fileuploader-files-container {
                padding: 0px 10px;
            }

            .dx-fileuploader-files-container .dx-fileuploader-file-container {
                -webkit-box-shadow: 0;
                box-shadow: 0;
            }

            .dx-fileuploader-show-file-list .dx-fileuploader-files-container{
                padding: 7px 3px 0;
            }

            .dx-fileuploader-input-wrapper::after{
                padding: 0;
            }

            .dx-layout-manager .dx-tabpanel .dx-multiview-item-content {
                padding: 0;
            }

            .dx-fileuploader-input-wrapper {
                padding: 0;
                border: 0;
            } */

        /* file uploader end */

        /* LIGHTGALLERY */

        .lg-backdrop {
            z-index: 1640 !important;
        }

        .lg-outer {
            z-index: 1650 !important;
        }

        /* FORM */
        .form-group-list-header {
            font-weight: bolder;
            font-size: 110%;

        }
        .form-group-comments-list {
            margin-top: 10px;
            margin-bottom: 10px;
            position: relative;
        }

        .form-group-comments-list .form-comments-list-elem:not(:last-child) {
            border-bottom: 1px solid #dddddd;
        }

        /* .form-comments-list-elem {
            display: flex;
        } */

        .comment-date-fio-wrapper {
            /* margin-left: 20px;
            margin-right: 20px; */
            border-right: 1px dashed #dddddd;
            /* padding-right: 20px; */

        }

        /* .form-comments-list-elem{
            padding: 10px 0;
        } */

        .comments-list-user-photo {
            position: relative;
            border: 1px solid #E0E0E0;
            width: 40px;
            height: 40px;
            border-radius: 90px;
            display: inline-block;
            overflow: hidden;
        }

        .comment-date-fio-wrapper {
            width: 100%;
        }

        #newCommentElemsWrapper {
            display: flex;
        }

        #newCommentFieldWrapper {
            width: 100%;
            margin-right: 10px;
        }

        .fileOnServerDivWrapper, .fileOnServerDivWrapperInfoTab {
            position: relative;
            width: 150px;
            height: auto;
            border-radius: 5px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            float: left;
            margin-right: 20px;
            margin-bottom: 20px;
            padding: 10px;
            display: flow;
        }

        .attachmentFileName {
            bottom: 0;
            padding-left: 6px;
            width: 100%;
            color: #8e8ebb;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .files-group-header {
            padding: 6px 10px;
            font-weight: bolder;
            margin-bottom: 10px;
            background: #e6e6ed;
            width: 100%;
            float: left;
        }

        .fakeCoverPDF {
            position: absolute;
            cursor: pointer;
            top: 0;
        }

        .fileOnServerDivWrapper:hover .fakeCoverPDF {
            background: black;
            opacity: 0.3;
        }

        .newFileDivCSS:hover .fakeCoverPDF {
            background: black;
            opacity: 0.3;
        }

        .fakeCoverPdfOnServerFilesTab {
            width: 130px;
            height: 120px;
            top: 10px;
        }

        .fakeCoverPdfOnNewFiles {
            width: 80px;
            height: 62px;
            top: 20px;
        }

        .newFileDivCSS {
            width: 100px;
            height: 100px;
            border: 1px solid grey;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            float: left;
            margin-right: 20px;
            margin-bottom: 20px;
            position: relative;
        }

        .file-files-tab{
            width: 150px;
            height: 130px;
            border-radius: 5px;
        }

        .file-info-tab{
            width: 75px;
            height: 65px;
            border-radius: 5px;
        }

        .fileOnServerDivWrapper:hover img  {
            filter: brightness(0.5);
        }

        .newFileImgWrapper:hover img  {
            filter: brightness(0.5);
        }

        .attacmentHoverCheckbox {
            position: absolute;
            top: 12px;
            left: 12px;
        }

        .attachmentHoverDeleteButton {
            position: absolute;
            top: 12px;
            right: 12px;
            border-radius: 2px !important;
        }

        .attacmentNewHoverDeleteButton {
            right: 2px !important;
        }

        .attachmentHoverDeleteButton .dx-icon {
            color: #337ab7 !important;
            font-size: 16px !important;
        }

        .attachmentHoverDeleteButton .dx-button-content {
            width: 22px !important;
            height: 22px !important;
        }

        /* ICONS */

        .info-circle-icon-color {
            color: #725fdb;
        }

        .comment-icon-color {
            color: #1b91d7;
        }

        .popup-field-nodata {
            color: #999;
            font-size: 17px;
        }

    /* Popup end */

        .dx-loadpanel-wrapper {
            z-index: 1800 !important;
        }

    /* Main */

        #container{
            background: white;
            padding: 5px;
            padding: 20px;
        }

        #headerRow, #headerToolbarWrapper{
            display: flex;
        }

        #headerRow{
            justify-content: space-between;
        }

        #headerToolbarWrapper{
            width: 100%;
            justify-content: end;
        }

        #headerToolbarWrapper > .dx-button {
            margin-bottom: 1px;
            margin-right: 1px;
        }

        .headerToolbarItem {
            /* height: 29px; */
            margin-left: 4px;
        }

        /* #dataGridContainer {
            padding-top: 15px;
            margin-top: 12px;
            border-top: 1px solid #ddd;
            padding-bottom: 10px;
        } */

        .dxTagBoxItem{
            width: 250px;
        }

        .headerToolbarItem-wrapper {
            display: flex;
            align-items: center;
            padding: 0;
            margin: 0;
        }

        .main-filter-label {
            line-height: 33px;
            font-weight: bold;
            padding-left: 14px;
            font-size: 12px;
        }

        .dx-tag-content{
            padding: 1px 32px 0px 5px !important;
        }

        #gridHeader{
            /* font-size: 110%;
            font-weight: bolder;
            color: #6d849b; */
            font-size: 16px;
            font-family: "Helvetica Neue","Segoe UI",helvetica,verdana,sans-serif;
            color: #333;
            font-weight: 400;
            display: flex;
            align-items: center;
            padding-bottom: 4px;
        }

        .round-color-marker {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 5px;
        }

        @media screen and (max-width: 1200px) {

            #headerRow, #headerToolbarWrapper{
                flex-direction: column;
                align-items: start;
            }

            .headerToolbarItem{
                margin-left: 0;
                margin-bottom: 10px;
                width: 300px;
            }

            .headerToolbarItem-wrapper {
                flex-direction: column;
                margin-top: 10px;
                align-items: baseline;
            }

            .main-filter-label {
                padding-left: 0;
            }
        }

    /* Main end */

</style>
