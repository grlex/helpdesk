/**
 * Created by Aspire on 24.10.2017.
 */
(function($){


    function initTrumbowygTextarea(textarea){
        textarea.attr('placeholder', trumbowygConfig.placeholder);
        textarea.trumbowyg(trumbowygConfig);
        textarea.parent().css('min-height','100');
        textarea.height(100);

        var editor = textarea.prev('.trumbowyg-editor');
        initMagnificPopupGallery(editor);

        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {

                if(mutation.addedNodes.length && mutation.addedNodes[0].tagName=='IMG'){
                    observer.disconnect();
                    var img = $(mutation.addedNodes[0]);
                    //img.css({ 'max-width': '280px', 'max-height': '280px' });
                    var originalSrc = img.data('original') ? img.data('original') : img.prop('src');
                    var linkWrapper = '<a href="__src__" class="gallery-image"></a>';
                    linkWrapper = linkWrapper.replace('__src__',originalSrc);
                    img.wrap(linkWrapper);
                    observer.observe(editor[0], config);
                    textarea.data('trumbowyg').syncTextarea();
                    return;
                }
            });
        });

        var config = {  childList: true, subtree: true };
        observer.observe(editor[0], config);

    }

    function initMagnificPopupGallery(container){

        if(container.hasClass('mfp')) return;
        container.magnificPopup({
            delegate: 'a.gallery-image',
            type: 'image',
            gallery: {
                enabled:true
            }
        });
        container.addClass('mfp');

    }


    function fosCommentEvents(){

        function initCommentsMagnificPopup(){
            $(fos_comment_container_selector+' .fos_comment_comment_show .fos_comment_comment_body:not(.mfp)').each(function(){
                initMagnificPopupGallery($(this));
            });
        }

        var newCommentTextarea, countElem, count;

        $(document).on('fos_comment_load_thread', '#fos_comment_thread', function (event, id) {

            countElem = $(this).children('h3').children('.comment_count').children('.count');
            count = +countElem.text();
            newCommentTextarea = $(this).find('.fos_comment_comment_new_form').find('textarea');

            if(newCommentTextarea.length)
                initTrumbowygTextarea(newCommentTextarea);

            initCommentsMagnificPopup();
        });

        $(document).on('fos_comment_show_form', '#fos_comment_thread', function (event, data) {
            var textarea = $(this).find('.fos_comment_comment_new_form textarea:not(.trumbowyg-textarea)');
            initTrumbowygTextarea(textarea);
        });

        $(document).on('fos_comment_show_edit_form', '#fos_comment_thread', function (event, data) {
            var textarea = $(this).find('.fos_comment_comment_edit_form textarea:not(.trumbowyg-textarea)');
            initTrumbowygTextarea(textarea);
        });

        $(document).on('fos_comment_submitted_form', '#fos_comment_thread', function (event, statusCode) {
            if(statusCode!==200) return;

        });

        $(document).on('fos_comment_add_comment', '#fos_comment_thread', function (event) {
            countElem.fadeOut('fast',function(){
                countElem.text(++count);
                countElem.fadeIn('fast');
            });
            initCommentsMagnificPopup();
            newCommentTextarea.trumbowyg('empty');

        });
    }
    fosCommentEvents();

    window.initWysiwygElements = function(selector){
        $(selector).each(function(index, textarea){
            textarea = $(textarea);
            textarea.trumbowyg(trumbowygConfig);
            initTrumbowygTextarea(textarea);
            initMagnificPopupGallery(textarea);
        });
    };
    initWysiwygElements('.wysiwyg-element, .wysiwyg-container textarea');

    window.initGalleryElements = function(selector){
        $(selector).each(function(index, elem){
            initMagnificPopupGallery($(elem));
        });
    };
    initGalleryElements('.mfp-gallery');

    window.initChosenElements = function(selector){
        $(selector).chosen({
            allow_single_deselect: true,
            width: '100%'
        });
    };
    //initChosenElements('.chosen-element');


})(jQuery);
