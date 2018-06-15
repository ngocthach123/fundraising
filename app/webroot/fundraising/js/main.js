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
            'tinyMCE', 'mooUser', 'mooButton', 'picker_date', 'tagsinput'], factory);
    } else if (typeof exports === 'object') {
        // Node, CommonJS-like
        module.exports = factory(require('jquery'));
    } else {
        // Browser globals (root is window)
        root.mooFundraising = factory();
    }
}(this, function ($, mooBehavior, mooFileUploader, mooAjax, mooOverlay, mooAlert, mooPhrase, mooGlobal, tinyMCE, mooUser, mooButton) {

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
                uploadButton: '<div class="upload-section"><i class="material-icons">photo_camera</i>' + mooPhrase.__('drag_or_click_here_to_upload_photo') +' </div>'
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

        if(typeof google.maps.places !== 'undefined')
        {
            var autocomplete = new google.maps.places.Autocomplete(document.getElementById('location'));
        }
    }

    var toggleUploader = function() {
        $('#images-uploader').slideToggle();
    }

    // app/Plugin/Topic/View/Topics/view.ctp
    var initOnView = function(){
        mooOverlay.registerImageOverlay();

        // bind action to button delete
        deleteCampaign();

        viewMap();
    }

    var viewMap = function()
    {
        $('#view_map').unbind('click');
        $('#view_map').click(function() {
            address = $(this).html();

            $('#campaignModal .modal-content').empty().append('');
            $('#campaignModal').modal();
            $.post(mooConfig.url.base + "/fundraisings/view_map", 'address=' + address, function(data){
                $('#campaignModal .modal-content').empty().append(data);
            });
        });

    }

    var initOnListing = function(){
        mooBehavior.initMoreResults();

        // bind action to button delete
        deleteCampaign();

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

    var initAjaxInvite = function(){

        var friends_userTagging = new Bloodhound({
            datumTokenizer:function(d){
                return Bloodhound.tokenizers.whitespace(d.name);
            },
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            prefetch: {
                url: mooConfig.url.base + '/users/friends.json',
                cache: false,
                filter: function(list) {

                    return $.map(list.data, function(obj) {
                        return obj;
                    });
                }
            },

            identify: function(obj) { return obj.id; },
        });

        friends_userTagging.initialize();


        $('#friends').tagsinput({
            freeInput: false,
            itemValue: 'id',
            itemText: 'name',
            typeaheadjs: {
                name: 'friends_userTagging',
                displayKey: 'name',
                highlight: true,
                limit:10,
                source: friends_userTagging.ttAdapter(),
                templates:{
                    notFound:[
                        '<div class="empty-message">',
                        mooPhrase.__('no_results'),
                        '</div>'
                    ].join(' '),
                    suggestion: function(data){
                        if($('#friends').val() != '')
                        {
                            var ids = $('#friends').val().split(',');
                            if(ids.indexOf(data.id) != -1 )
                            {
                                return '<div class="empty-message" style="display:none">'+mooPhrase.__('no_results')+'</div>';
                            }
                        }
                        return [
                            '<div class="suggestion-item">',
                            '<img alt src="'+data.avatar+'"/>',
                            '<span class="text">'+data.name+'</span>',
                            '</div>',
                        ].join('')
                    }
                }
            }
        });
        $('#sendButton').unbind('click');
        $('#sendButton').click(function(){
            $('#sendButton').spin('small');
            mooButton.disableButton('sendButton');
            $(".error-message").hide();

            mooAjax.post({
                url : mooConfig.url.base + '/fundraisings/ajax_sendInvite',
                data: $("#sendInvite").serialize()
            }, function(data){
                mooButton.enableButton('sendButton');
                $('#sendButton').spin(false);
                var json = $.parseJSON(data);
                if ( json.result == 1 )
                {
                    $('#simple-modal-body').html(json.msg);
                }
                else
                {
                    $(".error-message").show();
                    $(".error-message").html(json.message);
                }
            });

            return false;

        });

        $('#invite_type_topic').change(function(){
            $('#invite_friend').hide();
            $('#invite_email').hide();
            if ($('#invite_type_topic').val() == '1')
            {
                $('#invite_friend').show();
            }
            else
            {
                $('#invite_email').show();
            }
        });
    }

    var initMailSetting = function(){
        $('#btn_save').on('click', function(){

            $('#content').val(tinyMCE.activeEditor.getContent());
            $(this).spin('small');

            $.post(mooConfig.url.base + "/fundraisings/email_setting/",$('#formMailSetting').serialize(), function (data) {
                data = $.parseJSON(data);
                if(data.result == '1'){
                    $('#msg_success').show();
                    $(".error-message").hide();
                }else{
                    $('#msg_success').hide();
                    $(".error-message").show();
                    $(".error-message").html(data.message);
                }
                $('#btn_save').spin(false);
            });
        });
    }

    var initDonation = function(){
        $('.pre-item').unbind('click');
        $('.pre-item').click(function(){
            var data = $(this).data();
            $('#amount').val(data.value);
        });

        $('#view_term').click(function (e) {
            e.preventDefault();
            mooAlert.alert($('#term_content').html());
        });

        $('#btn_pay_offline').unbind('click');
        $('#btn_pay_offline').click(function (e) {
            $(this).spin('small');
            $.post(mooConfig.url.base + "/fundraisings/pay_offline/",$('#formDonation').serialize(), function (data) {
                data = $.parseJSON(data);
                if(data.result == '1'){
                    $.post(mooConfig.url.base + "/fundraisings/pay_offline/", function (data) {
                        $('#pay_step_1 li').hide();
                        $('#wrap_btn_payoffline').show();
                        $('#wrap_errorMessage').show();
                        $('#formDonation').prepend(data);
                        $('#captcha').attr('style', 'display: flex;');
                    });
                }else{
                    $('#msg_success').hide();
                    $(".error-message").show();
                    $(".error-message").html(data.message);
                }
                $('#btn_pay_offline').spin(false);
            });
        });

        $('#pay_step_1').on('click', '#btn_send_payoffline',function (e) {
            $(this).spin('small');
            $.post(mooConfig.url.base + "/fundraisings/pay_offline/1",$('#formDonation').serialize(), function (data) {
                data = $.parseJSON(data);
                if(data.result == '1'){
                    window.location = data.redirect;
                }else{
                    $('#msg_success').hide();
                    $(".error-message").show();
                    $(".error-message").html(data.message);
                }
                $('#btn_send_payoffline').spin(false);
            });
        });

        $('#btn_pay_paypal').unbind('click');
        $('#btn_pay_paypal').click(function (e) {
            $(this).spin('small');
            $.post(mooConfig.url.base + "/fundraisings/pay_paypal/",$('#formDonation').serialize(), function (data) {
                data = $.parseJSON(data);
                if(data.result == '1'){
                    $('#paypal_amount').val(data.amount);
                    $('#notify_url').val(data.notify_url);
                    $('#cancel_return').val(data.cancel_url);
                    $('#errorMessage').hide();
                    $('#paypal_form').submit();
                }else{
                    $('#msg_success').hide();
                    $(".error-message").show();
                    $(".error-message").html(data.message);
                    $('#btn_pay_paypal').spin(false);
                }
            });
        });
    }

    var initOnDonorListing = function() {
        mooBehavior.initMoreResults();
    }

    var initDeleteDonor = function() {
        $('#btn_delete_donor').unbind('click');
        $('#btn_delete_donor').click(function (e) {
            $(this).spin('small');
            $.post(mooConfig.url.base + "/fundraisings/delete_donor/",$('#formDeleteDonor').serialize(), function (data) {
                data = $.parseJSON(data);
                if(data.result == '1'){
                    window.location.reload();
                }else{
                    $(".error-message").show();
                    $(".error-message").html(data.message);
                }
                $('#btn_delete_donor').spin(false);
            });
        });
    }

    var initReceiveDonor = function() {
        $('#btn_receive_donor').unbind('click');
        $('#btn_receive_donor').click(function (e) {
            $(this).spin('small');
            $.post(mooConfig.url.base + "/fundraisings/receive_donor/",$('#formReceiveDonor').serialize(), function (data) {
                data = $.parseJSON(data);
                if(data.result == '1'){
                    window.location.reload();
                }else{
                    $(".error-message").show();
                    $(".error-message").html(data.message);
                }
                $('#btn_receive_donor').spin(false);
            });
        });
    }

    var initViewMap = function() {
        var myOptions = {
            zoom:15,
             center:new google.maps.LatLng($('#map_canvas').attr('lat'), $('#map_canvas').attr('lng')),
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        map = new google.maps.Map(document.getElementById('map_canvas'), myOptions);

        var infowindow = new google.maps.InfoWindow();

        //add marker
        var marker;
        var address = $('#map_canvas').attr('address');

        marker = new google.maps.Marker({
            position: new google.maps.LatLng($('#map_canvas').attr('lat'), $('#map_canvas').attr('lng')),
            map: map,
        });

        google.maps.event.addListener(marker, 'click', (function(marker) {
            return function() {
                infowindow.setContent(address);
                infowindow.open(map, marker);
            }
        })(marker));
    }

    return {
        initOnCreate: initOnCreate,
        initOnView : initOnView,
        initOnListing : initOnListing,
        toggleUploader : toggleUploader,
        initAjaxInvite : initAjaxInvite,
        initMailSetting : initMailSetting,
        initDonation : initDonation,
        initOnDonorListing : initOnDonorListing,
        initDeleteDonor : initDeleteDonor,
        initReceiveDonor : initReceiveDonor,
        initViewMap : initViewMap
    }
}));