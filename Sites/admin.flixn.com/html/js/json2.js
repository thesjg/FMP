/*
  * toJsonString
  *
  * produces a JSON string representation of a javascript object
  * usage: var jsonstring = someobject.toJSONString();
  *
  * Tino Zijdel - crisp@xs4all.nl, 01/10/2006
  */
(
	function()
	{
		var re = /[\x00-\x1f\\"]/;
		function toJSONString(obj)
		{
			switch (typeof obj)
			{
				case 'string':
					return '"' + (re.test(obj) ? encodeString(obj) : obj) + '"';
				case 'number':
				case 'boolean':
					return String(obj);
				case 'object':
					if (obj)
					{
						switch (obj.constructor)
						{
							case Array:
								var a = [];
								for (var i = 0, l = obj.length; i < l; i++)
									a[a.length] = toJSONString(obj[i]);
								return '[' + a.join(',') + ']';
							case Object:
								var a = [];
								for (var i in obj)
									if (obj.hasOwnProperty(i))
										a[a.length] = '"' + (re.test(i) ? encodeString(i) : i) + '":' + toJSONString(obj[i]);
								return '{' + a.join(',') + '}';
							case String:
								return '"' + (re.test(obj) ? encodeString(obj) : obj) + '"';
							case Number:
							case Boolean:
								return String(obj);
							case Function:
							case Date:
							case RegExp:
								return 'undefined';
						}
					}
					return 'null';
				case 'function':
				case 'undefined':
				case 'unknown':
					return 'undefined';
				default:
					return 'null';
			}
		}
		var stringescape = {
			'\b': '\\b',
			'\t': '\\t',
			'\n': '\\n',
			'\f': '\\f',
			'\r': '\\r',
			'"' : '\\"',
			'\\': '\\\\'
		};
		function encodeString(string)
		{
			return string.replace(
				/[\x00-\x1f\\"]/g,
				function(a)
				{
					var b = stringescape[a];
					if (b)
						return b;
					b = a.charCodeAt();
					return '\\u00' + Math.floor(b / 16).toString(16) + (b % 16).toString(16);
				}
			);
		}
		if (!Object.prototype.toJSONString)
			Object.prototype.toJSONString = function() { return toJSONString(this); }
		if (!Object.prototype.hasOwnProperty)
			Object.prototype.hasOwnProperty = function(p) { var undefined; return this.constructor.prototype[p] === undefined; }
	}
)();