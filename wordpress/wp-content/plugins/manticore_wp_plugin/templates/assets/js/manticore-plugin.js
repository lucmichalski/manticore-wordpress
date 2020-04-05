jQuery(document)
    .ready(function () {

        var inAdminPanel = jQuery('#isAdminPanel');
        if (inAdminPanel.length > 0) {
            jQuery(document).tooltip({
                content: function () {
                    return jQuery(this).prop('title');
                },
                show: null, // show immediately
                open: function (event, ui) {
                    if (typeof(event.originalEvent) === 'undefined') {
                        return false;
                    }

                    var $id = jQuery(ui.tooltip).attr('id');

                    // close any lingering tooltips
                    jQuery('div.ui-tooltip').not('#' + $id).remove();

                },
                close: function (event, ui) {
                    ui.tooltip.hover(function () {
                            jQuery(this).stop(true).fadeTo(400, 1);
                        },
                        function () {
                            jQuery(this).fadeOut('400', function () {
                                jQuery(this).remove();
                            });
                        });
                }
            });

            var slider_passages = jQuery("#slider-passages");
            if (slider_passages.length > 0) {

                var slider_range_words = jQuery("#slider-range-words");
                var slider_results_count = jQuery("#slider-count-results");
                var slider_single_words = jQuery("#slider-single-words");
                var slider_max_chars_count = jQuery("#slider-max-chars-count");
                var slider_range_chars_count = jQuery("#slider-range-chars-count");


                jQuery('#excerpt_dynamic_around').change(function (e) {
                    var value = jQuery(this).attr('checked');
                    if (value) {
                        slider_range_words.parentsUntil('tbody').show();
                        slider_single_words.parentsUntil('tbody').hide();

                        slider_range_chars_count.parentsUntil('tbody').show();
                        slider_max_chars_count.parentsUntil('tbody').hide();
                    } else {
                        slider_range_words.parentsUntil('tbody').hide();
                        slider_single_words.parentsUntil('tbody').show();

                        slider_range_chars_count.parentsUntil('tbody').hide();
                        slider_max_chars_count.parentsUntil('tbody').show();
                    }
                });


                slider_max_chars_count.slider({
                    min: 200,
                    max: 5000,
                    value: jQuery("#amount-max-chars-count").val(),
                    slide: function (event, ui) {
                        renderView();
                        jQuery("#amount-max-chars-count").val(ui.value);
                    }
                });

                slider_results_count.slider({
                    min: 1,
                    max: jQuery("#count-results").data('max-val'),
                    value: jQuery("#count-results").val(),
                    slide: function (event, ui) {
                        renderView();
                        jQuery("#count-results").val(ui.value);
                    }
                });


                slider_range_chars_count.slider({
                    range: true,
                    min: 200,
                    max: 5000,
                    values: jQuery("#amount-range-chars-count").data('values'),
                    slide: function (event, ui) {

                        if (jQuery('#excerpt_dynamic_around').attr('checked')) {
                            var val1 = parseInt(ui.values[0] / 200);
                            var val2 = parseInt(ui.values[1] / 200);

                            if (val1 <= 0) {
                                val1 = 1;
                            }

                            if (val2 <= 0) {
                                val2 = 1;
                            }
                            //console.log('Chars val1: ' + val1 + ' - val2: ' + val2);

                            slider_range_words.slider("values", [val1, val2]);
                            jQuery("#amount-range-words").val(val1 + " - " + val2);
                        }

                        jQuery("#amount-range-chars-count").val(ui.values[0] + " - " + ui.values[1]);
                        renderView();
                    }
                });


                slider_range_words.slider({
                    range: true,
                    min: 1,
                    max: 25,
                    values: jQuery("#amount-range-words").data('values'),
                    slide: function (event, ui) {

                        if (jQuery('#excerpt_dynamic_around').attr('checked')) {
                            var val1 = parseInt((ui.values[0]) * 200);
                            var val2 = parseInt((ui.values[1]) * 200);


                            if (val1 <= 0) {
                                val1 = 1;
                            }

                            if (val2 <= 0) {
                                val2 = 1;
                            }

                            //console.log('Words val1: ' + val1 + ' - val2:' + val2);

                            slider_range_chars_count.slider("values", [val1, val2]);
                            jQuery("#amount-range-chars-count").val(val1 + " - " + val2);
                        }

                        jQuery("#amount-range-words").val(val1 / 200 + " - " + ui.values[1]);
                        renderView();
                    }
                });

                slider_single_words.slider({
                    min: 1,
                    max: 25,
                    value: jQuery("#amount-single-words").val(),
                    slide: function (event, ui) {
                        renderView();
                        jQuery("#amount-single-words").val(ui.value);
                    }
                });

                slider_passages.slider({
                    min: 0,
                    max: 25,
                    value: jQuery("#amount-passages").val(),
                    slide: function (event, ui) {
                        renderView();
                        jQuery("#amount-passages").val(ui.value);
                    }
                });

                jQuery('#highlighting-title, #highlighting-content').change();


                var weight_sliders = jQuery('.weight-sliders');

                weight_sliders.slider({
                    min: 1,
                    max: 10,
                    slide: function (event, ui) {
                        jQuery('#' + jQuery(this).data('view-id')).val(ui.value);
                    }
                });

                jQuery.each(weight_sliders, function (index, value) {
                    var input_id = '#' + jQuery(value).data('view-id');
                    var input_value = jQuery(input_id).val();
                    jQuery(value).slider("value", input_value);
                });
            }
        }
    })
    .on('change', '#excerpt_dynamic_around, #excerpt_chunk_separator, ' +
        '#amount-max-chars-count, #min_excerpt_around, #max_excerpt_around, ' +
        '#before_comment, #before_page, #before_post', function () {
        renderView();
    })
    .on('submit', '#reindex_sphinx_form', function (event) {
        event.preventDefault();
        index();
        return false;
    })
    .on('change', '#highlighting-title, #highlighting-content', function () {
        var value = jQuery(this).val();
        var type = jQuery(this).data('type');

        switch (value) {
            case 'mark':
                setHighlighting('mark', type);
                break;
            case 'em':
                setHighlighting('em', type);
                break;
            case 'u':
                setHighlighting('u', type);
                break;
            case 'strong':
                setHighlighting('strong', type);
                break;
            case 'text_color':
                setHighlighting('text_color', type);
                break;
            case 'background_color':
                setHighlighting('background_color', type);
                break;
            case 'style':
                setHighlighting('style', type);
                break;
            case 'class':
                setHighlighting('class', type);
                break;
            case 'custom':
                setHighlighting('custom', type);
                break;
        }
    })
    .on('change', '#taxonomy_indexing', function () {
        var value = jQuery(this).attr('checked');
        if (value) {
            jQuery('#taxonomy_indexing_fields').show('slow');
        } else {
            jQuery('#taxonomy_indexing_fields').hide('slow');
        }
    })
    .on('change', '#custom_fields_indexing', function () {
        var value = jQuery(this).attr('checked');
        if (value) {
            jQuery('#custom_fields_to_indexing_div').show('slow');
        } else {
            jQuery('#custom_fields_to_indexing_div').hide('slow');
        }
    })
    .on('change', '#attachments_indexing', function () {
        var value = jQuery(this).attr('checked');
        if (value) {
            jQuery('#attachments_type_for_skip_indexing_div').show('slow');
        } else {
            jQuery('#attachments_type_for_skip_indexing_div').hide('slow');
        }
    })
    .on('click', '#custom_fields_to_indexing_div button', function (event) {
        event.preventDefault();
        var type = jQuery(this).data('indexing');
        var custom_fields_indexing_select = document.getElementById('custom_fields_for_indexing');

        for (var i = 0; i < custom_fields_indexing_select.length; i++) {
            if (type === 'all') {
                custom_fields_indexing_select.options[i].selected = 'selected';
            } else if (type === 'user_fields') {
                if (custom_fields_indexing_select.options[i].value.charAt(0) === '_') {
                    custom_fields_indexing_select.options[i].selected = false;
                } else {
                    custom_fields_indexing_select.options[i].selected = 'selected';
                }

            }
        }
        custom_fields_indexing_select.focus();
    })
    .on('click', '#wizard-indexing', function (event) {
        event.preventDefault();

        jQuery.ajax({
            url: jQuery('#admin-url').data('url') + 'admin-ajax.php',
            data: {
                action: 'start_daemon'
            },
            method: 'POST',
            dataType: 'json',
            success: function (response, status) {
                if (response.results != null) {
                    if (response.results === true) {
                        index(wizardIndexingDone);
                    } else if (response.results.err != null) {
                        jQuery("#indexing-log").html(response.results.err);
                        wizardIndexingDone();
                    }

                }
            }
        });

        return false;
    });

var renderView = debounce(renderSnippetView, 200);

function wizardIndexingDone() {
    jQuery('#wizard-indexing-skip')
        .val('Next')
        .removeClass('button-secondary')
        .removeClass('cancel')
        .addClass('button-primary');
    jQuery('#wizard-indexing').hide();
}

function index(onDone) {
    var progressbar = jQuery("#indexing_progressbar"),
        progressLabel = jQuery(".progress-label"),
        indexing_label = jQuery("#indexed_count"),
        all_count_label = jQuery("#index_all_count"),
        blog_id_label = jQuery("#indexing_blog"),
        indexing_log = jQuery("#indexing-log");

    progressbar.progressbar({
        value: false,
        change: function () {
            progressLabel.text(progressbar.progressbar("value") + "%");
        },
    });

    /**
     * Start indexing
     *
     * Timer sends requests to server every 30 seconds
     * If indexer crashes - next script running continue indexing
     *
     * If request sended but first script still working - new script die's
     *
     * timerResults can cancel timerIndexing if got results about indexing are finished
     *
     * */

    startIndexing();
    var timerIndexing = setInterval(function () {
        startIndexing();
    }, 30000);

    var error_attempts = 0;
    var timerResults = setInterval(function () {

        if (error_attempts >= 3) {
            clearTimeout(timerIndexing);
            clearTimeout(timerResults);
            error_attempts = 0;
        }
        jQuery.ajax({
            url: jQuery('#admin-url').data('url') + 'admin-ajax.php',
            data: {
                action: 'get_indexing_result'
            },
            method: 'POST',
            dataType: 'json',
            success: function (response, status) {
                if (response.results == null || response.results.length == 0 || response.results.status == 'error') {

                    if (response.results.status == 'error') {
                        jQuery('.progress-label').text(response.results.message);
                        clearTimeout(timerIndexing);
                        clearTimeout(timerResults);
                        if (typeof onDone === "function") {
                            onDone();
                        }
                    }

                    error_attempts++;
                    return;
                }


                if (response.results.indexed != 0 && parseInt(response.results.indexed) >= parseInt(response.results.all_count)) {
                    progressbar.progressbar("value", 100);
                    indexing_label.text(response.results.indexed);
                    all_count_label.text(response.results.all_count);

                    if (response.results.blog_id != null || response.results.blog_id !== ''){
                        blog_id_label.text('(Blog #' + response.results.blog_id + ')');
                    } else {
                        blog_id_label.text('');
                    }
                    clearTimeout(timerIndexing);
                    clearTimeout(timerResults);
                    error_attempts = 0;
                    if (typeof onDone === "function") {
                        onDone();
                    }
                } else if (response.results.indexed != 0) {
                    indexing_label.text(response.results.indexed);
                    all_count_label.text(response.results.all_count);
                    if (response.results.blog_id != null || response.results.blog_id !== ''){
                        blog_id_label.text('(Blog #' + response.results.blog_id + ')');
                    } else {
                        blog_id_label.text('');
                    }
                    if (response.results.logs != null) {
                        indexing_log.html(response.results.logs).show();
                    }
                    var persentage = parseInt((response.results.indexed / response.results.all_count).toFixed(2) * 100);
                    progressbar.progressbar("value", persentage);
                } else {
                    if (response.results.logs != null) {
                        indexing_log.html(response.results.logs).show();
                    }
                    progressbar.progressbar("value", 0);
                }
            }
        });
    }, 2000);
}

function setHighlighting(type, to) {
    var before_input, after_input, color_picker = jQuery('#color-picker-' + to);
    var default_colors = {
        text_color: '#c00000',
        background_color: '#ffc375'
    };

    hidePicker(color_picker);

    jQuery('.j-label-' + to).hide('fast');
    jQuery('.j-label-' + to + '-' + type).show('fast');

    if (to === 'title') {
        before_input = jQuery('#before_title_match');
        after_input = jQuery('#after_title_match');
    } else {
        before_input = jQuery('#before_text_match');
        after_input = jQuery('#after_text_match');
    }


    if (type === 'custom') {

        before_input.parentsUntil('tbody').show();
        after_input.parentsUntil('tbody').show();
        before_input.val('<b>');
        after_input.val('</b>');
        renderSnippetView();

    } else if (type === 'style') {

        before_input.val('border: 1px solid black;');
        before_input.parentsUntil('tbody').show();
        after_input.parentsUntil('tbody').hide();
        renderSnippetView();

    } else if (type === 'class') {

        before_input.val('test-highlighting');
        before_input.parentsUntil('tbody').show();
        after_input.parentsUntil('tbody').hide();
        renderSnippetView();

    } else if (type === 'text_color' || type === 'background_color') {

        before_input.parentsUntil('tbody').show();
        showPicker(default_colors[type], color_picker, before_input);
        color_picker.parents('td').show();
        before_input.parents('td').hide();
        after_input.parentsUntil('tbody').hide();

    } else {

        before_input.parentsUntil('tbody').hide();
        after_input.parentsUntil('tbody').hide();
        renderSnippetView();
    }


    before_input.change(function () {
        renderSnippetView();
    });
    after_input.change(function () {
        renderSnippetView();
    })
}

function showPicker(color, picker, input) {
    picker
        .show()
        .val(color)
        .wpColorPicker({
            change: function (event, ui) {
                input.val(ui.color.toString());
                renderView();
            }
        })
        .iris('color', color);
}

function hidePicker(picker) {
    picker.parents('td').hide();
}

function debounce(f, ms) {

    let timer = null;

    return function (...args) {
        const onComplete = () => {
            f.apply(this, args);
            timer = null;
        };

        if (timer) {
            clearTimeout(timer);
        }

        timer = setTimeout(onComplete, ms);
    };
}

function renderSnippetView() {
    var slider_range_words = jQuery("#amount-range-words");
    var slider_single_words = jQuery("#amount-single-words");
    var slider_count_results = jQuery("#count-results");
    var is_dynamic_lenght = jQuery('#excerpt_dynamic_around').attr('checked');
    var max_chars_count = jQuery('#amount-max-chars-count').val();
    if (max_chars_count) {
        jQuery.ajax({
            url: jQuery('#admin-url').data('url') + 'admin-ajax.php',
            data: {
                limit: max_chars_count,
                range_limit: jQuery("#amount-range-chars-count").val(),
                range: slider_range_words.val(),
                single: slider_single_words.val(),
                results: slider_count_results.val(),
                limit_passages: jQuery('#amount-passages').val(),
                separator: jQuery('#excerpt_chunk_separator').val(),
                before_text_match: jQuery('#before_text_match').val(),
                after_text_match: jQuery('#after_text_match').val(),
                before_title_match: jQuery('#before_title_match').val(),
                after_title_match: jQuery('#after_title_match').val(),
                highlighting_title_type: jQuery('#highlighting-title').val(),
                highlighting_text_type: jQuery('#highlighting-content').val(),
                before_comment: jQuery('#before_comment').val(),
                before_page: jQuery('#before_page').val(),
                before_post: jQuery('#before_post').val(),
                dynamic: is_dynamic_lenght,
                action: 'get_snippet'
            },
            method: 'POST',
            dataType: 'json',
            success: function (response, status) {
                jQuery('#live-example-snippet').empty();
                if (response.status != null && response.status == 'success') {
                    jQuery.each(response.result, function (index, value) {
                        jQuery('#live-example-snippet').append(value);
                    });

                } else {
                    jQuery('#live-example-snippet').html('<div class="snippet-sample-error">' + response.message + '</div>');
                }

            }
        });
    }
}

function startIndexing() {
    jQuery.ajax({
        url: jQuery('#admin-url').data('url') + 'admin-ajax.php',
        data: {
            action: 'start_indexing'
        },
        method: 'POST',
        dataType: 'json',
        success: function (response, status) {
            if (response.status != null && response.status == 'error' && response.message != null) {
                alert(response.message);
            }
        }
    });
}