/* global cbj: false */
/* global com_configbox: true */
/* global Kenedo: true */
/* global tinyMCE: true */
/* global CodeMirror: true */
/* global alert: false */
/* global confirm: false */
/* global alert: false */
/* global console: false */
/* jshint -W116 */

cbj(document).ready(function(){
	
	// If the user is authorized, init quickEdit for in-place editing
	if (window.com_configbox && window.com_configbox.canQuickEdit) {	
		setQuickEdit();
	}
	
	// Hide and show functions for configuration page quickedit functions
	cbj('.show-edit-buttons').click(function(){
		cbj(this).closest('.quick-edit-buttons-fader').fadeOut(200,function(){
			cbj(this).closest('.quick-edit-buttons').find('.quick-edit-buttons-content').fadeIn(200);
		});
	});
	
	cbj('.hide-edit-buttons').click(function(){

		cbj(this).closest('.quick-edit-buttons').find('.quick-edit-buttons-content').fadeOut(200,function(){
			cbj(this).closest('.quick-edit-buttons').find('.quick-edit-buttons-fader').fadeIn(200);
		});
		
	});
	
});

function setQuickEdit() {
	
	// Check if we're at least editors and we got a view variable
	if (window.com_configbox.canQuickEdit == false || typeof (window.com_configbox.view) == 'undefined') {
		return;
	}
	
	if (window.com_configbox.view == 'configuratorpage') {
		
		if (typeof(window.com_configbox.elements) != 'undefined' && window.com_configbox.elements != null) {
			cbj.each(window.com_configbox.elements, function() {
				
				quickEdit('elementtitle',{id:this.id});
				quickEdit('elementdesc',{id:this.id});
				
				if (typeof (this.options) == 'object' && this.options != null) {
					cbj.each(this.options, function() {
						
						quickEdit('xrefprice',{id:this.option_id});
						quickEdit('xreftitle',{id:this.option_id});
						quickEdit('xrefdesc',{id:this.option_id});
						
					});
				}
				
			});
		}
		
		quickEdit('pagetitle',{id:window.com_configbox.page_id});
		quickEdit('pagedescription',{id:window.com_configbox.page_id});
	}
	else if (window.com_configbox.view == 'productlisting') {

		cbj.each(window.com_configbox.products, function() {
			quickEdit('producttitle',this);
			quickEdit('productdesc',this);
			
		});
		
	}
	else if (window.com_configbox.view == 'product') {
		
		quickEdit('prodpageproducttitle',{id:window.com_configbox.prod_id});
		quickEdit('prodpageproductdesc',{id:window.com_configbox.prod_id});
		quickEdit('prodpageproductlongdesc',{id:window.com_configbox.prod_id});
			
	}
	
}

function quickEdit(itemtype, item) {
	
	// Check if we're at least editors and we got a view variable
	if (window.com_configbox.canQuickEdit == false || typeof (window.com_configbox.view) == 'undefined') {
		return;
	}
	
	if (!itemtype) return false;
	
	var subject = {};
	
	switch (itemtype) {
		
		case 'prodpageproductdesc':
			
			subject.selector = ('.listing-product-description');
			subject.fieldname = 'description-' + window.com_configbox.langTag;
			subject.entity = 'adminproducts';
			subject.inputtype = 'textarea';
			subject.htmlarea = 1;
			break;
		
		case 'prodpageproductlongdesc':
			
			subject.selector = ('.product-description');
			subject.fieldname = 'longdescription-' + window.com_configbox.langTag;
			subject.entity = 'adminproducts';
			subject.inputtype = 'textarea';
			subject.htmlarea = 1;
			break;
		
		case 'prodpageproducttitle':
			
			subject.selector = ('.listing-product-title, .componentheading');
			subject.fieldname = 'title-' + window.com_configbox.langTag;
			subject.entity = 'adminproducts';
			subject.alsoputin = '.componentheading, .product_title';
			break;
		
		case 'productdesc':
			
			subject.selector = ('.product-id-'+ item.id + ' .listing-product-description');
			subject.fieldname = 'description-' + window.com_configbox.langTag;
			subject.entity = 'adminproducts';
			subject.inputtype = 'textarea';
			subject.htmlarea = 1;
			break;
		
		case 'pagetitle':
			
			subject.selector = ('#com_configbox .configurator-page-title span');
			subject.fieldname = 'title-' + window.com_configbox.langTag;
			subject.entity = 'adminpages';
			break;
		
		case 'pagedescription':
			
			subject.selector = ('.configurator-page-description');
			subject.fieldname = 'description-' + window.com_configbox.langTag;
			subject.entity = 'adminpages';
			subject.htmlarea = 1;
			break;
			
		case 'elementtitle': 
			
			subject.selector = ('#elementwrapper-'+ item.id +' .element-title, #elementwrapper-'+ item.id +' .configbox-label-text');
			subject.fieldname = 'title-' + window.com_configbox.langTag;
			subject.entity = 'adminelements';
			break;
		
		case 'elementdesc': 
			
			subject.selector = ('#elementwrapper-'+ item.id +' .element-description');
			subject.fieldname = 'description-' + window.com_configbox.langTag;
			subject.entity = 'adminelements';
			subject.inputtype = 'textarea';
			subject.htmlarea = 1;
			break;
			
		case 'xrefdesc':
			subject.selector = '#xrefwrapper-' + item.id + ' .xref-description';
			subject.fieldname = 'description-' + window.com_configbox.langTag;
			subject.entity = 'adminoptions';
			subject.inputtype = 'textarea';
			subject.htmlarea = 1;
			subject.onEdit = function() {
				return cbj('.tool-tip .tool-text span').html();
			};
			break;
		
	}
	
	// Assign the Double Click event to the element
	cbj(subject.selector).dblclick(function(){
		
		subject.tag = cbj(this);
		subject.savename = itemtype +'-save-'+ item.id;
		subject.cancelname = itemtype +'-cancel-'+ item.id;
		subject.inputname = itemtype +'-input-'+ item.id;
		subject.containerid = itemtype +'-container-'+ item.id;
		
		var followlink;

		if (this.tagName == 'A') {
			followlink = '<a href="'+this.href+'"> ' + window.com_configbox.lang.followlink + '</a>';
		} 
		else {
			followlink = '';
		}

		var inputfield;

		// Prepare the quickedit input field (determine whether to use textbox or textarea)
		if (typeof (subject.inputtype) != 'undefined' && subject.inputtype == 'textarea') {
			inputfield = '<textarea class="mceEditor" id="'+subject.inputname+'" name="'+subject.inputname+'" style="width:100%;height:200px" rows="20" cols="40">' + subject.tag.html() + '</textarea>';
		} 
		else {
			inputfield = '<input style="margin-right:10px" id="'+subject.inputname+'" type="text" name="'+subject.inputname+'" value="' + subject.tag.html().replace(/"/g, '&quot;') + '" />';
		}

		var savebutton, cancelbutton;

		// Add the edit controls
		if (typeof (subject.inputtype) != 'undefined' && subject.inputtype == 'textarea') {
			savebutton = '<a class="quick-edit-textarea-button quick-edit-save" id="'+subject.savename+'"><span class="fa fa-check-circle">'+com_configbox.lang.save+'</span></a>';
			cancelbutton = '<a class="quick-edit-textarea-button quick-edit-cancel" id="'+subject.cancelname+'"><span class="fa fa-times-circle">'+com_configbox.lang.cancel+'</span></a>';

			subject.tag.after('<div style="clear:both" id="'+ subject.containerid + '">' + savebutton + cancelbutton + followlink + '<div class="clear"></div>' +   inputfield + '</div>');
		}
		else {
			savebutton = '<a class="quick-edit-textfield-button quick-edit-save" id="'+subject.savename+'"><span class="fa fa-check-circle"></span></a>';
			cancelbutton = '<a class="quick-edit-textfield-button quick-edit-cancel" id="'+subject.cancelname+'"><span class="fa fa-times-circle"></span></a>';
		
			subject.tag.after('<span id="'+ subject.containerid + '">'+ inputfield + savebutton + cancelbutton + followlink + '</span>');
		}
		
		// Invoke tinyMCE
		if (typeof (subject.htmlarea) != 'undefined' && subject.htmlarea == 1) {

			// Init HTML editors
			tinyMCE.init({
				convert_urls : false,
				document_base_url : com_configbox.urlBase,
				documentBaseURL : com_configbox.urlBase,
				baseURL : com_configbox.tinyMceBaseUrl,
				suffix : '.min',

				// General options
				selector	: '#'+subject.inputname,
				theme 		: "modern",
				plugins		: [
					"advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
					"searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
					"save table contextmenu directionality emoticons template paste textcolor"
				],
				toolbar		: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | l      ink image | print preview media fullpage | forecolor backcolor emoticons",
				content_css : "",
				template_external_list_url 	: "js/template_list.js",
				external_link_list_url 		: "js/link_list.js",
				external_image_list_url 	: "js/image_list.js",
				media_external_list_url 	: "js/media_list.js"
			});
			
		}
		else {

			// Adjust the input element to the subject's style
			
			cbj('#'+subject.inputname).css('font-size',subject.tag.css('font-size'));
			cbj('#'+subject.inputname+":text").css('border','0px solid black');
			
			cbj('#'+subject.inputname).addClass('quickedit-input');
			cbj('#'+subject.inputname).css('color',subject.tag.css('color'));
			cbj('#'+subject.inputname).css('font-weight',subject.tag.css('font-weight'));
			cbj('#'+subject.inputname).css('font-family',subject.tag.css('font-family'));
			cbj('#'+subject.inputname).css('margin','0px');
			cbj('#'+subject.containerid).css('display',subject.tag.css('display'));
			cbj('#'+subject.containerid).css('margin-top',subject.tag.css('margin-top'));
			cbj('#'+subject.containerid).css('margin-bottom',parseInt(subject.tag.css('margin-bottom')) - 1 + 'px');
			cbj('#'+subject.containerid).css('margin-left', parseInt(subject.tag.css('margin-left')) - 1 + 'px');
			cbj('#'+subject.containerid).css('margin-right',subject.tag.css('margin-right'));
			cbj('#'+subject.containerid).css('float',subject.tag.css('float'));
			cbj('#'+subject.inputname+":text").css('width',subject.tag.width() + 20);
			cbj('#'+subject.inputname+":text").css('background','none');
			
			cbj('#'+subject.inputname+":text").css('width','auto');
		}
				
		// If we have an input modifier, fire it
		if (typeof (subject.onEdit) != 'undefined') {
			cbj('#'+subject.inputname).val(subject.onEdit(subject.tag.html()));
		}
		
		cbj('#'+subject.inputname).focus().select();
		
		// Cancel Button Event
		cbj('#'+subject.cancelname).click(function() {
			cbj('#'+subject.containerid).remove();
			subject.tag.show();
		});
		
		// Hide the subject
		subject.tag.hide();

		// Trigger click functions on enter and escape
		cbj('#'+subject.inputname).keydown(function(event){
			
			if (event.keyCode == 27) cbj('#'+subject.cancelname).trigger('click');
			// We'll do wicked stuff to dynamically adjust the textbox width according to content
			if (this.type == 'text') {
				if (event.keyCode == 13) cbj('#'+subject.savename).trigger('click');
				else if (event.keyCode == 8){
					cbj(this).css('width', cbj(this).width() - (parseFloat(cbj(this).css('font-size')) * 0.5));
				} else if ((event.keyCode < 37 || event.keyCode > 40) &&  event.keyCode != 16) {
					cbj(this).css('width', cbj(this).width() + (parseFloat(cbj(this).css('font-size')) * 0.5));
				}
			}
		});
		
		// Save Button Event
		cbj('#'+subject.savename).click(function(){
			
			cbj('#'+subject.cancelname).append('<span class="quickeditwait"></span>');
			
			var data = {
				'option':'com_configbox',
				'task':'ajaxStore',
				'format':'raw',
				'controller':subject.entity,
				'id':item.id,
				'lang': window.com_configbox.langSuffix
			};
			
			if (typeof (subject.htmlarea) != 'undefined' && subject.htmlarea == 1) {
				subject.content = tinyMCE.get(subject.inputname).getContent();
			}
			else {
				subject.content = cbj('#'+subject.inputname).val();
			}
						
			data[subject.fieldname] = subject.content;
			
			// Do the request
			cbj.ajax({
				type: "POST",
				url: window.com_configbox.entryFile,
				data: data,
				dataType: "json",
				success: function(msg){
					
					if (msg.status == 1) {

						if (itemtype != 'xrefdesc') {
							// Put the new value in the element tag
							subject.tag.html(subject.content);
							
							if (typeof(subject.alsoputin) != 'undefined') {
								cbj(subject.alsoputin).html(subject.content);
							}
							
						}
						
						// Remove the edit stuff again
						cbj('#'+subject.containerid).remove();
						
						// Show the tag again
						subject.tag.show();
						
					}
					else {
						// Report any errors passed from the backend
						var errormsg = '';
						if (typeof(msg.errors) != 'undefined') {
							
							for (var i in msg.errors) {
								if (msg.errors.hasOwnProperty(i)) {
									if (typeof(msg.errors[i]) == 'string') errormsg += msg.errors[i];
								}
							}
						}
						cbj('#'+subject.containerid + ' .quickeditwait').remove();
						alert(errormsg);
					}

			   }
					
			});
			
		});

		return false;

	});
	
}
