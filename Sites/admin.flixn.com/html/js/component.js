var componentId;
var instanceId;

function componentUpdatePreviews()
{
    $('#component_preview_container').width(parseInt($('#component_width').val()))
                                     .height(parseInt($('#component_height').val()));
                                     
    $('#component_preview').parent().parent()
            .width(parseInt($('#component_width').val()))
            .height(parseInt($('#component_height').val()) + 25);

    $('#component_preview')
        .width(parseInt($('#component_width').val()))
        .height(parseInt($('#component_height').val()));
    

    swfobject.embedSWF('http://components.flixn.com/' + componentId + '.swf',
                       'component_preview', '100%', '100%', '9.0.115',
                       'http://components.flixn.com/expressinstall.swf',
                       {}, { allowscriptaccess: 'always', allownetworking: 'all',  menu: 'false'}, {});
    
    // set wmode: 'opaque', if you want to fix the dialog/flash disconnect issue:
    // problem becomes that you can't actually utilize the flash element if you do so
    
    if(1==1) // wtf?
    {
    $('#component_preview')
        .width(parseInt($('#component_width').val()))
        .height(parseInt($('#component_height').val()));
        
    $('#component_preview').parent().parent()
    .width(parseInt($('#component_width').val()))
    .height(parseInt($('#component_height').val()) + 25);
    }
    
    $('#component_preview')
    .width(parseInt($('#component_width').val()))
    .height(parseInt($('#component_height').val()));

}



function noOverlap() {  
$('#component_preview').parent().parent()
.width(parseInt($('#component_width').val()))
.height(parseInt($('#component_height').val()) + 25);

$('#component_preview')
.width(parseInt($('#component_width').val()))
.height(parseInt($('#component_height').val()));
}

function getTranscodeProfiles()
{
    var selectBox = document.getElementById("trans_selected");
    var priority = '';

    for (var i = 0; i < selectBox.length; i++)
        priority += selectBox.options[i].value + ',';

    return priority.substr(0, priority.length - 1);
}



function modEnableSelect()
{
    $("#moderation_profile_select").attr("disabled", "");
    moderationInstance = $('#moderation_profile_select').val();
}

function modDisableSelect()
{
    $("#moderation_profile_select").attr("disabled", "disabled");
}

function confirmDelete()
{
    var answer = confirm("Are you sure you want to delete this instance?");
    if(answer)
        {
            window.location = "/" + controller + "/delete/instance/" + instanceId; 
        }
}

