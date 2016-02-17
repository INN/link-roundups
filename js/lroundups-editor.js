(function() {
  var $ = jQuery,
      shortcode_string = 'roundup';

  var RoundupBlockModal = LR.Modal.extend({
    content: '',

    actions: {
      'Save': 'save',
      'Close': 'close'
    },

    render: function() {
      var tmpl = _.template($('#lroundups-post-tmpl').html());
      this.content = tmpl({
        title: this.title,
        posts: this.posts
      });
      return LR.Modal.prototype.render.apply(this, arguments);
    }

  });

  wp.mce.roundup = {
    shortcode_data: {},

    template: _.template(
      '<div class="lr-block">' +
        '<% if (typeof name !== "undefined" ) { %><%= name %><% } else { %>Link Roundup Block<% } %>' +
      '</div>'
    ),

    getContent: function() {
      var options = this.shortcode.attrs.named;
      options['innercontent'] = this.shortcode.content;
      return this.template(options);
    },

    edit: function(data, update) {
      var shortcode_data = wp.shortcode.next(shortcode_string, data);
      var values = shortcode_data.shortcode.attrs.named;
      values['innercontent'] = shortcode_data.shortcode.content;
      wp.mce.roundup.popupwindow(tinyMCE.activeEditor, values);
    },

    popupwindow: function(editor, values) {
      $.ajax({
        url: ajaxurl,
        dataType: 'json',
        data: {
          action: 'roundup_block_posts'
        },
        success: function(data) {
          var modal = new RoundupBlockModal();
          modal.posts = new Backbone.Collection(data);
          modal.title = values.name;
          modal.render();
        }
      });
    }
  };

  wp.mce.views.register(shortcode_string, wp.mce.roundup);

})();
