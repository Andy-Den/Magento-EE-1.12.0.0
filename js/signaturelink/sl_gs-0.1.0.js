
/* Author:  Daniel Williams, Connect Technology */
/* Project: GetSettings */
/* Note: Assumes jQuery is loaded */

var RetSettings = {
    _aVariablePlaceholder: "",

    init: function () {
        var that = this;
    },

    getSettings: function (settingID, clientID, storeID, useSecure, f, err, languageCode) {
        var url = "";
		
       if (useSecure) {
            url = "https://sls.signaturelink.com/GetSettings.svc/r/" + settingID + "/" + clientID + "/" + storeID +"?languagecode="+languageCode;
        } else {
            url = "http://sls.signaturelink.com/GetSettings.svc/r/" + settingID + "/" + clientID + "/" + storeID + "?languagecode="+languageCode;
        }
		
        jQuery.ajax({
            type: 'GET',
            url: url,
            dataType: 'jsonp',
            error: function (e) {
                if (err) {
                    err("error");
                }
            },
            success: function (data) {
                if (f) {
                    if (data) {
                        f(data);
                    } else if (err) {
                        err("no data");
                    }
                }
            }
        });

    }
}
