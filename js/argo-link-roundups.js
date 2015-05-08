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
            console.log('make a camapgin go go go');
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
