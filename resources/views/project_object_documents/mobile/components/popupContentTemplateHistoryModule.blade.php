<script>
    const renderHistoryTemplate = function() {
        // $('.dx-toolbar-center .dx-item-content').html('<div>История</div>') 
        const container = $('#popupContainer')
        container.html('')
        const containerScrollableWrapper = $('<div id="containerScrollableWrapper">').appendTo(container)
        
        popupLoadPanel.option('visible', true)
        projectObjectDocumentInfoByID.reload().done(()=>{

            popupLoadPanel.option('visible', false)
            let comments = projectObjectDocumentInfoByID.items()[0]?.comments.original

            if(!comments.length) {
                containerScrollableWrapper.append('<div class="documentElemMobile"><span class="popup-field-nodata">Нет данных</span></div>')
                return
            }
            
            comments.forEach(comment=>renderCommentMobile(comment, containerScrollableWrapper))
        })
        
    }

    const renderCommentMobile = (comment, container) => {
        if (comment.author.image) {
            photoUrl = `{{ asset('storage/img/user_images/') }}` + '/' + comment.author.image;
        } else {
            photoUrl = `{{ mix('img/user-male-black-shape.png') }}`;
        }

        $('<div>')
            .addClass('documentElemMobile')
            .append(`
                <div style="display:flex; align-items: center;">
                    <div class="comments-list-user-photo">
                        <img src="${photoUrl}" class="photo">
                    </div>
                    <div class="comment-author-date-info">
                        <div >${comment.author.full_name}</div>
                        <div>${new Date(comment.created_at).toLocaleString()}</div>
                    </div>
                </div>
                <div>${comment.comment}</div>
            `)
            .appendTo(container)
    }
</script>