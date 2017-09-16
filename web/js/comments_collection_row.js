$( function() {

    $('.comment-collection .submit').click(function(){


        var commentContainer = $(this).closest('.comment-container');
        var template = commentContainer.find('template')[0].content.cloneNode(true);

        var content = commentContainer.find('.new-comment').tinymce().getContent();
        var author = commentContainer.data('username');
        var locale = commentContainer.data('locale');
        var date = new Date();

        var index = +commentContainer.data('comment-count');
        console.log(index);
        commentContainer.data('comment-count',index+1);
        var formData = commentContainer.data('prototype').replace(/__name__/g,index);



        template.querySelector('.comment-name').textContent = author;
        template.querySelector('.comment-time').textContent = date.toLocaleString(locale);
        $(template.querySelectorAll('.comment-content')).html(content);
        $(template.querySelectorAll('.comment-form-data')).html(formData);
        formData = $(template.querySelectorAll('.comment-form-data')).children().first();
        formData.find('.content').html(content);
        formData.find('.datetime_year').text(date.getFullYear());
        formData.find('.datetime_month').text(date.getMonth()+1); // Date.getMonth() =>  [ 0 ... 11 ]
        formData.find('.datetime_day').text(date.getDate());
        formData.find('.datetime_hour').text(date.getHours());
        formData.find('.datetime_minute').text(date.getMinutes());


        var parent = commentContainer.data('parentForNewComment');
        parent = parent ? parent : commentContainer.find('.panel-body > .comments-list');
        parent.append(template);

        commentContainer.find('.new-comment').text('');




    });
    $('.comment-collection .clear').click(function(){
        var commentContainer = $(this).closest('.comment-container');
        commentContainer.find('.new-comment').text('');
    })

});
