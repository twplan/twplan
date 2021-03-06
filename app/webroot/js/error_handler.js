var TWP = TWP || {};

TWP.error_handler = function (message, url, line_number) {
	var suppressed = false;

	if (
		(line_number == 88 &&
		message.indexOf('10 $digest()') >= 0
		) ||
		(navigator.userAgent.indexOf('Opera/9.80') >= 0 && // Ignore location $digest() errors for this old version of Opera
		message.indexOf('$locationChangeStart') >= 0
		)
	) {
		var suppressed = true;
	}
	else {
		alert('Sorry, something went wrong. This error has been logged and we will try to resolve it. You may be able to continue using TWplan - try again!');
	}

	$.ajax({
		url: 'analytics/add_bug_report',
		type: 'POST',
		contentType: 'application/json; chareset=utf-8',
		data: JSON.stringify({
			description: line_number,
			page: url + ' (on: ' + document.URL + ')',
			error_message: message,
			is_js: true,
			is_replicable: null,
			contact_information: null,
			is_suppressed: suppressed
		}),
		success: function (data) {
			debugger;
		},
		error: function (data) {
			alert('Please file a bug report! Something is really wrong.\nError_message: ' + data);
		},
	});

	return false;
};

window.onerror = TWP.error_handler;