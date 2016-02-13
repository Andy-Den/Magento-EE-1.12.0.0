//----- On document ready -----//
jQuery(document).ready(function(){
	initConfAttrOptionUpdate();
	initAvailableColorWidget();
	initInfoTabs();	
});


//----- Global params and functions -----//
var jqzoomOptions = {
	zoomType: 'reverse',//Values: standard, reverse
	zoomWidth: 374,
	zoomHeight: 327,
	xOffset: 0,
	yOffset: 50,
	imageOpacity: 0.6,
	title : false
	//preloadImages: false //Very important, due to image swapping, preload image will cause problems, load image only when clicked
};

var swapImage = function (aHref, imgSrc) {
	jQuery("#main-image").attr('href', aHref);
	jQuery("#main-image>img").attr('src', imgSrc);
	jQuery("#main-image").jqzoom(jqzoomOptions);
};

var initConfAttrOptionUpdate = function () {
	//==== Update Stock availability/Ship lead time; Update Available color if it's the color select ====//
	jQuery("#product-options-wrapper select.super-attribute-select").change(function () {
		//==== Update Available color ====//
		jQuery(".available-color-list-item[option_id='" + jQuery(this).val() + "']").click();
		//==== Update Stock availability/Ship lead time ====//
		var completeMatchInfo = {};
		var isCompleteMatch = true;
		for(var productId in stockShipWidgetJson){
			if(stockShipWidgetJson.hasOwnProperty(productId) && !isNaN(productId)){
				isCompleteMatch = true;
				var selectElements = jQuery("#product-options-wrapper select.super-attribute-select");
				for(var tempIndex = 0; tempIndex < selectElements.length; tempIndex++){
					selectElement = jQuery(selectElements[tempIndex]);
					if(stockShipWidgetJson[productId].super_attr_info[selectElement.attr('attribute_id')] != selectElement.attr('value')){
						isCompleteMatch = false;
						break;
					}
				}
				if(isCompleteMatch){
					completeMatchInfo = stockShipWidgetJson[productId];
					jQuery("#widget_stock_info").html(completeMatchInfo.stock_info);
					jQuery("#widget_shipping_estimate_info").html(completeMatchInfo.shipping_estimate_info);
					break;
				}
			}
		}
		if(!isCompleteMatch){
			jQuery("#widget_stock_info").html(stockShipWidgetJson.default_stock_info);
			jQuery("#widget_shipping_estimate_info").html(stockShipWidgetJson.default_shipping_estimate_info);
		}
	});
};

var initAvailableColorWidget = function () {
	//==== Available color widgets ====//
	for(spConfigAttrId in spConfig.config.attributes){
		if(spConfig.config.attributes.hasOwnProperty(spConfigAttrId)){
			//Note spConfigAttrId is numeric
			spConfigAttr = spConfig.config.attributes[spConfigAttrId];
			if(spConfigAttr.code == "shoe_color_config"){
				for(var tempIndex = 0; tempIndex < spConfigAttr.options.length; tempIndex ++){
					tempOption = spConfigAttr.options[tempIndex];
					jQuery("#available-color-list").append(
							'<div class="available-color-list-item-container"><li class="available-color-list-item" attr_id="' + spConfigAttrId + '" option_id="' + tempOption.id + '" image_file="' + tempOption.image_file + '" style_number="' + tempOption.style_number + '">'
							+ '<img src="' + tempOption.image_url + '" alt="' + tempOption.image_label + '" class="available-color-image"/>'
							+ '</li></div>'
					);
				}
			}
		}
	}
	jQuery(".available-color-list-item").click(function (){
		attrId = jQuery(this).attr('attr_id');
		optionId = jQuery(this).attr('option_id');
		imageFile = jQuery(this).attr('image_file');
		styleNumber = jQuery(this).attr('style_number');

		//Update style number
		jQuery("#product-manu-style-number").html(styleNumber);
		jQuery(".product-view-media-gallery").parent('li.gallery-image-container').hide();
		jQuery(".product-view-media-gallery").each(function(){
			var reExp = new RegExp(styleNumber, "i");
			if(jQuery(this).attr("image_file").match(reExp)){
				jQuery(this).parent('li.gallery-image-container').show();
			}
		});
		
		//Hand shake to the configurable JS object
		jQuery("#attribute" + attrId).val(optionId);
		spConfig.configureElement(document.getElementById("attribute" + attrId));

		jQuery(".available-color-list-item").removeClass("available-color-list-item-selected");
		jQuery(this).addClass("available-color-list-item-selected");

		//Also try to select from media gallery
		//Note there is always a placeholder image where imageFile="", just in case the image is missing
		jQuery(".product-view-media-gallery[image_file='" + imageFile + "']:first").click();

	});
	//Important, upon page load, this will also trigger jqzoom via the swap image logic!
	jQuery(".available-color-list-item").eq(0).click();
};

var initInfoTabs = function () {
	//==== Toggle tabs ====//
	jQuery(".product-collaterals .product-details-tabs").hide();
	jQuery(".product-collaterals-tabs-head li").click(function(){
		headerIndex = jQuery(".product-collaterals-tabs-head ul li").index(jQuery(this));
		jQuery(".product-collaterals .product-details-tabs").hide();
		jQuery(".product-collaterals .product-details-tabs").eq(headerIndex).show();
		jQuery(".product-collaterals-tabs-head li").removeClass("active-tag");
		jQuery(this).addClass("active-tag");
	});
	jQuery(".product-collaterals-tabs-head li").eq(0).click();
};