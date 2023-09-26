@include('project_object_documents.desktop.editFormGroups.historyCommentsFunctions')

<script>
    const historyCommentsTabbedGroup =  
                        {
                            tabTemplate (data, index, element){
                                return '<div style="display: flex; align-items:center"><span class="fa fa-comment comment-icon-color"  style="padding-top: 1px;"></span><span style="margin-left:6px">История</span></div>'
                            },
    
                            items: [
                                {
                                    itemType: 'simple',
                                    template: (data, itemElement) => {
                                        itemElement.attr('id', 'commentsWrapperHistoryTab').css('height', '45vh')
                                        itemElement.append('<div class="form-group-list-header">Комментарии:</div>')
                                        itemElement.append('<div id="newAddedComments" style="color:#829be3"></div>')
                                        if(projectObjectDocumentInfoByID.items()[0])
                                        handleCommentsDataArr(projectObjectDocumentInfoByID.items()[0]?.comments.original, itemElement) 
                                        else 
                                        $(itemElement).append('<span class="popup-field-nodata">Нет данных</span>')
                                    }
                                },

                                {
                                    // dataField: "new_comment",
                                    // label: {
                                    //     text: "Новый комментарий",
                                    // },
                                    itemType: 'simple',
                                    template: (data, itemElement) => {
                                        const newCommentElemsWrapper = $('<div id="newCommentElemsWrapper">')
                                        const newCommentFieldWrapper = $('<div id="newCommentFieldWrapper">').appendTo(newCommentElemsWrapper)
                                        $('<div id="newCommentTextBox">').dxTextBox({
                                            onEnterKey: (e) => {
                                                if(!e.component.option('value'))
                                                return;
                                                handleNewCommentAdded(e.component.option('value'), e.component)
                                            },
                                            buttons: [{
                                                name: 'newComment',
                                                location: 'after',
                                                options: {
                                                    icon: 'fa fa-share-square',
                                                    type: 'default',
                                                    onClick() {
                                                        const newCommentTextBox = $('#newCommentTextBox').dxTextBox('instance');   
                                                        if(!newCommentTextBox.option('value'))
                                                        return;
                                                        handleNewCommentAdded(newCommentTextBox.option('value'), newCommentTextBox)                                                     
                                                    },
                                                },
                                            }]
                                        }).appendTo(newCommentFieldWrapper)
                                        
                                        // $('<div>')
                                        //     .dxButton({
                                        //         text: "Новый комментарий",
                                        //         icon: 'upload',
                                        //         onClick(){
                                        //             const newCommentTextBox = $('#newCommentTextBox').dxTextBox('instance')
                                        //             console.log('newCommentTextBox', newCommentTextBox);
                                        //             console.log("{{Auth::user()->user_full_name}}");
                                        //             console.log(projectObjectDocumentInfoByID.items()[0]?.comments.original);
                                        //         }
                                        //     }).appendTo(newCommentElemsWrapper);

                                        newCommentElemsWrapper.appendTo(itemElement)

                                    }
                                },
                            ]
                        }
</script>