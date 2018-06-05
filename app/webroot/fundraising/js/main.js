/* Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */

(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery', 'mooBehavior', 'mooFileUploader', 'mooAjax', 'mooOverlay', 'mooAlert', 'mooPhrase', 'mooGlobal',
            'tinyMCE', 'mooUser','picker_date'], factory);
    } else if (typeof exports === 'object') {
        // Node, CommonJS-like
        module.exports = factory(require('jquery'));
    } else {
        // Browser globals (root is window)
        root.mooFundraising = factory();
    }
}(this, function ($, mooBehavior, mooFileUploader, mooAjax, mooOverlay, mooAlert, mooPhrase, mooGlobal, tinyMCE, mooUser) {

    var initOnCreate = function () {
        $('#saveBtn').unbind('click');
        $('#saveBtn').click(function () {
            $(this).addClass('disabled');
            if (tinyMCE.activeEditor !== null) {
                $('#editor').val(tinyMCE.activeEditor.getContent());
            }
            mooBehavior.createItem('fundraisings', true);
        });

        var uploader = new mooFileUploader.fineUploader({
            element: $('#attachments_upload')[0],
            autoUpload: false,
            text: {
                uploadButton: '<div class="upload-section"><i class="fa fa-file-text-o"></i>' + mooPhrase.__('drag_photo') +' </div>'
            },
            validation: {
                allowedExtensions: mooConfig.photoExt,
                sizeLimit: mooConfig.sizeLimit
            },
            request: {
                endpoint: mooConfig.url.base + "/fundraising/fundraising_upload/attachments/" + $('#plugin_campaign_id').val()
            },
            callbacks: {
                onError: mooGlobal.errorHandler,
                onComplete: function(id, fileName, response) {
                    if(response.thumb){
                        $('#campaign_photo_ids').val($('#campaign_photo_ids').val() + ',' + response.photo_id);
                        tinyMCE.activeEditor.insertContent('<p align="center"><a href="' + response.large + '" class="attached-image"><img src="' + response.thumb + '"></a></p><br>');
                    }
                }
            }
        });

        $('#triggerUpload').click(function() {
            uploader.uploadStoredFiles();
        });

        var uploader1 = new mooFileUploader.fineUploader({
            element: $('#campaign_thumnail')[0],
            multiple: false,
            text: {
                uploadButton: '<div class="upload-section"><i class="material-icons">photo_camera</i>' + mooPhrase.__('drag_or_click_here_to_upload_photo') + '</div>'
            },
            validation: {
                allowedExtensions: mooConfig.photoExt,
                sizeLimit: mooConfig.sizeLimit
            },
            request: {
                endpoint: mooConfig.url.base + "/fundraising/fundraising_upload/avatar"
            },
            callbacks: {
                onError: mooGlobal.errorHandler,
                onComplete: function(id, fileName, response) {
                    $('#campaign_thumnail_preview > img').attr('src', response.thumb);
                    $('#campaign_thumnail_preview > img').show();
                    $('#thumbnail').val(response.file_path);
                }
            }
        });

        // bind action to button delete
        deleteCampaign();

        // toggleUploader
        $('#toggleUploader').unbind('click');
        $('#toggleUploader').on('click', function(){
            $('#images-uploader').slideToggle();
        });

        $(".datepicker").pickadate({
            monthsFull: [mooPhrase.__('january'), mooPhrase.__('february'), mooPhrase.__('march'), mooPhrase.__('april'), mooPhrase.__('may'), mooPhrase.__('june'), mooPhrase.__('july'), mooPhrase.__('august'), mooPhrase.__('september'), mooPhrase.__('october'), mooPhrase.__('november'), mooPhrase.__('december')],
            monthsShort: [mooPhrase.__('jan'), mooPhrase.__('feb'), mooPhrase.__('mar'), mooPhrase.__('apr'), mooPhrase.__('may'), mooPhrase.__('jun'), mooPhrase.__('jul'), mooPhrase.__('aug'), mooPhrase.__('sep'), mooPhrase.__('oct'), mooPhrase.__('nov'), mooPhrase.__('dec')],
            weekdaysFull: [mooPhrase.__('sunday'), mooPhrase.__('monday'), mooPhrase.__('tuesday'), mooPhrase.__('wednesday'), mooPhrase.__('thursday'), mooPhrase.__('friday'), mooPhrase.__('saturday')],
            weekdaysShort: [mooPhrase.__('sun'), mooPhrase.__('mon'), mooPhrase.__('tue'), mooPhrase.__('wed'), mooPhrase.__('thu'), mooPhrase.__('fri'), mooPhrase.__('sat')],
            today: mooPhrase.__('today'),
            clear: mooPhrase.__('clear'),
            format: 'yyyy-mm-dd',
            close: false,
            onClose: function () {

            }
        });
    }

    var toggleUploader = function() {
        $('#images-uploader').slideToggle();
    }

    // app/Plugin/Topic/View/Topics/view.ctp
    var initOnView = function(){
        mooOverlay.registerImageOverlay();

        // bind action to button delete
        deleteTopic();
    }

    // app/Plugin/Topic/View/Elements/lists/topics_list.ctp
    var initOnListing = function(){
        mooBehavior.initMoreResults();

        // bind action to button delete
        deleteTopic();

        $('.likeItem').unbind('click');
        $('.likeItem').click(function(){

            var obj = $(this);

            var data = $(this).data();

            var type = data.type;
            var item_id = data.id;
            var thumb_up = data.status;

            if(obj.hasClass('do_ajax')){
                return;
            }
            obj.addClass('do_ajax');
            $.post(mooConfig.url.base + '/likes/ajax_add/' + type + '/' + item_id + '/' + thumb_up, { noCache: 1 }, function(data){
                try
                {
                    var res = $.parseJSON(data);

                    obj.parents('.like-section:first').find('.likeCount:first').html( parseInt(res.like_count) );
                    obj.parents('.like-section:first').find('.dislikeCount:first').html( parseInt(res.dislike_count) );

                    if ( thumb_up )
                    {
                        obj.toggleClass('active');
                        obj.next().next().removeClass('active');
                    }
                    else
                    {
                        obj.toggleClass('active');
                        obj.prev().prev().removeClass('active');
                    }
                }
                catch (err)
                {
                    mooUser.validateUser();
                }
                obj.removeClass('do_ajax');
            });
        });
    }


    var deleteCampaign = function(){
        $('.deleteCampaign').unbind('click');
        $('.deleteCampaign').click(function(){

            var data = $(this).data();
            var deleteUrl = mooConfig.url.base + '/fundraisings/do_delete/' + data.id;
            mooAlert.confirm(mooPhrase.__('are_you_sure_you_want_to_remove_this_campaign'), deleteUrl);
        });
    }

    return {
        initOnCreate: initOnCreate,
        initOnView : initOnView,
        initOnListing : initOnListing,
        toggleUploader : toggleUploader,
    }
}));