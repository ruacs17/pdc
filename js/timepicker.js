/* -- DO NOT REMOVE --
 * jQuery TimePicker 1.0 plugin
 * 
 * Author: Dionlee Uy
 * Email: dionleeuy@gmail.com
 *
 * Date: Mon Mar 3 2013
 *
 * @requires jQuery
 * -- DO NOT REMOVE --
 */
 
(function ($) {

	var AM_PM = ['AM','PM'], MAX_HOUR = 12, MAX_MIN = 59,
		ACCEPTABLE_KEYS = [48,49,50,51,52,53,54,55,56,57,96,97,98,99,100,101,102,103,104,105,189 /*Num keys*/,40,39,38,37 /*Arrow Keys*/,17,16,9 /*Ctrl,Shift,Tab*/];

	var DTimePicker = function(elem, options){
		var that = this;
		this.input = $(elem);
		this.activeField = DTimePicker.HOUR;
		this.picker = null;
		if(options.editable){
			this.hour = $('<input class="tp_hr" type="text" style="width:16px;" maxlength="2">');
			this.min = $('<input class="tp_min" type="text" style="width:16px;" maxlength="2">');
			this.ampm = $('<input class="tp_am_pm" type="text" maxlength="2" style="width:25px;">');			
		}
		else{
			this.hour = $('<input class="tp_hr" type="text" style="width:16px;" maxlength="2" readonly>');
			this.min = $('<input class="tp_min" type="text" style="width:16px;" maxlength="2" readonly>');
			this.ampm = $('<input class="tp_am_pm" type="text" maxlength="2" style="width:25px;" readonly>');			
		}


		if(options.time){
			//alert(options.time)
			//var h = parseInt(options.time.substr(0, 2)), m = parseInt(options.time.substr(3, 2));
			var h = (options.time.substr(0, 2)), m = (options.time.substr(3, 2));
			//Set values of hour, minute and AM/PM inputs
			var am_pm;
			if(h=='--')
				am_pm='--';
			else{
				if(h==24)
					am_pm = "AM";
				else
					am_pm = (h>=12) ? "PM" : "AM";
			}
				

			/*if( parseInt(h) )
				am_pm = (h>=12) ? "PM" : "AM";
			else
				am_pm = '--';*/
			this.ampm.val(am_pm);
			//this.ampm.val((parseInt(h) >= 12 ? "PM" : "AM"));


			if(m=='--')
				m = '--';
			else{
				m = parseInt(m);
				if(m==0)
					m = '00';
				else
					m = (m>=10) ? m : "0"+m;
			}
			this.min.val(m);
			//this.min.val(m>=10?m:"0"+m);


			if(h=='--')
				h = '--'
			else{
				if(h==0)
					h=12;
				else{
					h = parseInt(h);
					h = (h > 12 ? h-12 : h);
					h = (h>=10) ? h:"0"+h					
				}

			}
			this.hour.val(h);
			//this.hour.val(h>=10?h:"0"+h);

			//Set value of anchor input
			this.setValue();
		}
		else{
			this.ampm.val('--');
			this.hour.val('--');
			this.min.val('--');
			this.setValue();
		}

		//Attach event listeners to inputs
		if(options.editable){
			this.hour.on('focus', function () {
				$(this).select();
				that.activeField = DTimePicker.HOUR;
			}).on('blur', function (e) {
				var val = parseInt($(this).val());
				if(val > MAX_HOUR) $(this).val(MAX_HOUR);
				if(val < 10 && val > 0) $(this).val("0"+val);
				if(val===0)$(this).val("01");
				that.setValue();
			}).on('keydown', function (e) {
				if(ACCEPTABLE_KEYS.indexOf(e.keyCode)<0) return false;
			}).on('keyup', function (e) {
				if (e.keyCode == 40){ //Arrow Down/Up button
					that.spinDown();
				} else if(e.keyCode == 38) {
					that.spinUp();
				}
			});
			this.min.on('focus', function () { 
				$(this).select();
				that.activeField = DTimePicker.MIN; 
			}).on('blur', function (e) {
				var val = parseInt($(this).val());
				if(val > MAX_MIN) $(this).val(MAX_MIN);
				if(val < 10 && val >= 0) $(this).val("0"+val);
				that.setValue();
			}).on('keydown', function (e) {
				if(ACCEPTABLE_KEYS.indexOf(e.keyCode)<0) return false;
			}).on('keyup', function (e) {
				if (e.keyCode == 40){ //Arrow Down/Up button
					that.spinDown();
				} else if(e.keyCode == 38) {
					that.spinUp();
				}
			});
			this.ampm.on('keydown', function (e) {
				if(e.keyCode == 80) { //P button
					$(this).val("PM").select();
					that.setValue();
				} else if (e.keyCode == 65){ //A button
					$(this).val("AM").select();
					that.setValue();
				} else if (e.keyCode == 40 || e.keyCode == 38){ //Arrow Down/Up button
					that.activeField = DTimePicker.AMPM;
					that.adjustTime(null);
				} else if(e.keyCode == 16 || e.keyCode == 9) { return true; }
				  else if(e.keyCode == 189) {return true;}
				return false;
			}).on('focus', function () { $(this).select(); that.activeField = DTimePicker.AMPM; });
		}

		if(options.editable)
			this.spinnerDiv = $('<div class="tp_spinners"></div>');
		else
			this.spinnerDiv = $('<div class="tp_spinners" style="display:none;"></div>');
		this.spinUpBtn = $('<span class="tp_spinup"></span>');
		this.spinDownBtn = $('<span class="tp_spindown"></span>');

		//Attache event listeners to spinners
		this.spinUpBtn.on('click', function (e) { that.spinUp(); });
		this.spinDownBtn.on('click', function (e) { that.spinDown(); });

		this.create();
	}

	DTimePicker.prototype = {

		constructor : DTimePicker, 

		create : function(){
			var that = this;
			that.input.wrap('<div class="tp"></div>');
			that.input.hide();

			that.picker = that.input.parent();

			that.picker.append(that.hour).append(":").append(that.min).append(" ").append(that.ampm).append(" ");

			that.spinnerDiv.append(that.spinUpBtn).append(that.spinDownBtn).appendTo(that.picker);
		},

		spinUp : function () { this.adjustTime('p'); },

		spinDown : function () { this.adjustTime('m'); },

		adjustTime : function (op) {
			var that = this;
			switch(this.activeField){
				case DTimePicker.HOUR:
					var hv = parseInt(that.hour.val() ) ? that.hour.val() : 0;
					var val = parseInt(hv),
						//limit = (op=='p'? MAX_HOUR : 1);
						limit = MAX_HOUR;
						val = (op=='p'? val+1 : val-1);
						//alert(limit)

						if(val <= limit && val >=0){
							if(val)
								that.hour.val((val<10?"0"+val:val)).select();
							else
								that.hour.val('--').select();
						}
						
					//if((op=='p'?(val<=limit):(val>=limit))) { that.hour.val((val<10?"0"+val:val)).select(); }
				break;
				case DTimePicker.MIN:
					var mv = parseInt(that.min.val()) ? that.min.val() : 0;
					var val = parseInt(mv),
						//limit = (op=='p'? MAX_MIN : 0);
						limit = MAX_MIN;
						//val = (op=='p'? val+1 : val-1);
						if(op=='p')
							val = (that.min.val()=='--') ? 0 : val + 1;
						else
							val = val - 1;
						if(val <= limit)
							if(val >=0)
								that.min.val((val<10?"0"+val:val)).select();
							else
								that.min.val('--').select();

					//if((op=='p'?(val<=limit):(val>=limit))) { that.min.val((val<10?"0"+val:val)).select(); }
				break;
				case DTimePicker.AMPM:
					var val = that.ampm.val();
					if(val=='--')
						val='AM';
					else if(val=='AM')
						val='PM';
					else if(val=='PM')
						val="--"
					that.ampm.val(val).select();
					//that.ampm.val((val=='AM'? "PM" : "AM")).select();
				break;
			}
			this.setValue();
		},

		setValue : function () {
			var ap = this.ampm.val(), h = this.hour.val(), m = this.min.val();
			var val="";
			if(h=="--" || m=="--" || ap=="--")
				val="";
			else{
				if(ap=="AM"){
					if(h>=12)
						h = parseInt(h)-12;
				}
				else if(ap=="PM"){
					if(h==12)
						h=12;
					else
						h=parseInt(h)+12;
				}
				h = parseInt(h);
				h = (h < 10) ? "0"+h : h;
				val = h+":"+m+":00";
			}

			//alert(val)
			//var val = (parseInt(h) && parseInt(m)) ? h+":"+m+":00" : "";
			this.input.attr('value', val);
		}
	}

	DTimePicker.HOUR = 1;
	DTimePicker.MIN = 2;
	DTimePicker.AMPM = 3;

	/* DEFINITION FOR TIME PICKER */
	$.fn.timepicker = function(opts){
		return $(this).each(function(){
 			var $this = $(this),
 				data = $(this).data('dtimepicker'),
 				options = $.extend({}, $.fn.timepicker.defaults, $this.data(), typeof opts == 'object' && opts);
 			if(!data){
 				$this.data('dtimepicker', (data = new DTimePicker(this, options)));
 			}
 			if(typeof opts == 'string') data[opts]();
		});
	}

	$.fn.timepicker.defaults = {
 		time: '00:00:00',
 		editable:true,
 	}

	$.fn.timepicker.Constructor = DTimePicker;

})(jQuery);