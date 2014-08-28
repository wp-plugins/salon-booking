if (!window.slmSchedule)
	window.slmSchedule = {};
	

slmSchedule._events = {};
slmSchedule._days = {};
slmSchedule._daysStaff = {};



slmSchedule.config={
	days: []
	,on_business :[]
	,holidays :[]
	,staff_holidays : {}
	
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
	for	(var i=0,to=slmSchedule.config.holidays.length;i < to ; i++ ){
		if (yyyymmdd.getTime() === slmSchedule.config.holidays[i].getTime() ) return true;
	}
	return false;
}
