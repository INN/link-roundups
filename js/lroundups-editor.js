(function() {
  var $ = jQuery,
      shortcode_string = 'roundup';

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

    popupwindow: function(editor, values, onsubmit_callback) {
      console.log('edit');
    }
  };

  wp.mce.views.register(shortcode_string, wp.mce.roundup);

})();
