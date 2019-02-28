;(function ($) {
    'use strict';

    /**
     * Store and retrieve active tab, for all nav-tabs, using web browser's local storage.
     *
     * @link https://www.tutorialrepublic.com/faq/how-to-keep-the-current-tab-active-on-page-reload-in-bootstrap.php
     */

    var storage = new QoboStorage({
        engine: 'local'
    });

    var prefix = 'activeTab_';
    // store active tab for each navtab
    $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
        var navId = $(e.target).parents('.nav-tabs').attr('id');

        if (! navId) {
            return;
        }

        storage.write(prefix + navId, $(e.target).closest('li').index());
    });

    // load active tab for each navtab
    $('.nav-tabs').each(function (key, value) {
        var navId = $(value).attr('id');

        if (! navId) {
            return;
        }

        if (! storage.read(prefix + navId)) {
            return;
        }

        $('#' + navId + ' li:eq(' + storage.read(prefix + navId) + ') a').tab('show');
    });

    /**
     * Dynamic tab loading for system info
     */
    $('.system-info li').each(function (key,element) {
        if ($(element).hasClass('active')) {
            var link = $(element).find('a');
            getSystemInfo(link);
        }
    });

    $('.system-info a').click(function () {
        getSystemInfo($(this));
    });

    function getSystemInfo(element)
    {
        var destination = $(element).data('tab');

        $.ajax({
            url: '/api/v1.0/system/info',
            method: 'POST',
            dataType: 'html',
            data: { tab: $(element).data('tab') },
            headers: {
                Authorization: 'Bearer ' + api_token
            },
            beforeSend: function (xhr) {
                $('#' + destination).empty();
                $('#spinner-system-info').show();
            }
        }).then(function (response) {
            $('#spinner-system-info').hide();
            $('#' + destination).empty();
            $('#' + destination).append(response);
        });
    }

    $(window).resize(function() {
        responsiveTabs()
    });

    responsiveTabs();

    /**
     * Get any responsive-tabs and check for overflow list-items in order to
     * a new dropdown menu with the ones that are overflowing the list
     */
    function responsiveTabs(){
        $('.responsive-tabs').each(function(){
            var parentUl = $(this)
            var ulWidth = parentUl.width() - 45;
            var hideTabs = false;
            //Get the total width of all list items
            var liTotalWidth = totalLiWidth(parentUl);

            //Get the list items in reverse order
            $(parentUl.find('li:not(.dropdown-li):not(.dropdown)').get().reverse()).each(function(){
                //Get the margins size
                var marginLeft = parseInt($(this).css("margin-left"))
                var marginRight = parseInt($(this).css("margin-right"))
                //list item width including the margins size
                var liWidth = $(this).width()+marginLeft+marginRight;

                //Checks if the list item total width is larger than the ul width
                if (liTotalWidth > ulWidth) {
                    hideTabs = true;
                    //Checks if the dropdown already exist else create it
                    if (!parentUl.find('.dropdown').length) {
                        var dropdownString = '<li class="dropdown pull-right" style="width:45px">' +
                                '<a class="dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="true">' +
                                    '<i class="fa fa-bars"></i>'+
                                '</a>' +
                                '<ul class="dropdown-menu pull-right"></ul>' +
                            '</li>';
                        parentUl.append(dropdownString)
                    }
                    //Move the item under the dropdown list
                    parentUl.find('.dropdown-menu').prepend($(this).addClass('dropdown-li'));

                    liTotalWidth = liTotalWidth-liWidth;
                }else{
                    return false;
                }
            });

            //Check if the hideTabs is false
            if (!hideTabs) {
                //Get the liTotalWidth again
                var liTotalWidth = totalLiWidth(parentUl);

                //Get all the list items from the dropdown
                parentUl.find('.dropdown-menu li').each(function(){
                    liTotalWidth = liTotalWidth+realListItemWidth($(this));
                    if(liTotalWidth < ulWidth){
                        parentUl.find('.dropdown').before($(this).removeClass('dropdown-li'))
                    }else{
                        return false;
                    }
                });
            }

            // Check if the dropdown-menu has items else delete it
            if (!parentUl.find('.dropdown-menu li').length ){
                parentUl.find('.dropdown').remove();
            }
        })
    }

    /**
     * Get total list items width
     *
     * @param {object} parentUl The parent ul
     * @return {float} Return the total width
     */
    function totalLiWidth(parentUl){
        // var liTotalWidth = 80;
        var liTotalWidth = 0;
        parentUl.find('li:not(.dropdown-li)').each(function(){
            var marginLeft = parseInt($(this).css("margin-left"))
            var marginRight = parseInt($(this).css("margin-right"))
            liTotalWidth = liTotalWidth+$(this).width()+marginLeft+marginRight;
        })

        return liTotalWidth;
    }

    /**
     * Get Real width of a hidden list item, in order to achieve that we have to
     * create it with hidden visibility
     *
     * @param {object} obj The object
     * @return {float} Return the width of the list item
     */
    function realListItemWidth(obj){
        //Check if the hidden nav ul already exist
        if (!$('#hidden-nav-ul').length) {
            var ulString = $('<ul id="hidden-nav-ul" class="nav nav-tabs" role="tablist" stule="visibility:hidden"><ul>');
            $('body').append(ulString)
        }

        var clone = obj.clone();
        clone.css("visibility","hidden");
        $('#hidden-nav-ul').append(clone);
        var width = clone.outerWidth();
        clone.remove();
        return width;
    }
})(jQuery);


/**
 * Prevent multiple form submition.
 */
jQuery.fn.preventDoubleSubmission = function () {
    $(this).on('submit',function (e) {
        var $form = $(this);

        if ($form.data('submitted') === true) {
            // Previously submitted - don't submit again.
            e.preventDefault();
        } else {
            // Mark it so that the next submit can be ignored.
            $form.data('submitted', true);
        }
    });

    return this;
};
$(document).ready(function () {
    $('form').preventDoubleSubmission();
});


