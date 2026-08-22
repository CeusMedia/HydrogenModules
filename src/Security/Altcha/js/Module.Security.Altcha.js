let ModuleSecurityAltcha = {
	verifyFormDataWithAltcha: async function(formData){
		const response = await fetch('api/altcha/verify', {
			method: 'POST',
			body: formData
		});
		const body = await response.json();
		return {
			ok: response.ok,
			statusCode: response.status,
			statusText: response.statusText,
			code: body.code,
			message: body.message,
		};
	}
};