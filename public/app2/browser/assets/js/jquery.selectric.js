(function(factory){if(typeof define==='function'&&define.amd){define(['jquery'],factory);}else if(typeof module==='object'&&module.exports){module.exports=function(root,jQuery){if(jQuery===undefined){if(typeof window!=='undefined'){jQuery=require('jquery');}else{jQuery=require('jquery')(root);}}
factory(jQuery);return jQuery;};}else{factory(jQuery);}}(function($){'use strict';var $doc=$(document);var $win=$(window);var pluginName='selectric';var classList='Input Items Open Disabled TempShow HideSelect Wrapper Focus Hover Responsive Above Below Scroll Group GroupLabel';var eventNamespaceSuffix='.sl';var chars=['a','e','i','o','u','n','c','y'];var diacritics=[/[\xE0-\xE5]/g,/[\xE8-\xEB]/g,/[\xEC-\xEF]/g,/[\xF2-\xF6]/g,/[\xF9-\xFC]/g,/[\xF1]/g,/[\xE7]/g,/[\xFD-\xFF]/g];var Selectric=function(element,opts){var _this=this;_this.element=element;_this.$element=$(element);_this.state={multiple:!!_this.$element.attr('multiple'),enabled:false,opened:false,currValue:-1,selectedIdx:-1,highlightedIdx:-1};_this.eventTriggers={open:_this.open,close:_this.close,destroy:_this.destroy,refresh:_this.refresh,init:_this.init};_this.init(opts);};Selectric.prototype={utils:{isMobile:function(){return/android|ip(hone|od|ad)/i.test(navigator.userAgent);},escapeRegExp:function(str){return str.replace(/[.*+?^${}()|[\]\\]/g,'\\$&');},replaceDiacritics:function(str){var k=diacritics.length;while(k--){str=str.toLowerCase().replace(diacritics[k],chars[k]);}
return str;},format:function(f){var a=arguments;return(''+f).replace(/\{(?:(\d+)|(\w+))\}/g,function(s,i,p){return p&&a[1]?a[1][p]:a[i];});},nextEnabledItem:function(selectItems,selected){while(selectItems[selected=(selected+1)%selectItems.length].disabled){}
return selected;},previousEnabledItem:function(selectItems,selected){while(selectItems[selected=(selected>0?selected:selectItems.length)-1].disabled){}
return selected;},toDash:function(str){return str.replace(/([a-z0-9])([A-Z])/g,'$1-$2').toLowerCase();},triggerCallback:function(fn,scope){var elm=scope.element;var func=scope.options['on'+fn];var args=[elm].concat([].slice.call(arguments).slice(1));if($.isFunction(func)){func.apply(elm,args);}
$(elm).trigger(pluginName+'-'+this.toDash(fn),args);},arrayToClassname:function(arr){var newArr=$.grep(arr,function(item){return!!item;});return $.trim(newArr.join(' '));}},init:function(opts){var _this=this;_this.options=$.extend(true,{},$.fn[pluginName].defaults,_this.options,opts);_this.utils.triggerCallback('BeforeInit',_this);_this.destroy(true);if(_this.options.disableOnMobile&&_this.utils.isMobile()){_this.disableOnMobile=true;return;}
_this.classes=_this.getClassNames();var input=$('<input/>',{'class':_this.classes.input,'readonly':_this.utils.isMobile()});var items=$('<div/>',{'class':_this.classes.items,'tabindex':-1});var itemsScroll=$('<div/>',{'class':_this.classes.scroll});var wrapper=$('<div/>',{'class':_this.classes.prefix,'html':_this.options.arrowButtonMarkup});var label=$('<span/>',{'class':'label'});var outerWrapper=_this.$element.wrap('<div/>').parent().append(wrapper.prepend(label),items,input);var hideSelectWrapper=$('<div/>',{'class':_this.classes.hideselect});_this.elements={input:input,items:items,itemsScroll:itemsScroll,wrapper:wrapper,label:label,outerWrapper:outerWrapper};if(_this.options.nativeOnMobile&&_this.utils.isMobile()){_this.elements.input=undefined;hideSelectWrapper.addClass(_this.classes.prefix+'-is-native');_this.$element.on('change',function(){_this.refresh();});}
_this.$element.on(_this.eventTriggers).wrap(hideSelectWrapper);_this.originalTabindex=_this.$element.prop('tabindex');_this.$element.prop('tabindex',-1);_this.populate();_this.activate();_this.utils.triggerCallback('Init',_this);},activate:function(){var _this=this;var hiddenChildren=_this.elements.items.closest(':visible').children(':hidden').addClass(_this.classes.tempshow);var originalWidth=_this.$element.width();hiddenChildren.removeClass(_this.classes.tempshow);_this.utils.triggerCallback('BeforeActivate',_this);_this.elements.outerWrapper.prop('class',_this.utils.arrayToClassname([_this.classes.wrapper,_this.$element.prop('class').replace(/\S+/g,_this.classes.prefix+'-$&'),_this.options.responsive?_this.classes.responsive:'']));if(_this.options.inheritOriginalWidth&&originalWidth>0){_this.elements.outerWrapper.width(originalWidth);}
_this.unbindEvents();if(!_this.$element.prop('disabled')){_this.state.enabled=true;_this.elements.outerWrapper.removeClass(_this.classes.disabled);_this.$li=_this.elements.items.removeAttr('style').find('li');_this.bindEvents();}else{_this.elements.outerWrapper.addClass(_this.classes.disabled);if(_this.elements.input){_this.elements.input.prop('disabled',true);}}
_this.utils.triggerCallback('Activate',_this);},getClassNames:function(){var _this=this;var customClass=_this.options.customClass;var classesObj={};$.each(classList.split(' '),function(i,currClass){var c=customClass.prefix+currClass;classesObj[currClass.toLowerCase()]=customClass.camelCase?c:_this.utils.toDash(c);});classesObj.prefix=customClass.prefix;return classesObj;},setLabel:function(){var _this=this;var labelBuilder=_this.options.labelBuilder;if(_this.state.multiple){var currentValues=$.isArray(_this.state.currValue)?_this.state.currValue:[_this.state.currValue];currentValues=currentValues.length===0?[0]:currentValues;var labelMarkup=$.map(currentValues,function(value){return $.grep(_this.lookupItems,function(item){return item.index===value;})[0];});labelMarkup=$.grep(labelMarkup,function(item){if(labelMarkup.length>1||labelMarkup.length===0){return $.trim(item.value)!=='';}
return item;});labelMarkup=$.map(labelMarkup,function(item){return $.isFunction(labelBuilder)?labelBuilder(item):_this.utils.format(labelBuilder,item);});if(_this.options.multiple.maxLabelEntries){if(labelMarkup.length>=_this.options.multiple.maxLabelEntries+1){labelMarkup=labelMarkup.slice(0,_this.options.multiple.maxLabelEntries);labelMarkup.push($.isFunction(labelBuilder)?labelBuilder({text:'...'}):_this.utils.format(labelBuilder,{text:'...'}));}else{labelMarkup.slice(labelMarkup.length-1);}}
_this.elements.label.html(labelMarkup.join(_this.options.multiple.separator));}else{var currItem=_this.lookupItems[_this.state.currValue];_this.elements.label.html($.isFunction(labelBuilder)?labelBuilder(currItem):_this.utils.format(labelBuilder,currItem));}},populate:function(){var _this=this;var $options=_this.$element.children();var $justOptions=_this.$element.find('option');var $selected=$justOptions.filter(':selected');var selectedIndex=$justOptions.index($selected);var currIndex=0;var emptyValue=(_this.state.multiple?[]:0);if($selected.length>1&&_this.state.multiple){selectedIndex=[];$selected.each(function(){selectedIndex.push($(this).index());});}
_this.state.currValue=(~selectedIndex?selectedIndex:emptyValue);_this.state.selectedIdx=_this.state.currValue;_this.state.highlightedIdx=_this.state.currValue;_this.items=[];_this.lookupItems=[];if($options.length){$options.each(function(i){var $elm=$(this);if($elm.is('optgroup')){var optionsGroup={element:$elm,label:$elm.prop('label'),groupDisabled:$elm.prop('disabled'),items:[]};$elm.children().each(function(i){var $elm=$(this);optionsGroup.items[i]=_this.getItemData(currIndex,$elm,optionsGroup.groupDisabled||$elm.prop('disabled'));_this.lookupItems[currIndex]=optionsGroup.items[i];currIndex++;});_this.items[i]=optionsGroup;}else{_this.items[i]=_this.getItemData(currIndex,$elm,$elm.prop('disabled'));_this.lookupItems[currIndex]=_this.items[i];currIndex++;}});_this.setLabel();_this.elements.items.append(_this.elements.itemsScroll.html(_this.getItemsMarkup(_this.items)));}},getItemData:function(index,$elm,isDisabled){var _this=this;return{index:index,element:$elm,value:$elm.val(),className:$elm.prop('class'),text:$elm.html(),slug:$.trim(_this.utils.replaceDiacritics($elm.html())),alt:$elm.attr('data-alt'),selected:$elm.prop('selected'),disabled:isDisabled};},getItemsMarkup:function(items){var _this=this;var markup='<ul>';if($.isFunction(_this.options.listBuilder)&&_this.options.listBuilder){items=_this.options.listBuilder(items);}
$.each(items,function(i,elm){if(elm.label!==undefined){markup+=_this.utils.format('<ul class="{1}"><li class="{2}">{3}</li>',_this.utils.arrayToClassname([_this.classes.group,elm.groupDisabled?'disabled':'',elm.element.prop('class')]),_this.classes.grouplabel,elm.element.prop('label'));$.each(elm.items,function(i,elm){markup+=_this.getItemMarkup(elm.index,elm);});markup+='</ul>';}else{markup+=_this.getItemMarkup(elm.index,elm);}});return markup+'</ul>';},getItemMarkup:function(index,itemData){var _this=this;var itemBuilder=_this.options.optionsItemBuilder;var filteredItemData={value:itemData.value,text:itemData.text,slug:itemData.slug,index:itemData.index};return _this.utils.format('<li data-index="{1}" class="{2}">{3}</li>',index,_this.utils.arrayToClassname([itemData.className,index===_this.items.length-1?'last':'',itemData.disabled?'disabled':'',itemData.selected?'selected':'']),$.isFunction(itemBuilder)?_this.utils.format(itemBuilder(itemData,this.$element,index),itemData):_this.utils.format(itemBuilder,filteredItemData));},unbindEvents:function(){var _this=this;_this.elements.wrapper.add(_this.$element).add(_this.elements.outerWrapper).add(_this.elements.input).off(eventNamespaceSuffix);},bindEvents:function(){var _this=this;_this.elements.outerWrapper.on('mouseenter'+eventNamespaceSuffix+' mouseleave'+eventNamespaceSuffix,function(e){$(this).toggleClass(_this.classes.hover,e.type==='mouseenter');if(_this.options.openOnHover){clearTimeout(_this.closeTimer);if(e.type==='mouseleave'){_this.closeTimer=setTimeout($.proxy(_this.close,_this),_this.options.hoverIntentTimeout);}else{_this.open();}}});_this.elements.wrapper.on('click'+eventNamespaceSuffix,function(e){_this.state.opened?_this.close():_this.open(e);});if(!(_this.options.nativeOnMobile&&_this.utils.isMobile())){_this.$element.on('focus'+eventNamespaceSuffix,function(){_this.elements.input.focus();});_this.elements.input.prop({tabindex:_this.originalTabindex,disabled:false}).on('keydown'+eventNamespaceSuffix,$.proxy(_this.handleKeys,_this)).on('focusin'+eventNamespaceSuffix,function(e){_this.elements.outerWrapper.addClass(_this.classes.focus);_this.elements.input.one('blur',function(){_this.elements.input.blur();});if(_this.options.openOnFocus&&!_this.state.opened){_this.open(e);}}).on('focusout'+eventNamespaceSuffix,function(){_this.elements.outerWrapper.removeClass(_this.classes.focus);}).on('input propertychange',function(){var val=_this.elements.input.val();var searchRegExp=new RegExp('^'+_this.utils.escapeRegExp(val),'i');clearTimeout(_this.resetStr);_this.resetStr=setTimeout(function(){_this.elements.input.val('');},_this.options.keySearchTimeout);if(val.length){$.each(_this.items,function(i,elm){if(elm.disabled){return;}
if(searchRegExp.test(elm.text)||searchRegExp.test(elm.slug)){_this.highlight(i);return;}
if(!elm.alt){return;}
var altItems=elm.alt.split('|');for(var ai=0;ai<altItems.length;ai++){if(!altItems[ai]){break;}
if(searchRegExp.test(altItems[ai].trim())){_this.highlight(i);return;}}});}});}
_this.$li.on({mousedown:function(e){e.preventDefault();e.stopPropagation();},click:function(){_this.select($(this).data('index'));return false;}});},handleKeys:function(e){var _this=this;var key=e.which;var keys=_this.options.keys;var isPrevKey=$.inArray(key,keys.previous)>-1;var isNextKey=$.inArray(key,keys.next)>-1;var isSelectKey=$.inArray(key,keys.select)>-1;var isOpenKey=$.inArray(key,keys.open)>-1;var idx=_this.state.highlightedIdx;var isFirstOrLastItem=(isPrevKey&&idx===0)||(isNextKey&&(idx+1)===_this.items.length);var goToItem=0;if(key===13||key===32){e.preventDefault();}
if(isPrevKey||isNextKey){if(!_this.options.allowWrap&&isFirstOrLastItem){return;}
if(isPrevKey){goToItem=_this.utils.previousEnabledItem(_this.lookupItems,idx);}
if(isNextKey){goToItem=_this.utils.nextEnabledItem(_this.lookupItems,idx);}
_this.highlight(goToItem);}
if(isSelectKey&&_this.state.opened){_this.select(idx);if(!_this.state.multiple||!_this.options.multiple.keepMenuOpen){_this.close();}
return;}
if(isOpenKey&&!_this.state.opened){_this.open();}},refresh:function(){var _this=this;_this.populate();_this.activate();_this.utils.triggerCallback('Refresh',_this);},setOptionsDimensions:function(){var _this=this;var hiddenChildren=_this.elements.items.closest(':visible').children(':hidden').addClass(_this.classes.tempshow);var maxHeight=_this.options.maxHeight;var itemsWidth=_this.elements.items.outerWidth();var wrapperWidth=_this.elements.wrapper.outerWidth()-(itemsWidth-_this.elements.items.width());if(!_this.options.expandToItemText||wrapperWidth>itemsWidth){_this.finalWidth=wrapperWidth;}else{_this.elements.items.css('overflow','scroll');_this.elements.outerWrapper.width(9e4);_this.finalWidth=_this.elements.items.width();_this.elements.items.css('overflow','');_this.elements.outerWrapper.width('');}
_this.elements.items.width(_this.finalWidth).height()>maxHeight&&_this.elements.items.height(maxHeight);hiddenChildren.removeClass(_this.classes.tempshow);},isInViewport:function(){var _this=this;if(_this.options.forceRenderAbove===true){_this.elements.outerWrapper.addClass(_this.classes.above);}else if(_this.options.forceRenderBelow===true){_this.elements.outerWrapper.addClass(_this.classes.below);}else{var scrollTop=$win.scrollTop();var winHeight=$win.height();var uiPosX=_this.elements.outerWrapper.offset().top;var uiHeight=_this.elements.outerWrapper.outerHeight();var fitsDown=(uiPosX+uiHeight+_this.itemsHeight)<=(scrollTop+winHeight);var fitsAbove=(uiPosX-_this.itemsHeight)>scrollTop;var renderAbove=!fitsDown&&fitsAbove;var renderBelow=!renderAbove;_this.elements.outerWrapper.toggleClass(_this.classes.above,renderAbove);_this.elements.outerWrapper.toggleClass(_this.classes.below,renderBelow);}},detectItemVisibility:function(index){var _this=this;var $filteredLi=_this.$li.filter('[data-index]');if(_this.state.multiple){index=($.isArray(index)&&index.length===0)?0:index;index=$.isArray(index)?Math.min.apply(Math,index):index;}
var liHeight=$filteredLi.eq(index).outerHeight();var liTop=$filteredLi[index].offsetTop;var itemsScrollTop=_this.elements.itemsScroll.scrollTop();var scrollT=liTop+liHeight*2;_this.elements.itemsScroll.scrollTop(scrollT>itemsScrollTop+_this.itemsHeight?scrollT-_this.itemsHeight:liTop-liHeight<itemsScrollTop?liTop-liHeight:itemsScrollTop);},open:function(e){var _this=this;if(_this.options.nativeOnMobile&&_this.utils.isMobile()){return false;}
_this.utils.triggerCallback('BeforeOpen',_this);if(e){e.preventDefault();if(_this.options.stopPropagation){e.stopPropagation();}}
if(_this.state.enabled){_this.setOptionsDimensions();$('.'+_this.classes.hideselect,'.'+_this.classes.open).children()[pluginName]('close');_this.state.opened=true;_this.itemsHeight=_this.elements.items.outerHeight();_this.itemsInnerHeight=_this.elements.items.height();_this.elements.outerWrapper.addClass(_this.classes.open);_this.elements.input.val('');if(e&&e.type!=='focusin'){_this.elements.input.focus();}
setTimeout(function(){$doc.on('click'+eventNamespaceSuffix,$.proxy(_this.close,_this)).on('scroll'+eventNamespaceSuffix,$.proxy(_this.isInViewport,_this));},1);_this.isInViewport();if(_this.options.preventWindowScroll){$doc.on('mousewheel'+eventNamespaceSuffix+' DOMMouseScroll'+eventNamespaceSuffix,'.'+_this.classes.scroll,function(e){var orgEvent=e.originalEvent;var scrollTop=$(this).scrollTop();var deltaY=0;if('detail'in orgEvent){deltaY=orgEvent.detail*-1;}
if('wheelDelta'in orgEvent){deltaY=orgEvent.wheelDelta;}
if('wheelDeltaY'in orgEvent){deltaY=orgEvent.wheelDeltaY;}
if('deltaY'in orgEvent){deltaY=orgEvent.deltaY*-1;}
if(scrollTop===(this.scrollHeight-_this.itemsInnerHeight)&&deltaY<0||scrollTop===0&&deltaY>0){e.preventDefault();}});}
_this.detectItemVisibility(_this.state.selectedIdx);_this.highlight(_this.state.multiple?-1:_this.state.selectedIdx);_this.utils.triggerCallback('Open',_this);}},close:function(){var _this=this;_this.utils.triggerCallback('BeforeClose',_this);$doc.off(eventNamespaceSuffix);_this.elements.outerWrapper.removeClass(_this.classes.open);_this.state.opened=false;_this.utils.triggerCallback('Close',_this);},change:function(){var _this=this;_this.utils.triggerCallback('BeforeChange',_this);if(_this.state.multiple){$.each(_this.lookupItems,function(idx){_this.lookupItems[idx].selected=false;_this.$element.find('option').prop('selected',false);});$.each(_this.state.selectedIdx,function(idx,value){_this.lookupItems[value].selected=true;_this.$element.find('option').eq(value).prop('selected',true);});_this.state.currValue=_this.state.selectedIdx;_this.setLabel();_this.utils.triggerCallback('Change',_this);}else if(_this.state.currValue!==_this.state.selectedIdx){_this.$element.prop('selectedIndex',_this.state.currValue=_this.state.selectedIdx).data('value',_this.lookupItems[_this.state.selectedIdx].text);_this.setLabel();_this.utils.triggerCallback('Change',_this);}},highlight:function(index){var _this=this;var $filteredLi=_this.$li.filter('[data-index]').removeClass('highlighted');_this.utils.triggerCallback('BeforeHighlight',_this);if(index===undefined||index===-1||_this.lookupItems[index].disabled){return;}
$filteredLi.eq(_this.state.highlightedIdx=index).addClass('highlighted');_this.detectItemVisibility(index);_this.utils.triggerCallback('Highlight',_this);},select:function(index){var _this=this;var $filteredLi=_this.$li.filter('[data-index]');_this.utils.triggerCallback('BeforeSelect',_this,index);if(index===undefined||index===-1||_this.lookupItems[index].disabled){return;}
if(_this.state.multiple){_this.state.selectedIdx=$.isArray(_this.state.selectedIdx)?_this.state.selectedIdx:[_this.state.selectedIdx];var hasSelectedIndex=$.inArray(index,_this.state.selectedIdx);if(hasSelectedIndex!==-1){_this.state.selectedIdx.splice(hasSelectedIndex,1);}else{_this.state.selectedIdx.push(index);}
$filteredLi.removeClass('selected').filter(function(index){return $.inArray(index,_this.state.selectedIdx)!==-1;}).addClass('selected');}else{$filteredLi.removeClass('selected').eq(_this.state.selectedIdx=index).addClass('selected');}
if(!_this.state.multiple||!_this.options.multiple.keepMenuOpen){_this.close();}
_this.change();_this.utils.triggerCallback('Select',_this,index);},destroy:function(preserveData){var _this=this;if(_this.state&&_this.state.enabled){_this.elements.items.add(_this.elements.wrapper).add(_this.elements.input).remove();if(!preserveData){_this.$element.removeData(pluginName).removeData('value');}
_this.$element.prop('tabindex',_this.originalTabindex).off(eventNamespaceSuffix).off(_this.eventTriggers).unwrap().unwrap();_this.state.enabled=false;}}};$.fn[pluginName]=function(args){return this.each(function(){var data=$.data(this,pluginName);if(data&&!data.disableOnMobile){(typeof args==='string'&&data[args])?data[args]():data.init(args);}else{$.data(this,pluginName,new Selectric(this,args));}});};$.fn[pluginName].defaults={onChange:function(elm){$(elm).change();},maxHeight:300,keySearchTimeout:500,arrowButtonMarkup:'<b class="button">&#x25be;</b>',disableOnMobile:false,nativeOnMobile:true,openOnFocus:true,openOnHover:false,hoverIntentTimeout:500,expandToItemText:false,responsive:false,preventWindowScroll:true,inheritOriginalWidth:false,allowWrap:true,forceRenderAbove:false,forceRenderBelow:false,stopPropagation:true,optionsItemBuilder:'{text}',labelBuilder:'{text}',listBuilder:false,keys:{previous:[37,38],next:[39,40],select:[9,13,27],open:[13,32,37,38,39,40],close:[9,27]},customClass:{prefix:pluginName,camelCase:false},multiple:{separator:', ',keepMenuOpen:true,maxLabelEntries:false}};}));