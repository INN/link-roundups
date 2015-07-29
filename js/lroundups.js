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
    })
})();
