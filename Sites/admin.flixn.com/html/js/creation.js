function createSubmit()
    {
        if(controller == "moderation")
        {
                var defaultAction = "view";
                var secondaryData = "&defer=" + $("input[@name='defer']:checked").val();
        }
        else
        {
                var defaultAction = "settings";
                var secondaryData = "";
        }       
                
        $("#create_submit_button").attr("disabled","disabled");
        var isValid = false;
        var instanceName = $("input[@name='instance_name']").val();
        $("#create_response").addClass("response_pre").html("").removeClass("response_post");
        
        if(instanceName.match("[^a-zA-Z0-9\\s]") || instanceName.length == 0)
        {
                $("#create_error").addClass("response_post")
                        .html("The name you have entered is not valid.").removeClass("response_pre");
                        // "A name can only contain letters, numbers, underscores, and spaces."
        }
        else if (instanceName.length > 63)
        {
                $("#create_error").addClass("response_post")
                        .html("The name you have entered is too long. A name can only be 63 characters in length.").removeClass("response_pre");
        }
        else if (instanceName.match("^\\s"))
        {
                $("#create_error").addClass("response_post")
                        .html("A name cannot begin with a space.").removeClass("response_pre");
        }
        else
        {
                $.ajax({
                async: true,
                type: "POST",
                url: baseUrl+"/"+controller+"/create/",
                data:   "name="+$("input[@name='instance_name']").val() + secondaryData,
                success: function(data, status){
                                if (data.substr(0,1) == "0")
                                {
                                        $("#create_response").addClass("response_post").html("An instance with this name already exists. " +
                                               "<br />Click <a href='"+baseUrl+"/"+controller+"/" + defaultAction + "/instance/" + data.substr(2,data.length) + "'>here</a> " +
                                               "to go to this instance.").removeClass("response_pre");
                                } else if (data.substr(0,1) == "1")
                                {
                                        $("#create_response").addClass("response_post").html("The instance has been created. " +
                                               "<br />Click <a href='"+baseUrl+"/"+controller+"/settings/instance/" + data.substr(2,data.length) + "'>here</a> " +
                                               "to go to this instance.").removeClass("response_pre");
                                        $("#select_menu ul").append("<li><a href='/"+controller+"/" + defaultAction + "/instance/" + data.substr(2,data.length) + "'>" + instanceName + "</a></li>");
                                        $("#no_instance").remove();
                                }
                },
                error: function() {
                                $("#create_response").addClass("response_post").html("An error occurred.");
                }
        
        });
        $("#create_submit_button").attr("disabled",false);
        $("#instance_name_text").val("");
        }
        $("#create_response").addClass("response_post").removeClass("response_pre");
        $("#create_submit_button").attr("disabled",false);
    }
