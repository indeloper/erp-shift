<style>
    /* #popupContainer {
        width: 100%; 
        height: 80vh;
    } */

    #containerScrollableWrapper {
        /* height: 80vh; */
        overflow-y: scroll;
    }

    #newDocumentButtonMobile {
        margin: 10px 0;
    }

    .documentsListElemWrapper {
        display: flex;
        flex-direction: column;
        padding: 10px;
    }

    .documentElemMobile {
        font-size: 16px;
        margin: 10px 0;
        line-height: 1.5;
        padding: 6px 10px;
        background: white;
        border-radius: 10px;
        box-shadow: 0px 0px 5px 1px #ededed;
    }

    .files-group-header {
        padding: 6px 10px;
        font-weight: bolder;
        margin-bottom: 10px;
        background: #e6e6ed;
        width: 100%;
    }

    .mobile-new-document-form-element {
        width: 100%;
        margin-bottom: 20px;
    }

    .round-color-marker {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 5px;
    }


    .dx-popup-bottom.dx-toolbar {
        padding: 0 !important;
        padding-bottom: 5px !important;
        padding-top: 20px !important;
    }

    .mt-18px {
        margin-top: -18px;
    }

    .dx-popup-wrapper>.dx-overlay-content {
        background: #f7f7f8 !important;
    }

    .comments-list-user-photo {
        position: relative;
        border: 1px solid #E0E0E0;
        width: 40px;
        height: 40px;
        border-radius: 90px;
        display: inline-block;
        overflow: hidden;
    }

    .photo {
        width: 100%;
        border-radius: 10px;
        display: block;
        margin: auto;
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
    }

    .comment-author-date-info {
        padding-left: 20px;
        padding-bottom: 10px;
        padding-top: 10px;
    }

    /* FILES */

    .fileOnServerDivWrapper,
    .fileOnServerDivWrapperInfoTab {
        position: relative;
        width: 150px;
        height: auto;
        border-radius: 5px;
        /* display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            float: left; */
        /* margin-right: 20px; */
        /* margin-bottom: 10px; */
        /* padding: 10px; */
        display: flow;
    }

    .attachmentFileWrapper {
        margin-bottom: 10px;
    }

    .filesGroupWrapperClass {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        flex-direction: row;
        width: 100%;
        padding: 0 20px;
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
        /* height: 100px;  */
        height: auto;
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

    .fileOnServerDivWrapper:hover img {
        filter: brightness(0.5);
    }

    .newFileImgWrapper:hover img {
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
</style>