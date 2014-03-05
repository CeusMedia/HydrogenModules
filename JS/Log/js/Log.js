/**
 *	Wrapper for logging with console or konsoul.
 *
 *	@category	mm.ji
 *	@package	Swipeshow.Core
 *	@uses		JSON
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright	mellowmessage 2011
 *	@since		28.03.2011
 *	@version	$Id: Log.js 1221 2011-07-06 02:36:56Z cwuerker $
 */
var Log = {																		//  literal object, use statically

	hasConsole: false,
	logStart: 0,
	logLast: 0,
	logTime: true,
	logModule: 'console',
	logLevel: 1 | 2 | 4 | 8 | 16,

	getTimeInfo: function(){
	
		var now = new Date().getTime();
		var code = (now - Log.logStart) + '(+' + (now - Log.logLast) + ')> ';
		Log.logLast = now;
		return code;
	},

	init: function(){
		this.logStart = this.logLast = new Date().getTime();
		this.hasConsole = 'console' in window;
	},

	logUsingConsole: function(level,message,data){
		if(!Log.hasConsole)											//  console is available in browser
			return false;
		if(this.logLevel & level){									//
			if(typeof(message) === 'object')
				return console.log(message);						//  note on console as information

			var list = [];											//
			for(var key in data)									//
				list.push(' '+key+':'+JSON.stringify(data[key]));	//
			message += list.join('');								//
			if(this.logTime)										//  provide time information
				if(typeof message !== 'object')						//  message is not an object
					if(typeof message !== 'function')				//  message is not a function
						message = Log.getTimeInfo()+message;		//  prepend time information
			return console.log(message);							//  note on console as information
		}
	},

	log: function(level,message,data){
		if(!this.logStart)														//  log engine has not been initialized before
			this.init();														//  initialize log engine

		data = typeof data === 'object' ? data : {};
		switch(this.logModule){												//  run code depending on log module
			case 'console':														//  module 'console', supported by FF and WK
				this.logUsingConsole(level,message,data);
				break;															//  break switch
			default:															//  else run default
				break;															//  break switch
		}
	}	
};

/**
 *	General log function to wrap calls to console, konsoul and possibly other backends.
 *	@param		level		Message level, see konsoul.js
 *	@param		message		Message string, data structures are not supported using konsoul
 *	@return		void
 */
function log(level,message,data){
	Log.log(level,message,data);												//  call log method of log engine
}
