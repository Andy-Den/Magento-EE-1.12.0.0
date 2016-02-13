
/* Project: Screen-Scrape */

if (!document.ELEMENT_NODE) {
    document.ELEMENT_NODE = 1;
    document.ATTRIBUTE_NODE = 2;
    document.TEXT_NODE = 3;
    document.CDATA_SECTION_NODE = 4;
    document.ENTITY_REFERENCE_NODE = 5;
    document.ENTITY_NODE = 6;
    document.PROCESSING_INSTRUCTION_NODE = 7;
    document.COMMENT_NODE = 8;
    document.DOCUMENT_NODE = 9;
    document.DOCUMENT_TYPE_NODE = 10;
    document.DOCUMENT_FRAGMENT_NODE = 11;
    document.NOTATION_NODE = 12;
}

document._importNode = function (node, allChildren) {
    switch (node.nodeType) {
        case document.ELEMENT_NODE:
            var newNode = document.createElement(node.nodeName);

            /* does the node have any attributes to add? */
            if (node.attributes && node.attributes.length > 0)
                for (var i = 0; i < node.attributes.length; i++) {
                    if (node.attributes[i].nodeName.indexOf("value") != -1) // i < 1 )
                        alert(node.name + " = " + node.getAttribute(node.attributes[i].nodeName));
                    newNode.setAttribute(node.attributes[i].nodeName, node.getAttribute(node.attributes[i].nodeName));
                }

            /* are we going after children too, and does the node have any? */
            if (allChildren && node.childNodes && node.childNodes.length > 0)
                for (var i = 0; i < node.childNodes.length; i++)
                    newNode.appendChild(document._importNode(node.childNodes[i], allChildren));

            return newNode;
            break;

        case document.TEXT_NODE:
        case document.CDATA_SECTION_NODE:
        case document.COMMENT_NODE:
            return document.createTextNode(node.nodeValue);
            break;
    }
};

var slScrape = {

    _maskFields: null,

    _getHTML: function (replaceHtml5, flashDivID, html5DivID, removeProfileTags, profileDivID, removeScripts, encodeBase64, maskFields) {
        var src = document.documentElement.innerHTML;
        var src1 = document.documentElement.outerHTML;
        var upd = false;
        var htmlData;

        this._maskFields = maskFields;


        //-----------------------------------------------------------------------
        // 1. Create new body element and update the values
        //-----------------------------------------------------------------------
        var dom = document.createElement("body");
        dom.innerHTML = document.getElementsByTagName("body")[0].innerHTML;
        this._replaceFields(dom, upd);

        if (removeProfileTags)
            this._removeProfilingTags(dom, profileDivID); // null with remove the default DIV ID; see function for 

        if (replaceHthml5)
            this._replaceHtml5(dom, flashDivID, html5DivID);

        if (removeScripts)
            jQuery(dom).find('script').each(function () { jQuery(this).remove(); });

        jQuery(dom).find('#SignremoteF9 > param').each(function (i, e) {
            var fURL, name = jQuery(e).attr("name");
            name = name.toLowerCase();
            if (name == 'movie')
                fURL = jQuery(name).val();
        });



        //-----------------------------------------------------------------------
        // 2. Retrieve existing BODY tag (opening only) from innerHTML
        //-----------------------------------------------------------------------
        var b = "<" + "BODY";
        var i = src.indexOf(b, 0);
        if (i == -1) i = src.indexOf(b.toLowerCase(), 0);
        if (i == -1) i = src.toLowerCase().indexOf(b.toLowerCase(), 0);

        if (i == -1) {
            alert("there is an issue");
            return false;
        }
        var n = src.indexOf(">", i + 1);

        var bodyHTML = src.substring(i, n + 1);
        bodyHTML = bodyHTML + dom.innerHTML + "</BODY>"

        var headHTML = '<head>' + (document.getElementsByTagName("base").length == 0 ? '<base href="' + this.getBaseURL() + '" />' : '') + document.getElementsByTagName("head")[0].innerHTML + '</head>';

        //-----------------------------------------------------------------------
        // 3. Update TEXTAREA element with data for debuging 
        //-----------------------------------------------------------------------
        //document.getElementsByTagName("textarea")[1].value = "<html>" + headHTML + bodyHTML + "</html>";

        return "<html>" + headHTML + bodyHTML + "</html>";
    },

    _replaceChild: function (dom, e, update) {
        try {
            var v = this._getFieldValueFromDoc(e);

            if (!update) {
                if (e.type == "text") {
                    // put text into new page fragment & replace source with new fragment 
                    //var frag = document.createDocumentFragment();
                    //frag.appendChild( document.createTextNode(v) );
                    //e.parentNode.replaceChild(frag, e);
                    jQuery(e).replaceWith(v);
                }
            }
            else if (update) {
                jQuery(e).val(v);
                jQuery(e).attr('value', v);
                //e.setAttribute("value", v);
            }
        }
        catch (ex) {
            alert("Error - " + ex);
        }
    },

    _replaceFields: function (dom, update) {
        var e;
        // var c = dom.getElementsByTagName("input");
        var m = false;
        var that = this;

        jQuery(dom).find(':input', '#SupportForm').each(function () {
            e = this;
            if (e.type == "text" || e.type == "checkbox" || e.type == "radio" || e.type == "textarea") {
                jQuery(e).replaceWith(that._getElementWithUserInput(e));
            }
            else if (e.type == "select-one" || e.type == "select-multiple") {
                jQuery(e).html(that._getElementWithUserInput(e));
            }

        });
    },

    _removeProfilingTags: function (dom, profileDivID) {
        if (!profileDivID)
            profileDivID = 'tmProfTag';

        jQuery(dom).find('#' + profileDivID).each(function () {
            e = this;
            // Comment out the innerHTML
            jQuery(e).html('<!-- Added comment tags to prevent profile tags from executing when rendering \n' + jQuery(e).html() + '\n -->');
        });
    },

    _replaceHtml5: function (dom, flashDivID, html5DivID) {
        try {
            // display the Flash sign pad and hide the HTML5 version
            jQuery(dom).find('#' + flashDivID).parent().css('display', 'block');
            jQuery(dom).find('#' + html5DivID).parent().css('display', 'none');
        }
        catch (ex) { }
    },

    _getFieldValueFromDoc: function (el) {
        var e, c = document.getElementsByTagName(el.tagName);
        var v, f = false;
        var n;

        for (var i = c.length - 1; i > -1; i--) {
            e = c[i];
            if (e.type == el.type && e.name == el.name) {
                if (el.type == "text") {
                    v = e.value;
                    f = true;
                    break;
                }
            }
        }
        return f ? v : null;
    },

    _getElementWithUserInput: function (el) {
        var e, selOpt, c = document.getElementsByTagName(el.tagName);
        var v, f = false;
        var n;
        var t = '<input ';

        var type = el.type.toLowerCase();

        for (var i = c.length - 1; i > -1; i--) {
            e = c[i];
            if (e.type.toLowerCase() == type && e.name == el.name) {
                ej = jQuery(e.type.toLowerCase() + '[name="' + el.name + '"]');

                if (type == "text") {
                    v = this._getFieldValue(e);
                    if (!v) {
                        v = jQuery(e).val();
                    }

                    var attrs = el.attributes;
                    for (var i = 0; i < attrs.length; i++) {
                        if (attrs[i].nodeName == 'value') {
                            t += (attrs[i].nodeName + '="' + v + '" ');
                            f = true;
                        }
                        else
                            t += (attrs[i].nodeName + '="' + attrs[i].nodeValue + '" ');
                    }
                    if (!f)
                        t += ('value="' + v + '" ');

                    t += '>';
                    f = true;
                    break;
                }
                else if ((type == "checkbox" || type == "radio") && e.value == el.value) {
                    v = jQuery(e).attr('checked');
                    var attrs = el.attributes;
                    for (var i = 0; i < attrs.length; i++) {
                        if (attrs[i].nodeName == 'checked') {
                            if (v) {
                                t += (attrs[i].nodeName + '="checked" ');
                                f = true;
                            }
                        }
                        else
                            t += (attrs[i].nodeName + '="' + attrs[i].nodeValue + '" ');
                    }
                    if (v && !f)
                        t += ('checked="checked" ');

                    t += '>';
                    f = true;
                    break;
                }
                else if (type == "select-one" || type == "select-multiple") {
                    t = '';
                    selOpt = jQuery('option:selected', e);

                    jQuery(e).find('option').each(function () {
                        v = (jQuery(selOpt) == jQuery(this) || jQuery(selOpt).get(0) == jQuery(this) || jQuery(selOpt).get(0) == this);
                        t += '<option ';

                        var attrs = this.attributes;
                        for (var i = 0; i < attrs.length; i++) {
                            if (attrs[i].nodeName == 'selected') {
                                if (v) {
                                    t += (attrs[i].nodeName + '="selected" ');
                                    f = true;
                                }
                            }
                            else
                                t += (attrs[i].nodeName + '="' + attrs[i].nodeValue + '" ');
                        }
                        if (v && !f)
                            t += ('selected="selected" ');

                        t += ('>' + jQuery(this).text() + '</option>');
                    });

                    f = true;
                    break;
                }
                else if (type == "textarea") {
                    v = jQuery(e).val();
                    t = '<textarea ';

                    var attrs = el.attributes;
                    for (var i = 0; i < attrs.length; i++) {
                        if (attrs[i].nodeName != 'value')
                            t += (attrs[i].nodeName + '="' + attrs[i].nodeValue + '" ');
                    }
                    t += ('>' + this.htmlEncode(v) + '</textarea>');

                    f = true;
                    break;
                }
            }
        }

        v = t;
        return f ? v : null;
    },
    getBaseURL: function () {
        var url = location.protocol + "//" + location.host + location.pathname;
        url = url.substr(0, url.lastIndexOf("/"));
        return url;
    },
    htmlEncode: function (value) {
        return jQuery('<div/>').text(value).html();
    },
    htmlDecode: function (value) {
        return jQuery('<div/>').html(value).text();
    },

    _iterate: function (obj, val) {
        var found = null;
        try {
            for (var prop in obj) {
                if (obj.hasOwnProperty(prop)) {
                    if (typeof obj[prop] == "object") {
                        found = iterate(obj[prop], val);
                    }
                    else if (obj[prop] == val) {
                        console.log(prop + "   " + obj[prop]);
                        var found = new Object;
                        found[prop] = obj[prop];
                    }
                }
                if (found)
                    break;
            }
        }
        catch (ex) {
            // todo: add logging
        }
        return found;
    },
    _getFirstPropertyName: function (obj) {
        var propName = null;
        if (obj) {
            for (var prop in obj) {
                if (obj.hasOwnProperty(prop)) {
                    propName = prop;
                    break;
                }
            }
        }
        return propName;
    },
    _getMaskedField: function (fieldID) {
        var isMaskedField = null;
        try {
            if (this._maskFields) {
                var prop = this._iterate(this._maskFields, fieldID);
                var key = this._getFirstPropertyName(prop);
                if (key) {
                    isMaskedField = key;
                }
            }
        }
        catch (ex) {
            // todo: add logging
        }
        return isMaskedField;
    },

    /**
    * Left-mask a string so that only the last "N" characters are not masked.
    * 
    * @param val  The string to mask
    * @param n    The number of characters at the end of the string that should not be masked
    * @param pad  The desired string used as the mask
    */
    _leftMask: function (val, n, pad) {
        var v = null;
        try {
            var len = String(val).length;
            if (len > n) {
                v = (new Array(len - n + 1)).join(pad).concat(val.substr(len - n));
            }
            else {
                v = val;
            }
        }
        catch (ex) {

        }
        return v;
    },

    /**
    * Right-mask a string so that only the first "N" characters are not masked.
    * 
    * @param val  The string to mask
    * @param n    The number of characters at the start of the string that should not be masked
    * @param pad  The desired string used as the mask
    */
    _rightMask: function (val, n, pad) {
        var v = null;
        try {
            var len = String(val).length;
            if (len > first) {
                v = val.substr(0, n).concat((new Array(len - n + 1)).join(pad));
            }
            else {
                v = val;
            }
        }
        catch (ex) {

        }
        return v;
    },

    _getFieldValue: function (field) {
        var val = jQuery(field).val();
        var propName = this._getMaskedField(jQuery(field).attr('id'));
        if (propName) {
            switch (propName) {
                case "ccnum":
                    if (val && val.length > 4)
                        val = this._leftMask(val, 4, '*');
                    break;
                case "cccvs":
                    val = this._leftMask(val, 0, '*');
                    break;
                case "last4":
                    val = this._leftMask(val, 4, '*');
                    break;
                case "last3":
                    val = this._leftMask(val, 3, '*');
                    break;
                case "last2":
                    val = this._leftMask(val, 2, '*');
                    break;
                case "last1":
                    val = this._leftMask(val, 1, '*');
                    break;
                case "first4":
                    val = this._rightMask(val, 4, '*');
                    break;
                default:
                    val = this._leftMask(val, 0, '*');
                    break;
            }
        }
        return val;
    },

  _getMaskedFieldValue: function (field, propName) {
        var val = jQuery('#' + field).val();
        // var propName = this._getMaskedField(jQuery(field).attr('id'));
        if (propName) {
            switch (propName) {
                case "ccnum":
                    if (val && val.length > 4)
                        val = this._leftMask(val, 4, '*');
                    break;
                case "cccvs":
                    val = this._leftMask(val, 0, '*');
                    break;
                case "last4":
                    val = this._leftMask(val, 4, '*');
                    break;
                case "last3":
                    val = this._leftMask(val, 3, '*');
                    break;
                case "last2":
                    val = this._leftMask(val, 2, '*');
                    break;
                case "last1":
                    val = this._leftMask(val, 1, '*');
                    break;
                case "first4":
                    val = this._rightMask(val, 4, '*');
                    break;
                default:
                    val = this._leftMask(val, 0, '*');
                    break;
            }
        }
        return val;
    }

}

var base64 = {};
base64.PADCHAR = '=';
base64.ALPHA = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';

base64.makeDOMException = function () {
    var e, tmp;

    try {
        return new DOMException(DOMException.INVALID_CHARACTER_ERR);
    } catch (tmp) {
        var ex = new Error("DOM Exception 5");

        ex.code = ex.number = 5;
        ex.name = ex.description = "INVALID_CHARACTER_ERR";

        ex.toString = function () { return 'Error: ' + ex.name + ': ' + ex.message; };
        return ex;
    }
}

base64.getbyte64 = function (s, i) {
    var idx = base64.ALPHA.indexOf(s.charAt(i));
    if (idx === -1) {
        throw base64.makeDOMException();
    }
    return idx;
}

base64.decode = function (s) {
    s = '' + s;
    var getbyte64 = base64.getbyte64;
    var pads, i, b10;
    var imax = s.length
    if (imax === 0) {
        return s;
    }

    if (imax % 4 !== 0) {
        throw base64.makeDOMException();
    }

    pads = 0
    if (s.charAt(imax - 1) === base64.PADCHAR) {
        pads = 1;
        if (s.charAt(imax - 2) === base64.PADCHAR) {
            pads = 2;
        }

        imax -= 4;
    }

    var x = [];
    for (i = 0; i < imax; i += 4) {
        b10 = (getbyte64(s, i) << 18) | (getbyte64(s, i + 1) << 12) |
            (getbyte64(s, i + 2) << 6) | getbyte64(s, i + 3);
        x.push(String.fromCharCode(b10 >> 16, (b10 >> 8) & 0xff, b10 & 0xff));
    }

    switch (pads) {
        case 1:
            b10 = (getbyte64(s, i) << 18) | (getbyte64(s, i + 1) << 12) | (getbyte64(s, i + 2) << 6);
            x.push(String.fromCharCode(b10 >> 16, (b10 >> 8) & 0xff));
            break;
        case 2:
            b10 = (getbyte64(s, i) << 18) | (getbyte64(s, i + 1) << 12);
            x.push(String.fromCharCode(b10 >> 16));
            break;
    }
    return x.join('');
}

base64.getbyte = function (s, i) {
    var x = s.charCodeAt(i);
    if (x > 255) {
        throw base64.makeDOMException();
    }
    return x;
}

base64.encode = function (s) {
    if (arguments.length !== 1) {
        throw new SyntaxError("Not enough arguments");
    }
    var padchar = base64.PADCHAR;
    var alpha = base64.ALPHA;
    var getbyte = base64.getbyte;

    var i, b10;
    var x = [];

    s = '' + s;

    var imax = s.length - s.length % 3;

    if (s.length === 0) {
        return s;
    }
    for (i = 0; i < imax; i += 3) {
        b10 = (getbyte(s, i) << 16) | (getbyte(s, i + 1) << 8) | getbyte(s, i + 2);
        x.push(alpha.charAt(b10 >> 18));
        x.push(alpha.charAt((b10 >> 12) & 0x3F));
        x.push(alpha.charAt((b10 >> 6) & 0x3f));
        x.push(alpha.charAt(b10 & 0x3f));
    }
    switch (s.length - imax) {
        case 1:
            b10 = getbyte(s, i) << 16;
            x.push(alpha.charAt(b10 >> 18) + alpha.charAt((b10 >> 12) & 0x3F) +
               padchar + padchar);
            break;
        case 2:
            b10 = (getbyte(s, i) << 16) | (getbyte(s, i + 1) << 8);
            x.push(alpha.charAt(b10 >> 18) + alpha.charAt((b10 >> 12) & 0x3F) +
               alpha.charAt((b10 >> 6) & 0x3f) + padchar);
            break;
    }
    return x.join('');
}

function base64Encode(str) {
    var CHARS = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
    var out = "", i = 0, len = str.length, c1, c2, c3;
    while (i < len) {
        c1 = str.charCodeAt(i++) & 0xff;
        if (i == len) {
            out += CHARS.charAt(c1 >> 2);
            out += CHARS.charAt((c1 & 0x3) << 4);
            out += "==";
            break;
        }
        c2 = str.charCodeAt(i++);
        if (i == len) {
            out += CHARS.charAt(c1 >> 2);
            out += CHARS.charAt(((c1 & 0x3) << 4) | ((c2 & 0xF0) >> 4));
            out += CHARS.charAt((c2 & 0xF) << 2);
            out += "=";
            break;
        }
        c3 = str.charCodeAt(i++);
        out += CHARS.charAt(c1 >> 2);
        out += CHARS.charAt(((c1 & 0x3) << 4) | ((c2 & 0xF0) >> 4));
        out += CHARS.charAt(((c2 & 0xF) << 2) | ((c3 & 0xC0) >> 6));
        out += CHARS.charAt(c3 & 0x3F);
    }
    return out;
}
