String.prototype.sprintf = function(){
	if(arguments.length === 0)
		return this;
	var data = this;
	for(var k=0; k<arguments.length; ++k){
		switch(typeof(arguments[k])){
			case 'string':
				data = data.replace(/%s/, arguments[k]);
				break;
			case 'number':
				data = data.replace(/%d/, arguments[k]);
				break;
			case 'boolean':
				data = data.replace(/%b/, arguments[k] ? 'true' : 'false');
				break;
		}
	}
	return data;
}