$( function() {

    $('.file-collection').closest('form').submit(function(e){
        if($(this).data('blocked')) {
            e.preventDefault();
            return;
        }
        $(this).find('.file-item input[type=file]').remove();
    });
    $('.file-collection').on('click','.file-item',function (e) {
        if(e.target instanceof HTMLInputElement) return;
        $(this).children('input[type=file]').click();
    });
    $('.file-collection').on('click','.file-remove',function(e){
        e.stopPropagation();
        $(this).parent().fadeOut('fast',function(){$(this).remove();});
    });
    $('.file-collection').on('change','input[type=file]', function () {



        var fileName = this.value.match(/\\([^\\]+)$/)[1] || this.value;
        var extension = this.files[0].name.match(/\.([^\.]+)$/)[1];

        var liElem = $(this).parent();
        console.log(liElem);

        if($(this).parent().hasClass('new-file')) {

            var indexes = liElem.parent().children().map(function(i,li){ return +$(li).data('index');}).toArray();
            console.log(indexes);
            var index = Math.max.apply(null,indexes)+1;

            var elem = liElem.parent('.file-collection').parent().data('prototype').replace(/__name__/g, index);


            $(this).after('<input type="file"/>');

            liElem  = liElem.before(elem).prev().hide().fadeIn('slow'); // switch to new LI

            liElem.append(this);
            liElem.find('.file-name').text(fileName).val(fileName);
            liElem.find('.file-icon')
                .removeClass()
                .addClass('file-icon file-' + extension);
            index++;
        }
        else{

            liElem.find('.file-name').text(fileName);
            liElem.find('.file-icon')
                .removeClass()
                .addClass('file-icon file-' + extension);
        }

        var data = new FormData();
        data.append('file',this.files[0]);

        var waitAnimation = {
            start: function(iconElem){
                iconElem.data('animating',true);
                iconElem.data('startTime', null);
                this.redraw(iconElem, performance.now());
            },
            redraw: function redraw(iconElem, time){
                iconElem.css({'transform': 'rotate('+((time - iconElem.data('startTime'))>>1)+'deg)'});
                if(iconElem.data('animating'))
                    animationFrame = requestAnimationFrame(redraw.bind(null,iconElem));
            },
            stop: function(iconElem){
                iconElem.data('animating',false);
            }
        };

        $.ajax({
            url: '/file/upload',
            processData: false,
            data: data,
            dataType: 'json',
            contentType: false,
            method: 'post',
            beforeSend: function(){
                liElem.removeAttr('title');
                liElem.removeClass('success error').addClass('wait')
                    .addClass('disabled').bind('click',preventClick);
                liElem.closest('form').data('blocked',true);
                waitAnimation.start(liElem.find('.wait'));
            },
            success: function(file){
                liElem.addClass('success');
                liElem.find('.file-id').val(file.data.id);

            },
            error: function(){
                liElem.attr('title','Не удалось загрузить');
                liElem.addClass('error');
                liElem.find('input').val('');
            },
            complete: function(){
                liElem.removeClass('disabled wait').unbind('click', preventClick);
                liElem.closest('form').data('blocked',false);
                waitAnimation.stop(liElem.find('.wait'));

            }


        });
        function preventClick(e){
            e.stopPropagation();
        }




    });

});
