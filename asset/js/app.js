jQuery.fn.clickify = function(target) {
	return this.each(function() {
		$(this).click(function(e) {
			// console.log('clicked '+e.target.nodeName+' #'+$(e.target).attr('id'));
			switch(e.target.nodeName) {
			case 'INPUT':
				if (e.target.type == 'checkbox') {
					return true;
				}
			case 'SELECT':
			case 'TEXTAREA':
				// console.log('-> ignore fields');
				return false;
			case 'LABEL':
				$etl = $('#'+$(e.target).attr('for'));
				$etl.prop('checked',!$etl.prop('checked'));
				return false;
			case 'A':
				var $t = $(e.target);
				// console.log('-> regular link : '+e.target.href+' / '+$t.attr('class'));
				var cl = $t.attr('class');
				if (cl && cl.search(/(ajax|cbox|edit\-link|noblock)/)) {
				} else {
					// $.blockUI();
					window.location.href = e.target.href;
					return false;
				}
				//console.log('-> ajax link : '+e.target.href);
				return true;
			case 'BUTTON':
				// console.log('-> button : return true');
				/*
				if ($el.attr('type') == 'submit') {
					$.blockUI();
				}
				*/
				return true;
			default:
				var el = $(this);
				var $et = $(e.target);
				// 1. check if this is an editable place
				if ($et.hasClass('edit-place')) {
					return true;
				}
				
				// 2. check if target is wrapped by a link
				var ei = $et.closest('a');
				if (ei[0]) {
					// console.log('-> wrapped by a A : '+ei.attr('href'));
					return true;
				}
				// if not, try to find a default link (.click-link)
				ei = el.find('.click-link:visible');
				if (ei[0]) {
					// console.log('-> force click-link');
					$ea = ei;
				} else {
					// console.log('-> auto first link');
					$ea = el.find('a:visible').first();
				}
				/*
				if ($ea.attr('class').search(/(ajax|cbox|edit\-link|noblock)/) == -1) {
					$.blockUI();
				}
				*/
				$ea.click();
				return false;
			}
		});
	});
}

jQuery.fn.ajaxify = function(target) {
	return this.each(function() {
		var $el = $(this);
		if ($el.is('form')) {
			$el.submit(function(e) { 
				e.preventDefault();
				$t = $(this);
				$t.ajaxform({
					u: $t.attr('action'),
					d: $t.serialize(),
					t: $t.attr('target'),
					p: $t.hasClass('append')?true:false
				});
				
			});
			if ($el.hasClass('auto')) {
				$el.submit();
			}
		} else {
			$el.click(function(e) {
				e.preventDefault();
				var $ec = $(this);
				if ($ec.hasClass('confirm')) {
					var m = 'Really delete this item ?';
					var t = $ec.attr('title');
					if (t && !t.search(/.+\?/)) {
						m = t;
					}
					if (!confirm(m)) { // -TODO-TRANSLATE-
						return false;
					}
				}
				$ec.ajaxload({
					t: ($ec.attr('rel')?($ec.attr('rel')):'#content')
				});
			});
		}
	});
}
jQuery.fn.ajaxload = function (opts, cbfn) {
	//$.blockUI();
	
	var defaults = {
	   u: this.attr('href'),
	   d: [],
	   t: '',
	   c: '',
	   a: true
	};
	var options = $.extend(defaults, opts);
	if (options.c) {
		$c = $(options.c);
	} else {
		$c = $(options.t);
	}
	var $t = $(options.t);
	$.ajax({
		url : options.u,
		type : 'post',
		async: options.a,
		beforeSend: function() {
			$c.addClass('loading');
			$t.addClass('loadbig');
		},
		error: function(jx, err) {
			switch(err){ 
			case "timeout":
			case "error":
			case "abort":
			case "parsererror":
				break;
			}
			$c.removeClass('loading');
			$t.removeClass('loadbig');
		},
		success: function(data) {
			$t.html(data);
		},
		complete: function() {
			$c.removeClass('loading');
			$t.removeClass('loadbig');
			init_live(options.t);
			//$.unblockUI();
			if(typeof cbfn == 'function'){
				cbfn.call();
		    }
		}
	});
}
jQuery.fn.ajaxform = function (opts, cbfn) {
	//$.blockUI();
	// console.log('ajaxform bg');
	var defaults = {
	   u: '',
	   d: [],
	   t: '',
	   c: 'loading',
	   a: true,
	   p: false
	};
	var options = $.extend(defaults, opts);
	if (!options.t) {
		options.t = '#content';
	}
	if (options.t == '#content') {
		options.c = 'loadctr'
	}
	$t = $(options.t);
	$.ajax({
		url: options.u,
		type: 'post',
		data: options.d,
		async: options.a,
		beforeSend: function() {
			$t.addClass(options.c);
			if (options.t == '#workresult') {
				$t.addClass('showing');
			}
			if (options.p) {
				$.fn.openDOMWindow.showOverlay(true);
			}
			// $('#tiptip_holder').css('display','none');
			$('.tip').tipsy("hide");
		},
		success: function(data) {
			if (options.p) {
				$t.append(data);
			} else {
				$t.html(data);
			}
		},
		complete: function() {
			$t.removeClass(options.c);
			if (options.t == '#cboxLoadedContent') {
				$('#cboxLoadingGraphic, #cboxLoadingOverlay').css('display','none');
			}
			init_live(options.t);
			if(typeof cbfn == 'function'){
				cbfn.call();
		    }
		    //$.unblockUI();
		}
	});
	
}
jQuery.fn.cascadify = function(target) {
	return this.each(function() {
		var $el = $(this);
		var csl = '#'+$el.attr('id');
		var msl = csl+'_member';
		var mid = $(msl).val();
		// console.log('init '+csl+' with '+msl+', member ID='+mid);
		var cid = memberCompanySearch(mid);
		// console.log('>> company ID='+cid);
		$el.val(cid);
		memberCompanySelect(cid, msl);
		$(msl).val(mid);
		$el.change(function() {
			memberCompanySelect($el.val(),msl);
		});
	});
}
var ischk = false;
function chkfix(el) {
	var $el = $(el);
	if ($el.attr('checked')) {
		if (ischk) {
			return false;
		}
		ischk = $('<div id="actfix" />');
		$('#actfoot').wrap(ischk);
	} else if (ischk) {
		var $mc = $('input:checkbox:checked');
		if ($mc[0]) {
			return false;
		}
		$('#actfoot').unwrap();
		ischk = false;	
	}
}
jQuery.fn.fixify = function(target) {
	return this.each(function() {
		$(this).change(function() {
			chkfix(this);
		});
	});
}
function memberCompanySearch(mid) {
	var fid = 0;
	mid = parseInt(mid);
	$.each(memcodata, function(cx, arr) {
		$.each(arr, function(mx, txt) {
			mx = parseInt(mx);
			if (mx == mid) {
				fid = cx;
			}
		});
	});
	return fid;
}
function memberCompanySelect(coid, misel) {
	if (!memcodata || !memcodata[coid]) {
		return false;
	}
	var $s = $(misel);
	$s.empty();
	
	var tuples = [];
	for (var key in memcodata[coid]) tuples.push([key, memcodata[coid][key]]);
	tuples.sort(function(a, b) {
    	a = a[1];
    	b = b[1];
    	return a < b ? -1 : (a > b ? 1 : 0);
    });

    var opts = $s.prop('options');
    for (var i = 0; i < tuples.length; i++) {
    	opts[opts.length] = new Option(tuples[i][1], tuples[i][0]);
    }
	
    /*
	$.each(arr, function(val, text) {	
	    opts[opts.length] = new Option(text, val);
	});
	*/
}
function init_live(d) {
	if (d) {
		d += ' ';
	}
	// $('div.tabs').tabify();
	$(d+'.click-rows tr,'+d+'.click-cell td').clickify();
	$(d+'.ajax').ajaxify();
	$(d+'.combrlist').cascadify();
	$(d+' .tip').tooltip({html:true});
}

function init_fixing() {
	if ($(window).height() < $(document).height()) {
		$('input:checkbox.click-link').fixify();
	}
}

$(document).ready(function() {
	init_live('');
	init_fixing();
});
$(window).resize(init_fixing);

// BOOTSTRAP PLUGINS
!function(a){function b(){return new Date(Date.UTC.apply(Date,arguments))}var c=function(b,c){var f=this;this.element=a(b);this.language=c.language||this.element.data("date-language")||"en";this.language=this.language in d?this.language:"en";this.format=e.parseFormat(c.format||this.element.data("date-format")||"mm/dd/yyyy");this.picker=a(e.template).appendTo("body").on({click:a.proxy(this.click,this)});this.isInput=this.element.is("input");this.component=this.element.is(".date")?this.element.find(".add-on"):false;this.hasInput=this.component&&this.element.find("input").length;if(this.component&&this.component.length===0)this.component=false;if(this.isInput){this.element.on({focus:a.proxy(this.show,this),keyup:a.proxy(this.update,this),keydown:a.proxy(this.keydown,this)})}else{if(this.component&&this.hasInput){this.element.find("input").on({focus:a.proxy(this.show,this),keyup:a.proxy(this.update,this),keydown:a.proxy(this.keydown,this)});this.component.on("click",a.proxy(this.show,this))}else{this.element.on("click",a.proxy(this.show,this))}}a(document).on("mousedown",function(b){if(a(b.target).closest(".datepicker").length==0){f.hide()}});this.autoclose=false;if("autoclose"in c){this.autoclose=c.autoclose}else if("dateAutoclose"in this.element.data()){this.autoclose=this.element.data("date-autoclose")}this.keyboardNavigation=true;if("keyboardNavigation"in c){this.keyboardNavigation=c.keyboardNavigation}else if("dateKeyboardNavigation"in this.element.data()){this.keyboardNavigation=this.element.data("date-keyboard-navigation")}switch(c.startView||this.element.data("date-start-view")){case 2:case"decade":this.viewMode=this.startViewMode=2;break;case 1:case"year":this.viewMode=this.startViewMode=1;break;case 0:case"month":default:this.viewMode=this.startViewMode=0;break}this.weekStart=(c.weekStart||this.element.data("date-weekstart")||d[this.language].weekStart||0)%7;this.weekEnd=(this.weekStart+6)%7;this.startDate=-Infinity;this.endDate=Infinity;this.setStartDate(c.startDate||this.element.data("date-startdate"));this.setEndDate(c.endDate||this.element.data("date-enddate"));this.fillDow();this.fillMonths();this.update();this.showMode()};c.prototype={constructor:c,show:function(b){this.picker.show();this.height=this.component?this.component.outerHeight():this.element.outerHeight();this.update();this.place();a(window).on("resize",a.proxy(this.place,this));if(b){b.stopPropagation();b.preventDefault()}this.element.trigger({type:"show",date:this.date})},hide:function(b){this.picker.hide();a(window).off("resize",this.place);this.viewMode=this.startViewMode;this.showMode();if(!this.isInput){a(document).off("mousedown",this.hide)}if(b&&b.currentTarget.value)this.setValue();this.element.trigger({type:"hide",date:this.date})},setValue:function(){var a=e.formatDate(this.date,this.format,this.language);if(!this.isInput){if(this.component){this.element.find("input").prop("value",a)}this.element.data("date",a)}else{this.element.prop("value",a)}},setStartDate:function(a){this.startDate=a||-Infinity;if(this.startDate!==-Infinity){this.startDate=e.parseDate(this.startDate,this.format,this.language)}this.update();this.updateNavArrows()},setEndDate:function(a){this.endDate=a||Infinity;if(this.endDate!==Infinity){this.endDate=e.parseDate(this.endDate,this.format,this.language)}this.update();this.updateNavArrows()},place:function(){var b=parseInt(this.element.parents().filter(function(){return a(this).css("z-index")!="auto"}).first().css("z-index"))+10;var c=this.component?this.component.offset():this.element.offset();this.picker.css({top:c.top+this.height,left:c.left,zIndex:b})},update:function(){this.date=e.parseDate(this.isInput?this.element.prop("value"):this.element.data("date")||this.element.find("input").prop("value"),this.format,this.language);if(this.date<this.startDate){this.viewDate=new Date(this.startDate)}else if(this.date>this.endDate){this.viewDate=new Date(this.endDate)}else{this.viewDate=new Date(this.date)}this.fill()},fillDow:function(){var a=this.weekStart;var b="<tr>";while(a<this.weekStart+7){b+='<th class="dow">'+d[this.language].daysMin[a++%7]+"</th>"}b+="</tr>";this.picker.find(".datepicker-days thead").append(b)},fillMonths:function(){var a="";var b=0;while(b<12){a+='<span class="month">'+d[this.language].monthsShort[b++]+"</span>"}this.picker.find(".datepicker-months td").html(a)},fill:function(){var a=new Date(this.viewDate),c=a.getUTCFullYear(),f=a.getUTCMonth(),g=this.startDate!==-Infinity?this.startDate.getUTCFullYear():-Infinity,h=this.startDate!==-Infinity?this.startDate.getUTCMonth():-Infinity,i=this.endDate!==Infinity?this.endDate.getUTCFullYear():Infinity,j=this.endDate!==Infinity?this.endDate.getUTCMonth():Infinity,k=this.date.valueOf();this.picker.find(".datepicker-days th:eq(1)").text(d[this.language].months[f]+" "+c);this.updateNavArrows();this.fillMonths();var l=b(c,f-1,28,0,0,0,0),m=e.getDaysInMonth(l.getUTCFullYear(),l.getUTCMonth());l.setUTCDate(m);l.setUTCDate(m-(l.getUTCDay()-this.weekStart+7)%7);var n=new Date(l);n.setUTCDate(n.getUTCDate()+42);n=n.valueOf();var o=[];var p;while(l.valueOf()<n){if(l.getUTCDay()==this.weekStart){o.push("<tr>")}p="";if(l.getUTCFullYear()<c||l.getUTCFullYear()==c&&l.getUTCMonth()<f){p+=" old"}else if(l.getUTCFullYear()>c||l.getUTCFullYear()==c&&l.getUTCMonth()>f){p+=" new"}if(l.valueOf()==k){p+=" active"}if(l.valueOf()<this.startDate||l.valueOf()>this.endDate){p+=" disabled"}o.push('<td class="day'+p+'">'+l.getUTCDate()+"</td>");if(l.getUTCDay()==this.weekEnd){o.push("</tr>")}l.setUTCDate(l.getUTCDate()+1)}this.picker.find(".datepicker-days tbody").empty().append(o.join(""));var q=this.date.getUTCFullYear();var r=this.picker.find(".datepicker-months").find("th:eq(1)").text(c).end().find("span").removeClass("active");if(q==c){r.eq(this.date.getUTCMonth()).addClass("active")}if(c<g||c>i){r.addClass("disabled")}if(c==g){r.slice(0,h).addClass("disabled")}if(c==i){r.slice(j+1).addClass("disabled")}o="";c=parseInt(c/10,10)*10;var s=this.picker.find(".datepicker-years").find("th:eq(1)").text(c+"-"+(c+9)).end().find("td");c-=1;for(var t=-1;t<11;t++){o+='<span class="year'+(t==-1||t==10?" old":"")+(q==c?" active":"")+(c<g||c>i?" disabled":"")+'">'+c+"</span>";c+=1}s.html(o)},updateNavArrows:function(){var a=new Date(this.viewDate),b=a.getUTCFullYear(),c=a.getUTCMonth();switch(this.viewMode){case 0:if(this.startDate!==-Infinity&&b<=this.startDate.getUTCFullYear()&&c<=this.startDate.getUTCMonth()){this.picker.find(".prev").css({visibility:"hidden"})}else{this.picker.find(".prev").css({visibility:"visible"})}if(this.endDate!==Infinity&&b>=this.endDate.getUTCFullYear()&&c>=this.endDate.getUTCMonth()){this.picker.find(".next").css({visibility:"hidden"})}else{this.picker.find(".next").css({visibility:"visible"})}break;case 1:case 2:if(this.startDate!==-Infinity&&b<=this.startDate.getUTCFullYear()){this.picker.find(".prev").css({visibility:"hidden"})}else{this.picker.find(".prev").css({visibility:"visible"})}if(this.endDate!==Infinity&&b>=this.endDate.getUTCFullYear()){this.picker.find(".next").css({visibility:"hidden"})}else{this.picker.find(".next").css({visibility:"visible"})}break}},click:function(c){c.stopPropagation();c.preventDefault();var d=a(c.target).closest("span, td, th");if(d.length==1){switch(d[0].nodeName.toLowerCase()){case"th":switch(d[0].className){case"switch":this.showMode(1);break;case"prev":case"next":var f=e.modes[this.viewMode].navStep*(d[0].className=="prev"?-1:1);switch(this.viewMode){case 0:this.viewDate=this.moveMonth(this.viewDate,f);break;case 1:case 2:this.viewDate=this.moveYear(this.viewDate,f);break}this.fill();break}break;case"span":if(!d.is(".disabled")){this.viewDate.setUTCDate(1);if(d.is(".month")){var g=d.parent().find("span").index(d);this.viewDate.setUTCMonth(g);this.element.trigger({type:"changeMonth",date:this.viewDate})}else{var h=parseInt(d.text(),10)||0;this.viewDate.setUTCFullYear(h);this.element.trigger({type:"changeYear",date:this.viewDate})}this.showMode(-1);this.fill()}break;case"td":if(d.is(".day")&&!d.is(".disabled")){var i=parseInt(d.text(),10)||1;var h=this.viewDate.getUTCFullYear(),g=this.viewDate.getUTCMonth();if(d.is(".old")){if(g==0){g=11;h-=1}else{g-=1}}else if(d.is(".new")){if(g==11){g=0;h+=1}else{g+=1}}this.date=b(h,g,i,0,0,0,0);this.viewDate=b(h,g,i,0,0,0,0);this.fill();this.setValue();this.element.trigger({type:"changeDate",date:this.date});var j;if(this.isInput){j=this.element}else if(this.component){j=this.element.find("input")}if(j){j.change();if(this.autoclose){this.hide()}}}break}}},moveMonth:function(a,b){if(!b)return a;var c=new Date(a.valueOf()),d=c.getUTCDate(),e=c.getUTCMonth(),f=Math.abs(b),g,h;b=b>0?1:-1;if(f==1){h=b==-1?function(){return c.getUTCMonth()==e}:function(){return c.getUTCMonth()!=g};g=e+b;c.setUTCMonth(g);if(g<0||g>11)g=(g+12)%12}else{for(var i=0;i<f;i++)c=this.moveMonth(c,b);g=c.getUTCMonth();c.setUTCDate(d);h=function(){return g!=c.getUTCMonth()}}while(h()){c.setUTCDate(--d);c.setUTCMonth(g)}return c},moveYear:function(a,b){return this.moveMonth(a,b*12)},dateWithinRange:function(a){return a>=this.startDate&&a<=this.endDate},keydown:function(a){if(this.picker.is(":not(:visible)")){if(a.keyCode==27)this.show();return}var b=false,c,d,e,f,g;switch(a.keyCode){case 27:this.hide();a.preventDefault();break;case 37:case 39:if(!this.keyboardNavigation)break;c=a.keyCode==37?-1:1;if(a.ctrlKey){f=this.moveYear(this.date,c);g=this.moveYear(this.viewDate,c)}else if(a.shiftKey){f=this.moveMonth(this.date,c);g=this.moveMonth(this.viewDate,c)}else{f=new Date(this.date);f.setUTCDate(this.date.getUTCDate()+c);g=new Date(this.viewDate);g.setUTCDate(this.viewDate.getUTCDate()+c)}if(this.dateWithinRange(f)){this.date=f;this.viewDate=g;this.setValue();this.update();a.preventDefault();b=true}break;case 38:case 40:if(!this.keyboardNavigation)break;c=a.keyCode==38?-1:1;if(a.ctrlKey){f=this.moveYear(this.date,c);g=this.moveYear(this.viewDate,c)}else if(a.shiftKey){f=this.moveMonth(this.date,c);g=this.moveMonth(this.viewDate,c)}else{f=new Date(this.date);f.setUTCDate(this.date.getUTCDate()+c*7);g=new Date(this.viewDate);g.setUTCDate(this.viewDate.getUTCDate()+c*7)}if(this.dateWithinRange(f)){this.date=f;this.viewDate=g;this.setValue();this.update();a.preventDefault();b=true}break;case 13:this.hide();a.preventDefault();break;case 9:this.hide();break}if(b){this.element.trigger({type:"changeDate",date:this.date});var h;if(this.isInput){h=this.element}else if(this.component){h=this.element.find("input")}if(h){h.change()}}},showMode:function(a){if(a){this.viewMode=Math.max(0,Math.min(2,this.viewMode+a))}this.picker.find(">div").hide().filter(".datepicker-"+e.modes[this.viewMode].clsName).show();this.updateNavArrows()}};a.fn.datepicker=function(b){var d=Array.apply(null,arguments);d.shift();return this.each(function(){var e=a(this),f=e.data("datepicker"),g=typeof b=="object"&&b;if(!f){e.data("datepicker",f=new c(this,a.extend({},a.fn.datepicker.defaults,g)))}if(typeof b=="string"&&typeof f[b]=="function"){f[b].apply(f,d)}})};a.fn.datepicker.defaults={};a.fn.datepicker.Constructor=c;var d=a.fn.datepicker.dates={en:{days:["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],daysShort:["Sun","Mon","Tue","Wed","Thu","Fri","Sat","Sun"],daysMin:["Su","Mo","Tu","We","Th","Fr","Sa","Su"],months:["January","February","March","April","May","June","July","August","September","October","November","December"],monthsShort:["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"]}};var e={modes:[{clsName:"days",navFnc:"Month",navStep:1},{clsName:"months",navFnc:"FullYear",navStep:1},{clsName:"years",navFnc:"FullYear",navStep:10}],isLeapYear:function(a){return a%4===0&&a%100!==0||a%400===0},getDaysInMonth:function(a,b){return[31,e.isLeapYear(a)?29:28,31,30,31,30,31,31,30,31,30,31][b]},validParts:/dd?|mm?|MM?|yy(?:yy)?/g,nonpunctuation:/[^ -\/:-@\[-`{-~\t\n\r]+/g,parseFormat:function(a){var b=a.replace(this.validParts,"\0").split("\0"),c=a.match(this.validParts);if(!b||!b.length||!c||c.length==0){throw new Error("Invalid date format.")}return{separators:b,parts:c}},parseDate:function(e,f,g){if(e instanceof Date)return e;if(/^[-+]\d+[dmwy]([\s,]+[-+]\d+[dmwy])*$/.test(e)){var h=/([-+]\d+)([dmwy])/,i=e.match(/([-+]\d+)([dmwy])/g),j,k;e=new Date;for(var l=0;l<i.length;l++){j=h.exec(i[l]);k=parseInt(j[1]);switch(j[2]){case"d":e.setUTCDate(e.getUTCDate()+k);break;case"m":e=c.prototype.moveMonth.call(c.prototype,e,k);break;case"w":e.setUTCDate(e.getUTCDate()+k*7);break;case"y":e=c.prototype.moveYear.call(c.prototype,e,k);break}}return b(e.getUTCFullYear(),e.getUTCMonth(),e.getUTCDate(),0,0,0)}var i=e&&e.match(this.nonpunctuation)||[],e=new Date,m={},n=["yyyy","yy","M","MM","m","mm","d","dd"],o={yyyy:function(a,b){return a.setUTCFullYear(b)},yy:function(a,b){return a.setUTCFullYear(2e3+b)},m:function(a,b){b-=1;while(b<0)b+=12;b%=12;a.setUTCMonth(b);while(a.getUTCMonth()!=b)a.setUTCDate(a.getUTCDate()-1);return a},d:function(a,b){return a.setUTCDate(b)}},p,q,j;o["M"]=o["MM"]=o["mm"]=o["m"];o["dd"]=o["d"];e=b(e.getUTCFullYear(),e.getUTCMonth(),e.getUTCDate(),0,0,0);if(i.length==f.parts.length){for(var l=0,r=f.parts.length;l<r;l++){p=parseInt(i[l],10);j=f.parts[l];if(isNaN(p)){switch(j){case"MM":q=a(d[g].months).filter(function(){var a=this.slice(0,i[l].length),b=i[l].slice(0,a.length);return a==b});p=a.inArray(q[0],d[g].months)+1;break;case"M":q=a(d[g].monthsShort).filter(function(){var a=this.slice(0,i[l].length),b=i[l].slice(0,a.length);return a==b});p=a.inArray(q[0],d[g].monthsShort)+1;break}}m[j]=p}for(var l=0,s;l<n.length;l++){s=n[l];if(s in m)o[s](e,m[s])}}return e},formatDate:function(b,c,e){var f={d:b.getUTCDate(),m:b.getUTCMonth()+1,M:d[e].monthsShort[b.getUTCMonth()],MM:d[e].months[b.getUTCMonth()],yy:b.getUTCFullYear().toString().substring(2),yyyy:b.getUTCFullYear()};f.dd=(f.d<10?"0":"")+f.d;f.mm=(f.m<10?"0":"")+f.m;var b=[],g=a.extend([],c.separators);for(var h=0,i=c.parts.length;h<i;h++){if(g.length)b.push(g.shift());b.push(f[c.parts[h]])}return b.join("")},headTemplate:"<thead>"+"<tr>"+'<th class="prev"><i class="icon-arrow-left"/></th>'+'<th colspan="5" class="switch"></th>'+'<th class="next"><i class="icon-arrow-right"/></th>'+"</tr>"+"</thead>",contTemplate:'<tbody><tr><td colspan="7"></td></tr></tbody>'};e.template='<div class="datepicker dropdown-menu">'+'<div class="datepicker-days">'+'<table class=" table-condensed">'+e.headTemplate+"<tbody></tbody>"+"</table>"+"</div>"+'<div class="datepicker-months">'+'<table class="table-condensed">'+e.headTemplate+e.contTemplate+"</table>"+"</div>"+'<div class="datepicker-years">'+'<table class="table-condensed">'+e.headTemplate+e.contTemplate+"</table>"+"</div>"+"</div>"}(window.jQuery)
$('.datepicker').datepicker({
	format: 'dd/mm/yy',
	weekStart: 1
});