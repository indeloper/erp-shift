<style>

    #downloadFilesButton {
        margin-left: 10px;
    }

    #newFilesListWrapper,
    #newFilesNotImgListWrapper,
    #filesOnServerListWrapper
    {
        margin-top: 10px;
        width: 100%;
        overflow-y: auto;
        max-height: 40vh;
    }
    /* .newFileDivCSS{
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
    } */

    .newFileDivWrapper,
    .fileOnServerDivWrapper{
        border-radius: 5px;
        float: left;
        margin-right: 10px;
        margin-bottom: 10px;
        position: relative;
        max-width: 100px;
    }

    .newFileDivWrapper {
        width: 100px;
        height: 80px;
        border: 1px solid #e1dede;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
    }

    .newFileImg{
        width: 100%;
        object-fit: cover;
        border-radius: 10px;
    }

    .newAttachmentIcon {
        width: 100%;
        object-fit: cover;
        border-radius: 10px;
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

    .attacmentHoverCkeckbox {
        position: absolute;
        top: 2px;
        left: 2px;
    }

    .attacmentHoverDeleteButton {
        position: absolute;
        top: 2px;
        right: 2px;
        border-radius: 2px !important;
    }

    .attacmentNewHoverDeleteButton {
        right: 2px !important;
    }

    .attacmentHoverDeleteButton .dx-icon {
        color: #337ab7 !important;
        font-size: 16px !important;
    }

    .attacmentHoverDeleteButton .dx-button-content {
        width: 22px !important;
        height: 22px !important;
    }

    
</style>