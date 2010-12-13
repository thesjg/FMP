var moderationInstance;
var styleId;

function selectSChange()
{
    moderationInstance = $('#moderation_profile_select').val();
    transcodeProfile = $('#transcode_profile_select').val();
}

function selectAChange()
{
    styleId=$('#recorded_style').val();
}

$(document).ready(function(){
    moderationInstance = $("#moderation_profile_select").val();
    transcodeProfile = $('#transcode_profile_select').val();
    selectSChange();

    componentUpdatePreviews();

    styleId = $("#uploaded_style").val();
    $("#settings_submit_button").click (
        function() {
            $('#settings_submit_button').attr('disabled','true');
            $.ajax({
            type: "POST",
            url: baseUrl + "/uploaded/ajax/instance/" + instance,
            data:   "s_name="+$("input[@name='s_name']").val()+
                    "&s_type="+$("input[@name='s_type']:checked").val()+
                    "&s_size="+$("input[@name='s_size']").val()+
                    "&s_file_size="+$("input[@name='s_file_size']").val()+
                    "&s_upload_moderation="+$("input[@name='s_upload_moderation']:checked").val()+
                    "&s_moderation_instance="+moderationInstance+
                    "&formMode=settings",
            success: function(data, status){
                    $("input[@name='s_size']").val(data.substr(0,data.search(":")));
                    $("input[@name='s_file_size']").val(data.substr(data.search(":") + 1,(data.search(";")-data.search(":")) - 1));
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
            url: "/uploaded/ajax/instance/" + instanceId,
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
                url: '/uploaded/ajax/instance/' + instanceId,
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

    //$("#code_display > ul").tabs();
});