<style>
    #fileUploaderNewFileButtonAnchorDiv,
    #newFilesListWrapper,
    #newFilesNotImgListWrapper {
        margin-top: 10px;
        width: 100%;
        overflow-y: auto;
    }
    .newFileDivCSS{
        border: 1px solid grey;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        width: 100px;
        height: 80px;
        border-radius: 5px;
        float: left;
        margin-right: 10px;
        margin-bottom: 10px;
        position: relative;
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

</style>