    // Show/hide Add This Follow with checkbox click-->		

    $("#notification").click(function(){
        if ($("#notification").is(":checked")) {
            $("#emailNotification").show();
        } else {     
             $("#emailNotification").hide();
        }
    });

    if ($("#notification").is(":checked")) {
        $("#emailNotification").show();
    } else {     
        $("#emailNotification").hide();
    }
    
    
    $("#add_this").click(function(){
        if ($("#add_this").is(":checked")) {
            $("#addThisBox").show();
        } else {     
             $("#addThisBox").hide();
        }
    });

    if ($("#add_this").is(":checked")) {
        $("#addThisBox").show();
    } else {     
        $("#addThisBox").hide();
    }   


    // Show/hide Share This with checkbox click-->		
    $("#share_this").click(function(){
        if ($("#share_this").is(":checked")) {
            $("#shareThisBox").show();
        } else {     
             $("#shareThisBox").hide();
        }
    });

    
    if ($("#share_this").is(":checked")) {
        $("#shareThisBox").show();
    } else {     
        $("#shareThisBox").hide();
    }


    // Show/hide Twitter Post with checkbox click-->		
    $("#twitter_post").click(function(){
        if ($("#twitter_post").is(":checked")) {
            $("#twitterPostBox").show();
        } else {     
             $("#twitterPostBox").hide();
        }
    });

    if ($("#twitter_post").is(":checked")) {
        $("#twitterPostBox").show();
    } else {     
        $("#twitterPostBox").hide();
    }


    // Show/hide Facebook Post with checkbox click-->		
    $("#facebook_post").click(function(){
        if ($("#facebook_post").is(":checked")) {
            $("#facebookPostBox").show();
        } else {     
             $("#facebookPostBox").hide();
        }
    });

    if ($("#facebook_post").is(":checked")) {
        $("#facebookPostBox").show();
    } else {     
        $("#facebookPostBox").hide();
    }	

