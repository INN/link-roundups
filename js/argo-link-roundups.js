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
                        console.log('Then we will open another modal that says "edit on MailChimp"');
                    },
                    error: function() {
                        self.hideSpinner();
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
            return this.ongoing;
        }
    });

    $(document).ready(function() {
        $('#argo-links-create-mailchimp-campaign').click(function() {
            if (typeof AL.instances.campaignModal == 'undefined')
                AL.instances.campaignModal = new AL.CreateCampaignModal();

            AL.instances.campaignModal.render();
            return false;
        });
    })
})();
