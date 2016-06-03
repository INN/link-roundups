// Mailchimp Backbone Modal
var LR = LR || {};

(function() {
    var $ = jQuery;

    // Views
    LR.BaseView = Backbone.View.extend({
        showSpinner: function() {
            this.$el.find('.spinner').css('display', 'inline-block');
            this.$el.find('.spinner').css('visibility', 'visible');
        },

        hideSpinner: function() {
            this.$el.find('.spinner').css('display', 'none');
            this.$el.find('.spinner').css('visibility', 'hidden');
        }
    });

    LR.Modal = LR.BaseView.extend({
        actions: null,

        content: null,

        events: {
            "click .close": "close"
        },

        initialize: function(options) {
            var self = this;

            this.$el.addClass('lroundups-modal');

            Backbone.View.prototype.initialize.apply(this, arguments);
            this.template = _.template($('#lroundups-modal-tmpl').html());

            if (!this.content)
                this.content = (options && typeof options.content !== 'undefined')? options.content : '';

            if (!this.actions)
                this.actions = (options && typeof options.actions !== 'undefined')? options.actions : {};

            this.setEvents();

            $('body').append(this.$el);
            if ($('#lroundups-modal-overlay').length == 0)
                $('body').append('<div id="lroundups-modal-overlay" />');

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
            $('body').addClass('lroundups-modal-open');
            this.$el.removeClass('hide');
            this.$el.addClass('show');
            return false;
        },

        close: function() {
          if ($('.lroundups-modal').length <= 1) {
            $('body').removeClass('lroundups-modal-open');
          }
          this.$el.removeClass('show');
          this.$el.addClass('hide');
          this.$el.remove()
          return false;
        },

        hide: function() {
          this.$el.hide();
        },

        show: function() {
          this.$el.show();
        }
    });

})();
