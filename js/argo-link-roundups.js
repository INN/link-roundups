var AL = AL || {};

(function() {
    var $ = jQuery;

    if (typeof AL.instances == 'undefined')
        AL.instances = {};

    AL.CreateCampaignModal = AL.Modal.extend({
        actions: {
            'Yes': 'createCampaign',
            'Cancel': 'close'
        },

        initialize: function(options) {
            this.content = 'Are you sure you want to create a new MailChimp campaign?';
            return AL.Modal.prototype.initialize.apply(this, arguments);
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

                        var model = new Backbone.Model(data.data);
                        AL.instances.campaignSuccessModal = new AL.CampaignCreatedModal({ model: model });
                        AL.instances.campaignSuccessModal.render();
                        updateCreateCampaignButton(model);
                    },
                    error: function() {
                        self.hideSpinner();
                        self.close();
                        alert('Something went wrong. Please try again.');
                    }
                };

            var data = {
                action: 'argo_links_create_mailchimp_campaign',
                security: AL.ajax_nonce,
                post_id: AL.post_id
            };

            opts.data = data;

            this.showSpinner();
            this.ongoing = $.ajax(opts);

            return false;
        }
    });

    AL.CampaignCreatedModal = AL.Modal.extend({
        actions: {
            'Close': 'close'
        },

        initialize: function(options) {
            var model = options.model;

            this.content = 'Successfully created a new MailChimp campaign!<br />' +
                '<a target="blank" href="https://' + AL.mc_api_endpoint +
                '.admin.mailchimp.com/campaigns/wizard/confirm?id=' + model.get('web_id') +
                '">Click here to edit your campaign in MailChimp.</a>';

            return AL.Modal.prototype.initialize.apply(this, arguments);
        }
    });

    var updateCreateCampaignButton = function(model) {
        var markup = '<p>A MailChimp roundup campaign exists for thist post.</p>' +
            '<a class="button" target="blank" href="https://' + AL.mc_api_endpoint +
            '.admin.mailchimp.com/campaigns/wizard/confirm?id=' + model.get('web_id') +
            '">Edit in MailChimp.</a>';

        var campaign_button = $('#argo-links-create-mailchimp-campaign'),
            parent = campaign_button.parent();

        campaign_button.remove();
        parent.html(markup);
    }

    $(document).ready(function() {
        $('#argo-links-create-mailchimp-campaign').click(function() {
            if (typeof $(this).attr('disabled') == 'undefined') {
                if (typeof AL.instances.campaignModal == 'undefined')
                    AL.instances.campaignModal = new AL.CreateCampaignModal();

                AL.instances.campaignModal.render();
            }
            return false;
        });
    })
})();
