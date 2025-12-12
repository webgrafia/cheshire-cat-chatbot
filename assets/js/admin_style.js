(function ($) {
    jQuery(document).ready(function ($) {

        // ===== Accordion behavior =====
        $('.cheshire-accordion-toggle').on('click', function () {
            const $toggle = $(this);
            $toggle.toggleClass('active');

            const $icon = $toggle.find('.dashicons');
            $icon.toggleClass('dashicons-plus dashicons-minus');

            let $row = $toggle.closest('tr').next();
            while ($row.length && $row.hasClass('cheshire-accordion-content')) {
                $row.toggleClass('visible');
                $row = $row.next();
            }
        });

        // ===== Swatches color sync =====
        $('.cheshire-accordion-toggle').each(function () {
            const $toggle = $(this);
            const $swatches = $toggle.find('.cheshire-accordion-swatch span');

            let $row = $toggle.closest('tr').next();
            const inputs = [];

            while ($row.length && $row.hasClass('cheshire-accordion-content')) {
                const $input = $row.find('input[type="color"]');
                if ($input.length) {
                    inputs.push($input);
                }
                $row = $row.next();
            }

            inputs.forEach(function ($input, index) {
                $input.on('input change', function () {
                    const color = $(this).val();
                    $swatches.eq(index).css('background-color', color);
                });
            });
        });

        // ===== Funzione per aggiornare variabili CSS =====
        function updateCSSVar(variable, value) {
            //console.log('Updating CSS variable:', variable, value);
            document.documentElement.style.setProperty(variable, value);
        }

        // ===== Mappa delle corrispondenze =====
        const colorVarMap = {
            'cheshire_chat_button_color': '--chat-primary-color',
            // Bot message color drives both the bot bubble background and the primary-hover used by chips text
            'cheshire_chat_bot_message_color': ['--chat-primary-hover', '--chat-bot-msg-bg'],
            // User message color drives both user bubble background and primary-active
            'cheshire_chat_user_message_color': ['--chat-primary-active', '--chat-user-msg-bg'],
            'cheshire_chat_user_text_color': '--chat-user-msg-color',
            'cheshire_chat_bot_text_color': '--chat-bot-msg-color',
            'cheshire_chat_header_color': '--chat-header-bg-color',
            // Footer color maps to both chat bg and footer bg
            'cheshire_chat_footer_color': ['--chat-bg-color', '--chat-footer-bg-color'],
            'cheshire_chat_background_color': '--chat-messages-bg',
            'cheshire_chat_header_buttons_color': '--chat-header-buttons-color',
            'cheshire_chat_header_buttons_color_hover': '--chat-header-buttons-color-hover',
            'cheshire_chat_header_buttons_color_hover_background': '--chat-header-buttons-color-hover-background',
            'cheshire_chat_header_buttons_color_focus': '--chat-header-buttons-color-focus',
            'cheshire_chat_input_color': '--chat-input-color',
            'cheshire_chat_input_text_color': '--chat-input-text-color',
            'cheshire_chat_button_color_hover': '--chat-button-color-hover',
            'cheshire_chat_button_color_hover_background': '--chat-button-color-hover-background',
            'cheshire_chat_button_color_focus': '--chat-button-color-focus',
            'cheshire_chat_button_color_active': '--chat-button-color-active',
        };

        const inputVarMap = {
            'cheshire_chat_header_bg_color': 'cheshire_chat_header_color',
            'cheshire_chat_bg_color' : 'cheshire_chat_background_color',
            'cheshire_chat_user_msg_color' : 'cheshire_chat_user_text_color',
            'cheshire_chat_user_msg_bg' : 'cheshire_chat_user_message_color',
            'cheshire_chat_bot_msg_color' : 'cheshire_chat_bot_text_color',
            'cheshire_chat_bot_msg_bg' : 'cheshire_chat_bot_message_color',
            'cheshire_chat_footer_bg_color' : 'cheshire_chat_footer_color',
        };

        // Flag per distinguere cambi programmati (applicazione tema) da input manuali
        let isApplyingTheme = false;

        // ===== Gestione di tutti gli input color =====
        $('input[type="color"]').on('input change', function () {
            const colorValue = $(this).val();
            const nameAttr = this.name;
            if (!nameAttr) return;

            let cssVar;

            // Caso 1: nella mappa (può essere stringa o array di stringhe)
            if (colorVarMap[nameAttr]) {
                cssVar = colorVarMap[nameAttr];
            }
            // Caso 2: fallback automatico
            else {
                cssVar = '--' + nameAttr
                    .replace(/^cheshire_chat_/, 'chat-')
                    .replace(/_/g, '-');
            }

            // Aggiorna una o più variabili CSS
            if (Array.isArray(cssVar)) {
                cssVar.forEach(v => updateCSSVar(v, colorValue));
            } else {
                updateCSSVar(cssVar, colorValue);
            }

            // ===== Reset selezione tema su "No theme" solo per modifiche manuali =====
            if (!isApplyingTheme) {
                const $themeSel = $('#cheshire_chat_selected_theme');
                if ($themeSel.length && $themeSel.val() !== '') {
                    // Imposta il valore vuoto così il salvataggio registrerà "No theme"
                    $themeSel.val('');
                    // Non triggeriamo change per evitare il ricaricamento di un tema
                }
            }
        });

        // ===== Theme selection & JSON loading =====
        const $themeSelect = $('#cheshire_chat_selected_theme');
        if ($themeSelect.length) {
            const baseUrl = $themeSelect.data('themes-base-url') || '';

            function applyThemeToInputs(themeData) {
                if (!themeData || typeof themeData !== 'object') return;

                // Segnala che stiamo applicando il tema (cambi programmati)
                isApplyingTheme = true;

                Object.keys(themeData).forEach(function (jsonKey) {
                    const val = themeData[jsonKey];
                    if (typeof val !== 'string' || val.trim() === '') return;

                    const inputName = 'cheshire_' + jsonKey.replace(/-/g, '_');


                    const $input = $('input[name="' + inputName + '"]');

                    // Aggiorna input se presente
                    if ($input.length) {
                        console.log(inputName, val)
                        $input.val(val);
                        $input.trigger('input').trigger('change');
                    } else {
                        //console.log(inputName)
                        if(inputVarMap[inputName]) {
                            //console.log(inputVarMap[inputName])
                            let $_input = $('input[name="' + inputVarMap[inputName] + '"]');
                            if($_input.length) {
                                $_input.val(val);
                                $_input.trigger('input').trigger('change');
                            }
                        }
                    }

                    // 🔹 Applica sempre anche la variabile CSS corrispondente
                    const mappedVar = colorVarMap[inputName]
                        ? colorVarMap[inputName]
                        : '--' + inputName
                        .replace(/^cheshire_chat_/, 'chat-')
                        .replace(/_/g, '-');

                    updateCSSVar(mappedVar, val);
                });

                // Fine applicazione tema
                isApplyingTheme = false;
            }

            function fetchAndApplyTheme(fileName) {
                if (!fileName) return;
                if (!baseUrl) {
                    console.warn('[Cheshire Cat] Missing themes base URL.');
                    return;
                }
                const url = baseUrl + fileName;
                $.getJSON(url)
                    .done(function (data) {
                        applyThemeToInputs(data);
                    })
                    .fail(function (jqxhr, textStatus, error) {
                        console.warn('[Cheshire Cat] Failed to load theme', fileName, textStatus, error);
                    });
            }

            $themeSelect.on('change', function () {
                const fileName = $(this).val();
                if (!fileName) return;
                fetchAndApplyTheme(fileName);
            });

            // Se c’è un tema già selezionato al load, applicalo
            if ($themeSelect.val()) {
                fetchAndApplyTheme($themeSelect.val());
            }
        }

    });
})(jQuery);