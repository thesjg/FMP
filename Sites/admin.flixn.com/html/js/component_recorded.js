var moderationInstance;
var styleId;

function aspectLock(a)
{
    if (a == 1)
        $("#component_height").val(Math.round(parseInt($("#component_width").val()) / (4/3)) + 35);
    else
        $("#component_width").val(Math.round(parseInt($("#component_height").val() - 35) * (4/3)));
}

function selectSChange()
{
    moderationInstance = $('#moderation_profile_select').val();
    transcodeProfile = $('#transcode_profile_select').val();
}

function selectAChange()
{
    styleId=$('#recorded_style').val();
}

styleId = $("#recorded_style").val();

$(document).ready(function(){

    componentUpdatePreviews();

    moderationInstance = $("#moderation_profile_select").val();
    transcodeProfile = $('#transcode_profile_select').val();
    selectSChange();
    styleId = $("#uploaded_style").val();
/*
    <?php
        if ($this->moderatedSelectDisable == 'checked="checked"')
            echo "modDisableSelect();";
    ?>

    $('#settings_submit_button').click(function(){
        $('#recorder_settings').submit();
    });

    $('#recorder_settings').ajaxForm({
        dataType: null,
        success:  processFormResult
    });
*/


    $("#settings_submit_button").click (
        function() {
            $('#settings_submit_button').attr('disabled','true');
            $.ajax({
            type: "POST",
            url: baseUrl + '/recorded/ajax/instance/' + instanceId,
            data:   "s_name="+$("input[@name='s_name']").val()+
                    "&s_type="+$("input[@name='s_type']:checked").val()+
                    "&s_hq="+$("input[@name='s_hq']:checked").val()+
                    "&s_time_hours="+$("input[@name='s_time_hours']").val()+
                    "&s_time_minutes="+$("input[@name='s_time_minutes']").val()+
                    "&s_time_seconds="+$("input[@name='s_time_seconds']").val()+
                    "&s_upload_moderation="+$("input[@name='s_upload_moderation']:checked").val()+
                    "&s_moderation_instance="+moderationInstance+
                    "&formMode=settings",
            success: function(data, status){
                    $('#settings_submit_button').removeAttr('disabled');
                    $('#instance_selected').html($("input[@name='s_name']").val());
                    $('#instance_name_top').html($("input[@name='s_name']").val());
                    $('#s_settings_response').show();
                    $('#s_settings_response').removeClass("response_pre")
                            .stop()
                            .addClass("response_post")
                            .html("Your settings have been saved.")
                            .fadeTo(4000,100).fadeOut(4000);
                    },
            error: function(XMLHttp, error) {
                    $('#s_settings_response').removeClass("response_pre")
                            .stop()
                            .addClass("response_post")
                            .html("An error occured. Please Try again.")
                            .fadeTo(12000,100).fadeOut(5000).css("color: 'red'");
                    $('#settings_submit_button').removeAttr('disabled');
                    }
            });

            componentUpdatePreviews();
        });


    $("#appearance_submit_button").click (
        function() {
            $('#appearance_submit_button').attr('disabled','true');
            $.ajax({
            type: "POST",
            url: "/recorded/ajax/instance/" + instanceId,
            data:   "component_width="+$("#component_width").val()+
                    "&component_height="+$("#component_height").val()+
                    "&formMode=appearance",
            success: function(data, status){
                    $("#component_width").val(data.substr(0,data.search(":")));
                    $("#component_height").val(data.substr(data.search(":") + 1,(data.search(";")-data.search(":")) - 1));
                    $('#appearance_submit_button').removeAttr('disabled');
                    $('#style_response').show();
                    $('#style_response').removeClass("response_pre")
                            .stop()
                            .addClass("response_post")
                            .html("Your settings have been saved.")
                            .fadeTo(4000,100).fadeOut(4000);
                    },
            error: function(XMLHttp, error) {
                    $('#style_response').removeClass("response_pre")
                            .stop()
                            .addClass("response_post")
                            .html("An error occured. Please Try again.")
                            .fadeTo(12000,100).fadeOut(5000).css("color: 'red'");
                        $('#appearance_submit_button').removeAttr('disabled');
                        }
                });

            componentUpdatePreviews();
        });

    $("#transcode_submit_button").click (
            function() {

                transcodeProfiles = getTranscodeProfiles();

                $('#transcode_submit_button').attr('disabled','true');
                $.ajax({
                type: "POST",
                url: '/recorded/ajax/instance/' + instanceId,
                data:   "transcode_profiles="+transcodeProfiles+
                        "&formMode=transcode",
                success: function(data, status){
                        $('#transcode_submit_button').removeAttr('disabled');
                        $('#s_transcode_settings_response').show();
                        $('#s_transcode_settings_response').removeClass("response_pre")
                                .stop()
                                .addClass("response_post")
                                .html("Your settings have been saved.")
                                .fadeTo(4000,100).fadeOut(4000);
                        },
                error: function(XMLHttp, error) {
                        $('#s_transcode_settings_response').removeClass("response_pre")
                                .stop()
                                .addClass("response_post")
                                .html("An error occured. Please Try again.")
                                .fadeTo(12000,100).fadeOut(5000).css("color: 'red'");
                        $('#transcode_submit_button').removeAttr('disabled');
                        }
                });
            });

        $("#tselected_button").click(function(){
             return !$('#trans_selected option:selected').remove().appendTo('#trans_available');
        });

        $("#tavailable_button").click(function(){
            return !$('#trans_available option:selected').remove().appendTo('#trans_selected');
        })

});