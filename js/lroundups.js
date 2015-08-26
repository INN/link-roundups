var LR = LR || {};

(function() {
    var $ = jQuery;

    if (typeof LR.instances == 'undefined')
        LR.instances = {};

    LR.CreateCampaignModal = LR.Modal.extend({
        actions: {
            'Yes': 'createCampaign',
            'Cancel': 'close'
        },

        initialize: function(options) {
            this.content = 'Are you sure you want to create a new MailChimp campaign?';
            return LR.Modal.prototype.initialize.apply(this, arguments);
        },

        createCampaign: function() {
            if (typeof this.ongoing !== 'undefined' && $.inArray(this.ongoing.state(), ['resolved', 'rejected']) == -1)
                return false;

            var self = this,
                opts = {
                    url: ajaxurl,
                    dataType: 'json',
                    method: 'post',
                    success: function(data) {
                        self.hideSpinner();
                        self.close();

                        if (data.success) {
                            var model = new Backbone.Model(data.data);
                            LR.instances.campaignSuccessModal = new LR.CampaignCreatedModal({ model: model });
                            LR.instances.campaignSuccessModal.render();
                            updateCreateCampaignButton(model);
                        } else {
                            new LR.Modal({
                                className: 'lroundups-error-modal',
                                content: "Failed to create MailChimp Campaign. Error message:<br /><strong>" + data.message + "</strong>",
                                actions: {
                                    'Close': 'close'
                                }
                            }).render();
                        }
                    },
                    error: function() {
                        self.hideSpinner();
                        self.close();
                        new LR.Modal({
                            className: 'lroundups-error-modal',
                            content: 'Something went wrong. Please try again.',
                            actions: {
                                'Close': 'close'
                            }
                        }).render();
                    }
                };

            var data = {
                action: 'lroundups_create_mailchimp_campaign',
                security: LR.ajax_nonce,
                post_id: LR.post_id
            };

            opts.data = data;

            this.showSpinner();
            this.ongoing = $.ajax(opts);

            return false;
        }
    });

    LR.CampaignCreatedModal = LR.Modal.extend({
        actions: {
            'Close': 'close'
        },

        initialize: function(options) {
            var model = options.model;

            this.content = 'New MailChimp campaign created successfully!<br />' +
                '<a target="blank" href="https://' + LR.mc_api_endpoint +
                '.admin.mailchimp.com/campaigns/wizard/confirm?id=' + model.get('web_id') +
                '">Click here to edit your campaign in MailChimp.</a>';

            return LR.Modal.prototype.initialize.apply(this, arguments);
        }
    });

    var updateCreateCampaignButton = function(model) {
        var markup = '<p>A MailChimp roundup campaign exists for this post.</p>' +
            '<a class="button" target="blank" href="https://' + LR.mc_api_endpoint +
            '.admin.mailchimp.com/campaigns/wizard/confirm?id=' + model.get('web_id') +
            '">Edit in MailChimp.</a>';

        var campaign_button = $('#link-roundups-create-mailchimp-campaign'),
            parent = campaign_button.parent();

        campaign_button.remove();
        parent.html(markup);
    }

    $(document).ready(function() {
        $('#link-roundups-create-mailchimp-campaign').click(function() {
            if (typeof $(this).attr('disabled') == 'undefined') {
                if (typeof LR.instances.campaignModal == 'undefined')
                    LR.instances.campaignModal = new LR.CreateCampaignModal();

                LR.instances.campaignModal.render();
            }
            return false;
        });

        /**
         *  Some helpful stuff for the default link html option
         *
         * @since 0.3.2
         */
        if (typeof LROUNDUPS_DEFAULT_LINK_HTML !== 'undefined') {
            $('.lroundups-restore-default-html').click(function() {
                if ($(this).is(':disabled'))
                    return;

                var val = $('<div />').html(LROUNDUPS_DEFAULT_LINK_HTML).html();
                $('[name="lroundups_custom_html"]').val(val);
                $(this).attr('disabled', true);
                return false;
            });

            var check_restore_button = function() {
                if ($('[name="lroundups_custom_html"]').val() !== LROUNDUPS_DEFAULT_LINK_HTML)
                    $('.lroundups-restore-default-html').removeAttr('disabled');
            };

            check_restore_button();

            $('[name="lroundups_custom_html"]').on('input propertychange', check_restore_button);
        }

        /**
         * From a checkbox element, find the post ID and title, and return a WP shortcode.
         *
         * @since 0.3.2
         */
        var link_roundups_get_shortcode = function(checkbox) {
            var row = $(checkbox).parent().parent(),
            post_id = row.data('post-id'),
            title = row.find('.column-title').text();
            return '[rounduplink id="' + post_id + '" title="' + title + '"]';
        };

        /**
         * When "Send to Editor" is clicked, send checked stories to the editor
         * Also, do not reload the page
         *
         * @since 0.3.2
         * @uses link_roundups_get_shortcode
         */
        $('body').on('click', '.append-saved-links', function(){
            // find all the roundups links in the table, and send them to the editor if they're checked
            $('.lroundups-link .cb-select').each(function(){
                if ($(this).is(":checked"))
                    send_to_editor(link_roundups_get_shortcode(this));
            });
            return false;
        });

        /**
         * If an <a> inside the "Recent Saved Links" div is clicked, submit its href to this file and display the response.
         *
         * @since 0.1
         */
        $('body').on('click', '#link_roundups_roundup thead a, #link_roundups_roundup tfoot a', function() {
            var url = $(this).attr('href'),
                query = url.match(/(\?)(.*)$/)[2],
                date = $('#link_roundups_roundup').find('select[name="link_date"]').val();

            query += '&link_date=' + date + '&action=lroundups_saved_links_list_table_render';

            $.get(ajaxurl, query).done(function(response) {
                $('#lroundups-display-area').html(response);
            });

            return false;
        });

        /**
         * When "Filter Links" is clicked, fill the table display area with the HTML produced by this file, when supplied with the query args.
         */
        $('body').on('click', '#filter_links', function() {
            var self= $(this),
                parentEl = self.parent();

            self.find(".spinner").css('visibility','visible');

            var query = 'link_date=' + parentEl.find('select[name="link_date"]').val()
                        + '&action=lroundups_saved_links_list_table_render';

            $.post(ajaxurl, query, function(response) {
                $('#lroundups-display-area').html(response);
                self.find(".spinner").css('visibility','hidden');
            });

            return false;
        });

        /**
         * Check all the checkboxes if the "Check all boxes" checkbox is checked, and if it's unchecked, uncheck all the checkboxes.
         */
        $('body').on('click', '#cb-select-all-1, #cb-select-all-2', function(){
            if ($(this).is(':checked')) {
                $('.lroundups-links input[type=checkbox]').each(function(){
                    $(this).prop("checked", true);
                });
            } else {
                $('.lroundups-links input[type=checkbox]').each(function(){
                    $(this).prop("checked", false);
                });
            }
        });
    })
})();
