/**
 *	...
 *
 *	Notes:
 *	  1. Setting lineHeight needs to update renderer, @see https://stackoverflow.com/a/37976142
 *
 *	@see https://stackoverflow.com/questions/6440439/how-do-i-make-a-textarea-an-ace-editor
 *
 */
var ModuleAce = {
	verbose: true,
	strict: true,
	config: {																	//  @see https://github.com/ajaxorg/ace/wiki/Configuring-Ace
		theme: "ace/theme/tomorrow",
		mode: "ace/mode/html",
		options: {
			lineHeight: 1.25,						//  add top and bottom padding, @see https://stackoverflow.com/a/37976142
			minLines: 4,							//  show atleast 4 lines
			maxLines: 'Infinity',							//  ...
			useWorker: false,
		},
		events: [{
			event: 'change',
			callback: function(){
			}
		}],
		hotkeys: [{
	    	key: "F11",
	    	name: "Toggle Fullscreen",
			callback: function(editor){
				var dom = require("ace/lib/dom");
		        var fullScreen = dom.toggleCssClass(document.body, "fullScreen");
		        dom.setCssClass(editor.container, "fullScreen", fullScreen);
		        editor.setAutoScrollEditorIntoView(!fullScreen);
		        editor.resize();
			}
		},{
			key: "Ctrl-Shift-s",
			name: "showSettingsMenu",
			callback: function(editor) {
				ace.config.loadModule("ace/ext/settings_menu", function(module) {
                	module.init(editor);
                	editor.showSettingsMenu()
            	})
			},
			readOnly: true
		}],
		flags: {
			fontSize: 16,							//  default: 12
			highlightSelectedWord: false,			//  default: true
//
//		Default values:
//			readOnly: false,						//  default: false
//			highlightActiveLine: true,				//  default: true
//			displayIndentGuides: true,				//  default: true
//
//		Good to know:
//			readOnly: false,						//  default: false
//			scrollSpeed: 2,							//  default: 2 | number of lines to scroll with one mouse scroll tick
//
//		No change detected at first glance:
//			animatedScroll: true,					//  default: false
//			highlightGutterLine: false,				//  default: true
//			selectionStyle: 'abc',					//  default: line
//			behavioursEnabled: false,				//  default: true
//			scrollMargin: ???,						//  default: ???
//
//		Not tested yet:
//			printMarginColumn(Number)				//  Sets the column defining where the print margin should be.
//
//			showFoldWidgets: false,					//  Indicates whether the fold widgets are shown or not.
//			showInvisibles: false,					//  If showInvisibles is set to true, invisible characters—like spaces or new lines—are show in the editor.
//			showPrintMargin: false,					// 	If showPrintMargin is set to true, the print margin is shown in the editor.
//			showFoldWidgets: false,					//  Indicates whether the fold widgets are shown or not.
//			showInvisibles: false,					//  If showInvisibles is set to true, invisible characters—like spaces or new lines—are show in the editor.
//			showPrintMargin: false,					//  If showPrintMargin is set to true, the print margin is shown in the editor.
//			setStyle: 'abc',						//  Adds a new class, style, to the editor.
//			setWrapBehavioursEnabled: false,		//  Specifies whether to use wrapping behaviors or not, i.e. automatically wrapping the selection with characters such as brackets when such a character is typed in.
//
		}
	},
	applyAuto: function(){
		jQuery(".ace-auto").each(function(){
			ModuleAce.applyTo(this);
		});
	},
	applyTo: function(elementOrSelectorOrContainer, config){
		"use strict";
		config = typeof config === "undefined" ? [] : config;
		var container, editor, hotkeys = [], events = [];
		container	= jQuery(elementOrSelectorOrContainer);								//  try to find HTML node
		if(!container.size()){															//  no HTML node found
			if(ModuleAce.strict)														//  in strict mode:
				throw "AceEditor: Apply failed - element not found";					//  ... throw an exception
			ModuleAce.log("AceEditor: Apply failed - element not found");				//  in verbose mode: note fail in console log
		}
//		if(!container.attr("id"))														//  no ID attribute set
//			container.attr("id", "ace-container-" + Math.random() * 100000);			//  set random ID attribute

		if(typeof config.events !== "undefined"){										//  custom events set
			events	= config.events.slice();											//  make a copy
			delete config.events;														//  remove since deep copy would fail on arrays
		}
		if(typeof config.hotkeys !== "undefined"){										//  custom hotkeys set
			hotkeys	= config.hotkeys.slice();											//  make a copy
			delete config.hotkeys;														//  remove since deep copy would fail on arrays
		}

		/*  Build editor config out of module defaults, JavaScript options, HTML attributes and HTML data attributes  */
		config = jQuery.extend(true, ModuleAce.config, config);							//  extend defaults by given config in deep mode
		config.events	= config.events.concat(events);									//  merge default and custom events
		config.hotkeys	= config.hotkeys.concat(hotkeys);								//  merge default and custom hotkeys
		ModuleAce.extendConfigByHtmlTagAttributes(container, config);
		ModuleAce.log("AceEditor: Final configuration setup for this instance:");		//  in verbose mode: announce display of final config
		ModuleAce.log(config);															//  in verbose mode: log final config

		/*  Create Ace instance and apply configuration  */
		try{
			if(container.get(0).nodeName === "TEXTAREA"){								//  selected container is a textarea
				var editDiv = $('<div>', {												//  create a new container to apply ace on
					position: 'absolute',												//
//					'class': container.attr('class'),
					width: container.width(),											//  set width of original textarea
					height: container.height()											//  set height of original textarea
				}).data('original-value', container.val());								//  copy original textarea content for change detection
				editDiv.insertBefore(container);										//  insert new container OVER textarea
				editor = ace.edit(editDiv.get(0));										//  apply ace editor to new container
				editor.getSession().setValue(container.val());							//  set editor content from original textarea
				container.css('visibility', 'hidden');									//  hide original textarea
				editor.getSession().on("change", function(){							//  on content changes ...
					container.val(editor.session.getValue());							//  ... update content of original textarea
				});
			}
			else{																		//  selected container is NOT a textarea
				editor = ace.edit(container.get(0));									//  apply ace editor on (first node of) found container
			}
			container.data("ace-editor-instance", editor);								//  store editor instance on container for later (events etc.)
			ModuleAce.configureEditorInstance(editor, config);							//  apply merged configuration to editor
		}
		catch(exception){
			if(ModuleAce.strict)
				throw exception;
			ModuleAce.log(exception);
		}
		return editor;																	//  return editor instance
	},
	configureEditorInstance: function (editor, config){
		"use strict";
		var i, optionKey, currentValue, configValue, flagKey, flagGetMethod, flagSetMethod;
//		editor.setAutoScrollEditorIntoView(true);
		editor.renderer.updateFontSize();												//  called here just to minimize flicker (is called below also)
		if(config.theme)																//  editor theme is configured
			editor.setTheme(config.theme);												//  set editor theme
		if(config.mode)																	//  editor mode is configured
			editor.session.setMode(config.mode);										//  set editor mode

		for(i=0; i<config.events.length; i++){											//  iterate configured events
			ModuleAce.log( "AceEditor: Set event \"%s\"", config.events[i].event );
			editor.session.on(config.events[i].event, config.events[i].callback);		//
		}
		for(i=0; i<config.hotkeys.length; i++){											//  iterate configured hotkeys
			ModuleAce.log( "AceEditor: Set hotkey \"%s\"", config.hotkeys[i].key );
			editor.commands.addCommand({												//  add hotkey as new command
				bindKey: config.hotkeys[i].key,											//  hotkey bind key
				name: config.hotkeys[i].name,											//  hotkey name
				exec: config.hotkeys[i].callback										//  hotback callback
			});
		}
		for(optionKey in config.options){												//  iterate options
			configValue	= config.options[optionKey];									//  shortcut value
			switch(optionKey){															//
				case "lineHeight":														//
					editor.container.style[optionKey] = configValue;					//
					break;																//
				default:																//
					currentValue = editor.getOption(optionKey);							//
					if(currentValue == configValue)										//
						continue;														//
					editor.setOption(optionKey, configValue);							//
			}
			ModuleAce.log(
				"AceEditor: Set option \"%s\" to %s (was: %s)",
				optionKey,
				JSON.stringify(configValue),
				JSON.stringify(currentValue)
			);
		}
		editor.renderer.updateFontSize();												//  realize line-height according to font-size @link https://stackoverflow.com/a/37976142
		for(flagKey in config.flags){													//  iterate config flags
			flagGetMethod = "get" + flagKey.charAt(0).toUpperCase() + flagKey.slice(1);	//  render method to get flag value
			flagSetMethod = "set" + flagKey.charAt(0).toUpperCase() + flagKey.slice(1);	//  render method to set flag value
			if(typeof editor[flagGetMethod] !== "function"){							//  flag key is not defined
				if(ModuleAce.strict)													//  if strict mode is on ...
					throw "AceEditor: Invalid flag: " + flagKey;						//  ... throw exception
				ModuleAce.log("AceEditor: Invalid flag: " + flagKey);					//  otherwise log
				continue;																//  skip to next flag
			}
			currentValue = editor[flagGetMethod]();										//  get current flag value
			configValue	= config.flags[flagKey];										//  shortcut configured flag value
			if(currentValue == configValue)												//  no change
				continue;																//  skip to next flag
			editor[flagSetMethod](configValue);											//  set flag value
			ModuleAce.log(
				"AceEditor: Set flag \"%s\" to %s (was: %s)",
				flagKey,
				JSON.stringify(configValue),
				JSON.stringify(currentValue)
			);
		}
	},
	extendConfigByHtmlTagAttributes: function (elementOrContainer, config) {
		var container = jQuery(elementOrContainer);
		var height = container.height();
		ModuleAce.log("Container height: %d", height);
		config.options.minLines = Math.floor(height / config.flags.fontSize);
		ModuleAce.log("minLines: " + config.options.minLines);

		/*  Apply HTML attributes as ACE options/flags to config  */
		jQuery.each(container.get(0).attributes, function(nr){
			if(this.name.match(/^data-/))
				return;
			switch(this.name.toLowerCase()){
				case "rows":
					config.options.minLines = this.value;
					ModuleAce.log(
						"AceEditor: Found HTML attribute \"%s\" to override option \"minLines\" with %d",
						this.name,
						this.value
					);
					break;
				case "readonly":
				case "disabled":
					if(config.flags.readOnly == true)
						return;
					config.flags.readOnly = true;
					ModuleAce.log(
						"AceEditor: Found HTML attribute \"%s\" to override flag \"readOnly\" with true",
						this.name
					);
					break;
				default:
/*					ModuleAce.log(
						"AceEditor: Found HTML attribute \"%s\" but no handle for it",
						this.name
					);*/
			}
		});

		/*  Apply HTML data attributes as ACE options/flags to config  */
		jQuery.each(container.data(), function(key, value){
			if(key.match(/^aceFlag/)){
				key = key.replace(/^aceFlag/, "");
				key = key.charAt(0).toLowerCase() + key.slice(1);
				var currentValue = false;
				if(typeof config.flags[key] !== "undefined")
					currentValue	= config.flags[key];
				if(currentValue == value)
					return;
				config.flags[key] = value;
				ModuleAce.log(
					"AceEditor: Found data flag attribute \"%s\" and configured it with value %s (was: %s)",
					key,
					JSON.stringify(value),
					JSON.stringify(currentValue),
				);
			}
			if(key.match(/^aceOption/)){
				key = key.replace(/^aceOption/, "");
				key = key.charAt(0).toLowerCase() + key.slice(1);
				var currentValue = false;
				if(typeof config.options[key] !== "undefined")
					currentValue	= config.options[key];
				if(currentValue == value)
					return;
				config.options[key] = value;
				ModuleAce.log(
					"AceEditor: Found data option attribute \"%s\" with value %s (was: %s)",
					key,
					JSON.stringify(value),
					JSON.stringify(currentValue)
				);
			}
		});
	},
	getFrom: function(elementOrSelectorOrContainer){
		"use strict";
		var container, editor;
		container	= jQuery(elementOrSelectorOrContainer);
		if(!container.size())
			throw "AceEditor.getEditor: Node not found";
		editor = container.data("ace-editor-instance");
		if(!editor)
			throw "AceEditor.getEditor: Element has no Ace Editor";
		return editor;
	},
	log: function(){
		if(arguments.length === 0)
			throw "ModuleAce.log needs atleast 1 argument (as message)";
		if(ModuleAce.verbose && typeof console !== "undefined")
			console.log(ModuleAce.sprintf.apply(this, arguments));
	},
	sprintf: function(message){
		if(arguments.length === 0)
			throw "sprintf needs atleast 1 argument (as message)";
		var msg = arguments[0];
		for(var i=1; i<arguments.length; i++)
			msg = msg.replace(/(%[ds])/, arguments[i]);
		return msg;
	}
};
