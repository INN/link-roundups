var SL = SL || {};

(function() {
    var $ = jQuery;

    // Views
    SL.BaseView = Backbone.View.extend({
        showSpinner: function() {
            this.$el.find('.spinner').css('display', 'inline-block');
        },

        hideSpinner: function() {
            this.$el.find('.spinner').css('display', 'none');
        }
    });

    SL.Modal = SL.BaseView.extend({
        actions: null,

        content: null,

        events: {
            "click .close": "close"
        },

        initialize: function(options) {
            var self = this;

            this.$el.addClass('argo-links-modal');

            Backbone.View.prototype.initialize.apply(this, arguments);
            this.template = _.template($('#argo-links-modal-tmpl').html());

            if (!this.content)
                this.content = (typeof options.content !== 'undefined')? options.content : '';

            if (!this.actions)
                this.actions = (typeof options.actions !== 'undefined')? options.actions : {};

            this.setEvents();

            $('body').append(this.$el);
            if ($('#argo-links-modal-overlay').length == 0)
                $('body').append('<div id="argo-links-modal-overlay" />');

            return this;
        },

        render: function() {
            this.$el.html(this.template({
                content: this.content,
                actions: this.actions
            }));
            this.setEvents();
            this.open();
        },

        setEvents: function() {
            var events = {};
            _.each(this.actions, function(v, k) { events['click .' + k] = v; });
            this.delegateEvents(_.extend(this.events, events));
        },

        open: function() {
            $('body').addClass('argo-links-modal-open');
            this.$el.removeClass('hide');
            this.$el.addClass('show');
            return false;
        },

        close: function() {
            $('body').removeClass('argo-links-modal-open');
            this.$el.removeClass('show');
            this.$el.addClass('hide');
            return false;
        }
    });

})();
