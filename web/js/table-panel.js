/**
 * Created by Aspire on 10.10.2017.
 */


function tablePanel($) {
    /* row actions menu positioning */

    function placeActionsList(list, panel, button) {
        list.css('left', button.offset().left - panel.offset().left);
        list.css('top', button.height() + button.offset().top - panel.offset().top)
    }

    function updateGroupActionsMenuAvailability() {

        var actions = [];
        var tds = $(".table-panel .panel-body input.selector-single:checked").parent('td');
        tds.first()
            .next('.actions')
            .find('a.action')
            .each(function(index,elem){
                actions.push(elem.dataset.action);
            });

        tds.slice(1).each(function(index, td){
            var entryActions = [];
            $(td).next('.actions').find('a.action').each(function(index,elem){
                entryActions.push(elem.dataset.action);
            });
            actions = actions.filter(function(action){
                return entryActions.includes(action);
            });
        });



        $('.table-panel .group-actions .dropdown-menu a.action').each(function(index,elem){
            var li = $(elem).parent('li');
            if(actions.includes(elem.dataset.action)) li.removeClass('disabled');
            else li.addClass('disabled');
        });

    }

    var filtering = false, filteringPended = false;
    function filterTableEntries() {
        if(filtering) {
            filteringPended = true;
            return;
        }
        filtering = true;

        var filter = $('.table-panel .panel-body .table th.field-header input')
            .map(function (index, elem) {
                return {
                    paramName: $(this).attr('name'),
                    value: $(this).val()
                }
            })
            .filter(function (index, fieldFilter) {
                return fieldFilter.value.length > 0
            })
            .toArray();


        var ajaxUriParams = {};
        var currentUriParams = window.location.search.replace('?', '').split('&');
        currentUriParams.forEach(function (param) {
            param = param.split('=');
            paramName = decodeURIComponent(param[0]);
            if (paramName == '') return;
            if (paramName == 'page') return;
            ajaxUriParams[paramName] = decodeURIComponent(param[1]);
        });
        filter.forEach(function (fieldFilter) {
            if (!fieldFilter.value) return;
            ajaxUriParams[fieldFilter.paramName] = fieldFilter.value;
        });


        var uriParams = [];
        Object.getOwnPropertyNames(ajaxUriParams).forEach(function (paramName) {
            uriParams.push(paramName + '=' + ajaxUriParams[paramName]);
        });
        uriParams.push('table-only');

        uriParams = '?' + uriParams.join('&');
        uriParams = encodeURI(uriParams);


        var uri = window.location.pathname + uriParams;


        $.ajax({
            url: uri,
            success: function(data){
                data = $(data);

                $('.table-panel > .panel-body tbody').replaceWith(data.find('.panel-body tbody'));
                $('.table-panel > .panel-footer').replaceWith(data.children('.panel-footer'));
            },
            error: function(jqxHR, status, error){
                console.log(error);
            },
            complete: function(){
                filtering=false;
                if(filteringPended){
                    setTimeout(function(){
                        filteringPended=false;
                        filterTableEntries();
                    }, 100);
                }
            }
        });


    }

    var delayTimer = undefined;

    function runDelayTimer(callback) {

        if (delayTimer !== undefined)
            clearTimeout(delayTimer);
        delayTimer = setTimeout(function () {
            delayTimer = undefined;
            callback();
        }, 500);
    }

    tablePanel.getGroupItems  = function() {
        return $(".table-panel .panel-body input.selector-single:checked")
            .map(function (i, elem) {
                return {
                    id: elem.dataset.id,
                    name: elem.dataset.name
                }
            })
            .toArray();
    };

    tablePanel.loadModalContent = function(uri, modalId) {
        $(modalId+'.modal-header .modal-title').empty();
        $(modalId+' .modal-body').empty();
        $.ajax({
            url: uri,
            success: function(data){
                data = $(data);
                $(modalId+' .modal-header .modal-title').html(data.find('.title').html());
                $(modalId+' .modal-body').html(data.find('.items-list'));
                $(modalId+' .modal-body').append(data.find('form').attr('action',uri));
                $(modalId+' .modal-body form .cancel').removeAttr('onclick').attr('data-dismiss','modal');
                window.initWysiwygElements(modalId+' .modal-body form.wysiwyg-container textarea');
            },
            error: function(jqxHR, status, error){
                console.log(error);
            }
        });

    };


    function activatePanel(){
        $('.table-panel').on('click', '.panel-body .dropdown.actions > a', function (event) {
            placeActionsList($(this).next('ul'), $(this).closest('.panel'), $(this));
        });
        $('.table-panel').on('click', '.panel-body .dropdown.actions a.action[data-toggle=modal]', function (event) {
            event.preventDefault();
            var uri = this.dataset.uri;

            if($(this).closest('.actions').hasClass('group')) {
                var ids = tablePanel.getGroupItems().map(function (item) {
                    return item.id;
                }).join('&');
                uri += ids;

            }
            uri+="?form-only&back_uri="+encodeURIComponent(window.location.href);
            tablePanel.loadModalContent(uri, '#table-panel-modal');

        });

        $('.table-panel').on('scroll', '.panel-body' , function (event) {
            $('table td:first-child .dropdown.open').removeClass('open');
        });


        /* disable pagination if there is only one page */

        $('.table-panel').on('click', '.panel-footer .pagination a.disabled' , function () {
            return false;
        });

        /*  group selector checkboxes*/

        $(".table-panel").on('click', '.panel-body input.selector-all' , function () {

            if (this.checked) $(".table-panel .panel-body input.selector").prop('checked', true);
            else  $(".table-panel .panel-body input.selector").prop('checked', false);
            updateGroupActionsMenuAvailability();
        });
        var lastClickedSingleSelector = null;
        $(".table-panel").on('click', '.panel-body input.selector-single' , function (event) {
            $(".table-panel .panel-body input.selector-all").prop('checked', false);
            if(lastClickedSingleSelector && event.shiftKey){
                var selectors = $('.table-panel .panel-body input.selector-single');
                var from = selectors.index(lastClickedSingleSelector);
                var to = selectors.index(this);
                if(from > to) { var tmp = from; from = to; to = tmp; }
                for(i = from+1; i < to ; i++){
                    var selector = selectors.get(i);
                    selector.checked = !selector.checked;
                }
            }
            lastClickedSingleSelector = this;
            updateGroupActionsMenuAvailability();
        });

        updateGroupActionsMenuAvailability();

        /* show and hide filter inputs */

        $(".table-panel").on('click', '.panel-heading .filter-switcher' , function () {

            $(".table-panel .panel-body th").toggleClass('filtering');

        });

        $(".table-panel").on('keyup change', '.panel-body th .filter input' , function () {
            if ($(this).data('prev_value') == this.value) return;
            $(this).data('prev_value', this.value);
            runDelayTimer(filterTableEntries);


        });

        $(".table-panel").on('click', ' .panel-body th .filter .cleaner' , function (e) {
            $(this).parent('.input-group-btn').prev('input').val('').change();
        });
    }
    activatePanel();







}
tablePanel(jQuery);





