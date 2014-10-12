if (!window.slmSchedule)
	window.slmSchedule = {};
	

slmSchedule._events = {};
slmSchedule._days = {};
slmSchedule._daysStaff = {};

slmSchedule._width = [];



slmSchedule.config={
	days: []
	,days_detail:[]
	,on_business :[]
	,holidays :[]
//	,chkHolidays :[]
	,staff_holidays : {}
	,open_position : 0
	,close_width : 0
	
};

slmSchedule.setEventDetail = function(ev,detail) {
	this._events[ev]["item_cds"] = detail[0];
	this._events[ev]["remark"] = detail[1];
	this._events[ev]["p2"] = detail[2];
	this._events[ev]["name"] = detail[3];
	this._events[ev]["tel"] = detail[4];
	this._events[ev]["mail"] = detail[5];
	this._events[ev]["user_login"] = detail[6];
	this._events[ev]["coupon"] = detail[7];
}

slmSchedule.chkHoliday = function(yyyymmdd) {
	for	(var i=0,to=slmSchedule.config.on_business.length;i < to ; i++ ){
		if (yyyymmdd.getTime()== slmSchedule.config.on_business[i].getTime() ) return false;
	}
	var tmp_days = yyyymmdd.getDay();
	for	(var i=0,to=slmSchedule.config.days.length;i < to ; i++ ){
		if ( tmp_days == slmSchedule.config.days[i] ) return true;
	}
	if (slmSchedule.date.existHolidays(yyyymmdd) ) return true;
	return false;
}

slmSchedule.date = {
	toYYYYMMDD:function(yyyymmdd) {
		var y = yyyymmdd.getFullYear();
		var m = yyyymmdd.getMonth() + 1;
		var d = yyyymmdd.getDate();
		return y+('0' + m).slice(-2)+('0' + d).slice(-2);
	},
	existHolidays:function(yyyymmdd) {
		for	(var i=0,to=slmSchedule.config.holidays.length;i < to ; i++ ){
			if (yyyymmdd.getTime() === slmSchedule.config.holidays[i].getTime() ) return true;
		}
	}
}

slmSchedule.chkFullHoliday = function(yyyymmdd) {
	if (slmSchedule.date.existHolidays(yyyymmdd) ) return true;
	var idx = slmSchedule.config.days.indexOf(yyyymmdd.getDay());
	var base = slmSchedule.config.days_detail[idx][0];
	var width = slmSchedule.config.days_detail[idx][1];
	if (base <= slmSchedule.config.open_position && slmSchedule.config.close_width <= width ) return true;
	return false;
}



slmSchedule.getLeft = function(yyyymmdd,base,width) {
	if (!slmSchedule.date.existHolidays(yyyymmdd) ) {
		var idx = slmSchedule.config.days.indexOf(yyyymmdd.getDay());
		if (slmSchedule.config.days_detail[idx]) {
			return +slmSchedule.config.days_detail[idx][0] * width + base;
		}
		else {
			alert("E099 slmSchedule.getLeft is wrong.");
		}
	}
	return slmSchedule.config.open_position + width + base;
}

slmSchedule.getWidth = function(yyyymmdd,width) {
	if (!slmSchedule.date.existHolidays(yyyymmdd) ) {
		var idx = slmSchedule.config.days.indexOf(yyyymmdd.getDay());
		if (slmSchedule.config.days_detail[idx]) {
			return slmSchedule._calcWidth (idx);
//			return +slmSchedule.config.days_detail[idx][1] * width;
		}
		else {
			alert("E099 slmSchedule.getWidth is wrong.");
		}
	}
	return slmSchedule.config.close_width * width;
}

slmSchedule.calcWidthBase = function(base,width) {
	var calc = 0;
	var max_cnt = +base + width;
	if (base == 0 ) base =1;
	for(var i = +base-1 ;i < max_cnt; i++ ) {
		calc += slmSchedule._width[i];		
	}
	return calc;
}


slmSchedule._calcWidth = function(idx) {
	var calc = 0;
	var i = +slmSchedule.config.days_detail[idx][0];
	var max_cnt = i + slmSchedule.config.days_detail[idx][1] ;
	if (i > 0 ) i--;
	for(i;i < max_cnt; i++ ) {
		calc += slmSchedule._width[i];		
	}
	return calc;
}

slmSchedule.setWidth = function(setWidth) {
	if (slmSchedule._width.length > 0 ) return;
	var tmp_array = setWidth.split(",");
	for(var i = 0,max_cnt = tmp_array.length;i < max_cnt  ; i++ ){
		var setWidth = tmp_array[i] /12 ;
		for(var j = 0 ; j < 12 ; j++ ) {
			slmSchedule._width.push(setWidth);
		}
	}
}