<?php
/**
 * Description of install.
 * This installs a Cart and readies the data for mapping.
 *
 * @author PMBliss
 * @package Core\Install
 */
if (file_exists('libraries/pos_libs/rpro9'))
{
    $pos_name = 'Retail Pro V9 - RDice';
    $pos_type = 'rpro9';
    $pos_type_install = 'v9';
}
if (file_exists('libraries/pos_libs/rpro8'))
{
    $pos_name = 'Retail Pro V8 - ECI';
    $pos_type = 'rpro8';
    $pos_type_install = 'v8';
}
if (file_exists('libraries/pos_libs/rpro4web'))
{
    $pos_name = 'Retail Pro V8 - RPro 4 Web';
    $pos_type = 'rpro4web';
    $pos_type_install = '4web';
}

if (file_exists('libraries/pos_libs/cp'))
{
    $pos_name = 'CounterPoint - CPice';
    $pos_type = 'cp';
    $pos_type_install = 'cp';
}
if (file_exists('libraries/pos_libs/svl'))
{
    $pos_name = 'SVL';
    $pos_type = 'svl';
    $pos_type_install = 'svl';
}

$install_scripts = file_exists("install_scripts_{$pos_type_install}.php");

$cart_type = include "cart_type.inc";

$rdi_image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEsAAAA2CAYAAACP8mT1AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAAZdEVYdFNvZnR3YXJlAEFkb2JlIEltYWdlUmVhZHlxyWU8AAADImlUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNS4wLWMwNjEgNjQuMTQwOTQ5LCAyMDEwLzEyLzA3LTEwOjU3OjAxICAgICAgICAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIiB4bWxuczpzdFJlZj0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL3NUeXBlL1Jlc291cmNlUmVmIyIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ1M1LjEgV2luZG93cyIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDpBQjE5QzAzRjgwNzgxMUUyQTAyNjlEQzAyMTdGRDAxRCIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDpBQjE5QzA0MDgwNzgxMUUyQTAyNjlEQzAyMTdGRDAxRCI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOkFCMTlDMDNEODA3ODExRTJBMDI2OURDMDIxN0ZEMDFEIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOkFCMTlDMDNFODA3ODExRTJBMDI2OURDMDIxN0ZEMDFEIi8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwv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';
$loader_image = 'data:image/gif;base64,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';

?>

<html>
    <head>
        <meta content="text/html; charset=UTF-8" http-equiv="content-type">
        <meta charset="utf-8">
        <title>Install <?php echo $pos_name; ?></title>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <link href="<?php echo $rdi_image; ?>" rel="shortcut icon">
        <script>
            $(document).ajaxStart(function() {
                $("#loading-mask").show();
            });
            $(document).ajaxComplete(function() {
                $("#loading-mask").hide();
            });
			

			 $(document).ready(function() {

				function functionToLoadFile(){
					
					$.ajax({
						url: 'status',
						cache: false,
						success: function(data) {
						   var myvar = data;

						   $('#status').html(data);
						   setTimeout(functionToLoadFile, 1000);
						}, 
						global: false,     // this makes sure ajaxStart is not triggered
						dataType: 'html'
					});
				}

				setTimeout(functionToLoadFile, 10);
			});


            function loadInstallScripts(part, type)
            {
                var callType;

                if (type === 'rpro9')
                {
                    callType = 'v9';
                }

                if (type === 'rpro8')
                {
                    callType = 'v8';
                }

                if (type === 'rpro4web')
                {
                    callType = '4web';
                }

                if (type === 'cp')
                {
                    callType = 'cp';
                }
				
                if (type === 'svl')
                {
                    callType = 'svl';
                }


                if ($('#verbose_queries').is(':checked'))
                {
                    vq = "1";
                }
                else
                {
                    vq = "0";
                }

                $.ajax({
                    type: 'POST',
                    cache: false,
                    url: 'install_scripts_' + callType + '.php?verbose_queries=' + vq,
                    dataType: "HTML",
                    data: ({
                        action: part
                    }),
                    before: function()
                    {
                        $('#response').html("<h4>Call " + part + "</h4>");

                    },
                    success: function(data) {
                        $('#response').html('');
                        $('#response').append(data);

                    },
                    error: function(xhr, textStatus, errorThrown) {
                        $('#output').append("<li>" + textStatus + "</li>");
                        $('#output').append("<li>" + errorThrown + "</li>");
                    },
                    complete: function(data) {
                        $('#response').append(data);
                        $('#output').append("<li>Finished Request</li>");
                    }


                });

            }


            function callUpload(part)
            {
                $.ajax({
                    type: 'POST',
                    cache: false,
                    url: 'rdi_upload_' + part + '.php',
                    dataType: "HTML",
                    before: function()
                    {
                        $('#response').html("<h4>Call " + part + "</h4>");

                    },
                    success: function(data) {
                        $('#response').append(data);

                    },
                    error: function(xhr, textStatus, errorThrown) {
                        $('#output').append("<li>" + textStatus + "</li>");
                    },
                    complete: function() {
                        $('#output').append("<li>Finished Request</li>");
                    }


                });

            }

            function callUrlWithUpdates(url, query)
            {
                //add_progress_bar(query);

                ajax_stream(url);

            }

            function callTools(part)
            {
                var vq;

                if ($('#verbose_queries').is(':checked'))
                {
                    vq = "1";
                }
                else
                {
                    vq = "0";
                }
                if ($('#load_product_data').is(':checked'))
                {
                    lpd = "1";
                }
                else
                {
                    lpd = "0";
                }

                $.ajax({
                    type: 'GET',
                    cache: false,
                    url: 'libraries/cart_libs/<?php echo $cart_type ?>/tools/' + part + '.php',
                    dataType: "HTML",
                    data: ({verbose_queries: vq, load_product_data: lpd}),
                    beforeSend: function()
                    {
                        $('#response').html("<h4>Call tool" + part + "</h4>");

                    },
                    success: function(data) {
                        $('#response').append(data);

                    },
                    error: function(xhr, textStatus, errorThrown) {
                        $('#output').append("<li>" + textStatus + "</li>");
                    },
                    complete: function() {
                        $('#output').append("<li>Finished Request</li>");
                    }


                });

            }

            function installAddon(part, type)
            {
                var addonName;

                if (part === 'backupModule')
                {
                    addonName = '<?php echo $cart_type; ?>_' + type + '_pos_common_pre_load';
                }

                $.ajax({
                    type: 'GET',
                    cache: false,
                    url: 'add_ons/' + addonName + '.php',
                    dataType: "HTML",
                    data: ({install: "1", verbose_queries: "1"}),
                    beforeSend: function()
                    {
                        $('#response').html("<h4>Call tool " + part + "</h4>");

                    },
                    success: function(data) {
                        $('#response').append(data);

                    },
                    error: function(xhr, textStatus, errorThrown) {
                        $('#output').append("<li>" + textStatus + "</li>");
                    },
                    complete: function() {
                        $('#output').append("<li>Finished Request</li>");
                    }


                });

            }

            function doClear()
            {
                $('#response').html('');
            }

            function log_message(message)
            {
                $('#response').append(message + '<br />');
            }

            function add_progress_bar(query)
            {
                if ($(query + " div#progressor").size() == 0)
                {
                    $(query).append('<div style="border:1px solid #ccc; width:100%; height:20px; overflow:auto; background:#eee;">\
        <div id="progressor" style="background:#07c; width:0%; height:100%;"></div>\
    </div>');
                }
            }

            function ajax_stream(url)
            {
					url += "&_" + +new Date;
                if (!window.XMLHttpRequest)
                {
                    log_message("Your browser does not support the native XMLHttpRequest object.");
                    return;
                }

                try
                {
                    window.xhr = new XMLHttpRequest();
                    xhr.previous_text = '';

                    //xhr.onload = function() { log_message("[XHR] Done. responseText: <i>" + xhr.responseText + "</i>"); };
                    xhr.onerror = function() {
                        log_message("[XHR] Fatal Error.");
                    };
                    xhr.onreadystatechange = function()
                    {
                        try
                        {
                            if (xhr.readyState > 3)
                            {
                                var new_response = xhr.responseText.substring(xhr.previous_text.length);
                                //var result = JSON.parse( new_response );
                                //log_message(result.message);
                                //todo make finish these.
                                log_message(new_response);

                                //update the progressbar
                                // document.getElementById('progressor').style.width = result.progress + "%";
                                xhr.previous_text = xhr.responseText;
                            }
                        }
                        catch (e)
                        {
                            //log_message("<b>[XHR] Exception: " + e + "</b>");
                        }


                    };


                    xhr.open("GET", url, true);
                    xhr.send("Making request...");
                }
                catch (e)
                {
                    log_message("<b>[XHR] Exception: " + e + "</b>");
                }
            }



            $(function() {
                $('#file').change(function() {
                    $(this).siblings('.text').text(this.value || 'Nothing selected')
                });
            })

        </script>
        <style>
            #loading-mask {
                color: #D85909;
                font-size: 1.1em;
                font-weight: bold;
                opacity: 0.8;
                position: absolute;
                text-align: center;
                z-index: 500;
            }

            #loading-mask .loader {
                background: none repeat scroll 0 0 #FFF4E9;
                border: 2px solid #F1AF73;
                color: #D85909;
                font-weight: bold;
                left: 50%;
                margin-left: -105px;
                padding: 15px 30px;
                position: fixed;
                text-align: center;
                top: 45%;
                width: 150px;
                z-index: 1000;
            }



            div#main {
                margin-left: 30vw;

            }
            div.steps {
                float: right;
                width: 58%;
                background-color: lightgrey;
            }
            div.result {
                float: left;
                width: 85%;
                margin-left: -30vw;
                background-color: lightyellow;
            }
            #cleared {
                clear: both;
            }
            body{
                background-color:whitesmoke;
            }

            div#response{
                height: 85vh;
				max-width: 100%;
                overflow-y: scroll;
            }
			
			.pure-table {
    border: 1px solid #cbcbcb;
    border-collapse: collapse;
    border-spacing: 0;
    empty-cells: show;
}
.pure-table caption {
    color: #000;
    font: italic 85%/1 arial,sans-serif;
    padding: 1em 0;
    text-align: center;
}
.pure-table td, .pure-table th {
    border-left: 1px solid #cbcbcb;
    border-width: 0 0 0 1px;
    font-size: 12px;
    margin: 0;
    overflow: visible;
    padding: 0.5em 1em;
}
.pure-table td:first-child, .pure-table th:first-child {
    border-left-width: 0;
}
.pure-table thead {
    background-color: #e0e0e0;
    color: #000;
    text-align: left;
    vertical-align: bottom;
}
.pure-table td {
    background-color: white;
}
.pure-table-odd td {
    background-color: #f2f2f2;
}
.pure-table-striped tr:nth-child(2n-1) td {
    background-color: #f2f2f2;
}
.pure-table-bordered td {
    border-bottom: 1px solid #cbcbcb;
}
.pure-table-bordered tbody > tr:last-child > td {
    border-bottom-width: 0;
}
.pure-table-horizontal td, .pure-table-horizontal th {
    border-bottom: 1px solid #cbcbcb;
    border-width: 0 0 1px;
}

 .pure-table th {
	 background-color:grey;
 }

.pure-table-horizontal tbody > tr:last-child > td {
    border-bottom-width: 0;
}


        </style>
    </head>
    <body>
        <div id="loading-mask" style="display:none">
            <p id="loading_mask_loader" class="loader">
                <img alt="Loading..." src="<?php echo $loader_image; ?>">
                <br>
                Please wait...
            </p>
        </div>
        <h3><img src="<?php echo $rdi_image; ?>" height="30" >Install Scripts <?php echo $pos_type ?></h3>
		<b>Status</b>
		<em id="status"></em>
        <div id="main">
            <div class="steps">
                <fieldset >
                    <legend>Options</legend>
                    <input type='checkbox' id='verbose_queries' value='on' /> Verbose Queries
                </fieldset><br>

                <?php if ($install_scripts): ?>
                    <fieldset id="step1">
                        <legend>Initial Install</legend>
                        <p>
                            <button onclick="loadInstallScripts('init', '<?php echo $pos_type ?>')">Step 1</button><em>Add folder, user. Check for Prefix, entity type and get admin link. </em><br>
                            <!--<button onclick="callUrlWithUpdates('install_scripts_<?php echo $pos_type ?>.php?verbose_queries=0&action=init','fieldset#step1')">Step 1</button><em>Add folder, user. Check for Prefix, entity type and get admin link. </em><br>-->
                        </p>
                    </fieldset>

                    <br>
                    <fieldset id="step2">
                        <legend>Main Install</legend>
                        <p><em>These should only be run once. Running again can cause errors.</em></p>
                        <p>
                            <button onclick="callUrlWithUpdates('install_scripts_<?php echo $pos_type_install ?>.php?verbose_queries=0&action=Tables', 'fieldset#step2')">Step 2</button><em>Adds <?php echo $pos_name; ?> Tables.</em><br>
                            <button onclick="callUrlWithUpdates('install_scripts_<?php echo $pos_type_install ?>.php?verbose_queries=0&action=Tables&test_install=1', 'fieldset#step2b')">Step 2b</button><em>Tests <?php echo $pos_name; ?> Tables.</em><br>
                            <!-- This needs to be able to select the options on install. color/size.. etc-->
                            <button onclick="loadInstallScripts('installAttributes', '<?php echo $pos_type ?>')">Step 3</button><em>Adds <?php echo $cart_type; ?> Attributes. (related_id, related_parent_id, itemnum, size, rdi_avail, rdi_last_update, etc..)</em><br>
                            <button onclick="loadInstallScripts('alterMagentoTables', '<?php echo $pos_type ?>')">Step 4</button><em>Alters <?php echo $cart_type; ?>'s tables.</em><br>
                        </p>

                    </fieldset ><br>
                <?php endif; ?>

                <fieldset >
                    <legend>Transfer Data and Load Staging</legend>
                    <p>
                        <?php if ($pos_type == 'rpro9'): ?>
                        <h4>Step 5</h4><em>Copy XML to the 'in' folder. Click both of these to upload.</em><br>
                        <button onclick="callUpload('styles')">Products</button><button onclick="callUpload('catalog')">Category</button><br>
                        <div class="custom-file">
                            <span class="text">Nothing selected</span>
                            <span class="button">Choose a file</span>
                            <input type="file" id="file" />
                        </div>
                        <em><?php echo dirname($_SERVER['SCRIPT_URI']); ?>/</em><br><br>
                        <b>Confirm Data arrived</b><br>
                        <button onclick="loadInstallScripts('rproStyleCount', '<?php echo $pos_type ?>')">Styles</button>
                        <button onclick="loadInstallScripts('rproCategoryCount', '<?php echo $pos_type ?>')">Categories</button><br>
                        <em>Count will appear in the response.</em><br><br>

                    <?php elseif ($pos_type == 'rpro8'): ?>
                        <b>  Step 5: </b> <em>   Exchange the N and X mail bags only!</em><br>
                        <em><?php echo dirname($_SERVER['SCRIPT_URI']); ?>/</em><br><br>
                        <b>Confirm Data arrived</b><br>
                        <button onclick="loadInstallScripts('rproStyleCount', '<?php echo $pos_type ?>')">Styles</button>
                        <button onclick="loadInstallScripts('rproCategoryCount', '<?php echo $pos_type ?>')">Categories</button>
                        <button onclick="loadInstallScripts('rproUpsellCount', '<?php echo $pos_type ?>')">Upsells</button><br>
                        <em>Count will appear in the response.</em><br><br>
                        <button onclick="callTools('unzip_G_mailbag')">Step 6</button><em>Copy G Mailbag to 'in' folder. Click to unzip.</em><br>


                    <?php elseif ($pos_type == 'rpro4web'): ?>
                        <b>  Step 5: </b> <em>   Copy in the ecstyle_ .xml into the in folder.</em><br>
                        <em><?php echo dirname($_SERVER['SCRIPT_URI']); ?>/</em><br><br>
                        <b>Confirm Data arrived</b><br>
                        <button onclick="loadInstallScripts('rproStyleCount', '<?php echo $pos_type ?>')">Styles</button>

                    <?php elseif ($pos_type == 'cp'): ?>
                        <b>  Step 5: </b> <em>   Exchange the N and X mail bags only!</em><br>
                        <button onclick="loadInstallScripts('rproStyleCount', '<?php echo $pos_type ?>')">Styles</button>
                        <button onclick="loadInstallScripts('rproCategoryCount', '<?php echo $pos_type ?>')">Categories</button>
                        <button onclick="loadInstallScripts('rproUpsellCount', '<?php echo $pos_type ?>')">Upsells</button><br>
                        <em>Count will appear in the response.</em><br><br>
                        <button onclick="callTools('unzip_G_mailbag')">Step 6</button><em>Copy G Mailbag to 'in' folder. Click to unzip.</em><br>
                    <?php endif; ?>
                    </p>
                </fieldset>



                <br>
                <fieldset >
                    <legend>Create Staging XLSX for mapping.</legend>
                    <h5><input type="checkbox" id="load_product_data" value="on" />Create Product Data</h5>
                    <button onclick="callTools('staging_stats')">Step 7</button><br>
                    <p><h4>Mapping Queries</h4>
                    <pre>
SELECT * FROM rdi_field_mapping;
SELECT * FROM rdi_field_mapping_pos;
                    </pre>
                    </p>
                </fieldset><br>

                <fieldset >
                    <button onclick="installAddon('backupModule', '<?php echo $pos_type ?>')">Step 8</button><em>Install the backups Module.</em><br>
                </fieldset>

                <fieldset >
                    <em>If this is a new install, we can safely move color and manufacturer attributes into the attribute sets with this button</em>
                    <button onclick="callTools('move_color')">Optional </button><em>Move Color and Manufacturer into Default-General Group.</em><br>
                </fieldset>
            </div>

            <div class="result">
                <fieldset class="result"><h4>Script Response</h4><div id="response"></div></fieldset>
			</div>
        </div>
    </body>
    <script>
        $("#loading").hide();
    </script>


</html>