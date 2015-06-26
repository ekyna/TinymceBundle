(function(root, factory) {
    "use strict";
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = factory(require('jquery')); // TODO
    }
    else if (typeof define === 'function' && define.amd) {
        define('stfalcon/tinymce', ['jquery', 'json!tinymce_config', 'tinymce'], function($, config) {
            return factory($, config);
        });
    } else {
        root.StfalconTinymce = factory(root.jQuery); // TODO
    }
}(this, function($, config) {
    "use strict";

    if (typeof tinymce == 'undefined') {
        throw 'Tinymce is not available.';
    }

    tinymce.baseURL = config.tinymce_url;
    tinymce.suffix = '.min';

    // Load external plugins
    var externalPlugins = [];
    if (typeof config.external_plugins == 'object') {
        for (var pluginId in config.external_plugins) {
            if (!config.external_plugins.hasOwnProperty(pluginId)) {
                continue;
            }
            var opts = config.external_plugins[pluginId],
                url = opts.url || null;
            if (url) {
                externalPlugins.push({
                    'id': pluginId,
                    'url': url
                });
                tinymce.PluginManager.load(pluginId, url);
            }
        }
    }

    return {
        init: function($element) {
            $element.each(function() {
                var $textarea = $(this);

                // Get editor's theme from the textarea data
                var theme = $textarea.data('theme') || 'simple';
                // Get selected theme options
                var settings = (typeof config.theme[theme] != 'undefined')
                    ? config.theme[theme]
                    : config.theme['simple'];

                settings.external_plugins = settings.external_plugins || {};
                for (var p = 0; p < externalPlugins.length; p++) {
                    settings.external_plugins[externalPlugins[p]['id']] = externalPlugins[p]['url'];
                }

                // Overwrite config through data-config attribute
                var overwrite = $textarea.data('config') || {};
                for (var key in overwrite) { settings[key] = overwrite[key]; }

                // workaround for an incompatibility with html5-validation
                if ($textarea.prop('required')) {
                    $textarea.prop('required', false);
                }
                if ($textarea.attr('id') === '') {
                    $textarea.attr('id', 'tinymce_' + Math.random().toString(36).substr(2));
                }
                // Add custom buttons to current editor
                if (typeof config.tinymce_buttons == 'object') {
                    settings.setup = function(editor) {
                        for (var buttonId in config.tinymce_buttons) {
                            if (!config.tinymce_buttons.hasOwnProperty(buttonId)) continue;

                            // Some tricky function to isolate variables values
                            (function(id, opts) {
                                opts.onclick = function() {
                                    var callback = window['tinymce_button_' + id];
                                    if (typeof callback == 'function') {
                                        callback(editor);
                                    } else {
                                        alert('You have to create callback function: "tinymce_button_' + id + '"');
                                    }
                                };
                                editor.addButton(id, opts);

                            })(buttonId, clone(config.tinymce_buttons[buttonId]));
                        }
                        //Init Event
                        /*if (config.use_callback_tinymce_init) {
                         editor.on('init', function() {
                         var callback = window['callback_tinymce_init'];
                         if (typeof callback == 'function') {
                         callback(editor);
                         } else {
                         alert('You have to create callback function: callback_tinymce_init');
                         }
                         });
                         }*/
                    }
                }

                // Initialize textarea by its ID attribute
                var editor, id = $textarea.attr('id');
                //console.log('[Tinymce] creating ' + id);

                editor = new tinymce.Editor($textarea.attr('id'), settings, tinymce.EditorManager);
                editor.render();
            });
        },
        destroy: function($element) {
            $element.each(function() {
                var id = $(this).attr('id');
                //console.log('[Tinymce] removing ' + id);

                tinymce.remove('#' + id);
            });
            setTimeout(function() { console.log(tinymce.editors); }, 5000);
        },
        save: function($element) {
            tinymce.triggerSave();
        }
    };
}));
