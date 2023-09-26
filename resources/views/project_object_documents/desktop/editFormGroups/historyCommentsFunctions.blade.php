<script>

    function handleCommentsDataArr(commentsArr, itemElement) {
        
        if(commentsArr.length === 0) {
            $(itemElement).append('<span class="popup-field-nodata">Нет данных</span>')
            return
        }

        let itemElementContent = 
            '<div class="form-group-list-wrapper">'
                + '<div class="form-group-comments-list">'
                + '<table>'

        itemElementContent += getCommentsListHTML(commentsArr)
        itemElementContent += '</table></div></div>'
        itemElement.append(itemElementContent)
    }

    function getCommentsListHTML(commentsArr) {
        let itemsProcessed = 0;
        let itemElementContent = ''

        commentsArr.forEach(comment=>{

            itemsProcessed++;

            if (comment.author.image) {
                photoUrl = `{{ asset('storage/img/user_images/') }}` + '/' + comment.author.image;
            } else {
                photoUrl = `{{ mix('img/user-male-black-shape.png') }}`;
            }
            itemElementContent += 
                '<tr class="form-comments-list-elem" style="">'
                    +'<td style="padding-bottom:10px; padding-top: 10px"><div class="comments-list-user-photo">'
                        + '<img src="' + photoUrl + '" class="photo">'
                    +'</div></td>'
                    +'<td style="padding-left:20px;   padding-bottom:10px; padding-top: 10px; ">'
                        + '<div style="border-right: 1px dashed #dddddd; padding-right: 20px;">'
                            + '<div>' + comment.author.full_name + '</div>'
                            + '<div>' + new Date(comment.created_at).toLocaleString() + '</div>'
                        + '</div>'
                    +'</td>'
                    +'<td style="padding-left:20px;  padding-right: 20px; padding-bottom:10px; padding-top: 10px">'
                        + comment.comment
                    +'</td>'
                +'</tr>'


            // itemElementContent += 

            //         '<tr class="form-comments-list-elem">'
            //             + '<td><div class="comments-list-user-photo">'
            //                 + '<img src="' + photoUrl + '" class="photo">'
            //             + '</div></td>'
            //             + '<td style=""><div class="comment-date-fio-wrapper">'
            //                 + '<div>' + comment.author.full_name + '</div>'
            //                 + '<div>' + new Date(comment.created_at).toLocaleString() + '</div>'
            //             + '</div></td>'
            //             + '<td ><div class="group-list-comment-field">' + comment.comment + '</div></td>'
            //         +'</tr>'

            // itemElementContent += 

            //         '<div class="form-comments-list-elem" style="">'
            //             + '<div class="comments-list-user-photo">'
            //                 + '<img src="' + photoUrl + '" class="photo">'
            //             + '</div>'
            //             + '<div class="comment-date-fio-wrapper">'
            //                 + '<div>' + comment.author.full_name + '</div>'
            //                 + '<div>' + new Date(comment.created_at).toLocaleString() + '</div>'
            //             + '</div>'
            //             + '<div class="group-list-comment-field">' + comment.comment + '</div>'
            //         +'</div>'
                    
        })

        return itemElementContent
    }

    

</script>