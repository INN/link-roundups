(function() {
  var $ = jQuery,
      shortcode_string = 'roundup_block';

  /* Modal used for editing the contents of an editable block */
  var RoundupBlockModal = LR.Modal.extend({
    content: '',

    searchFields: [
      'post_title',
      'post_content',
      'post_excerpt'
    ],

    addedPosts: new Backbone.Collection(),

    actions: {
      'Save': 'save',
      'Close': 'close'
    },

    events: {
      'click .remove': 'removePost',
      'click .close': 'close'
    },

    render: function() {
      var self = this,
          tmpl = _.template($('#lroundups-posts-tmpl').html()),
          existingPosts = [];

      this.content = tmpl({
        name: this.name,
        hasPosts: (typeof this.ids !== 'undefined')
      });

      LR.Modal.prototype.render.apply(this, arguments);

      this.showSpinner();

      if (this.posts.length <= 0)
        return;

      if (typeof this.ids !== 'undefined') {
        var ids = this.ids.split(',');

        _.each(ids, function(id, idx) {
          var p = self.posts.findWhere({ "ID": Number(id) });
          if (p) {
            existingPosts.push(p);
          }
        });
      }

      if (existingPosts.length > 0) {
        this.addedPosts.add(existingPosts);
        this.posts.remove(existingPosts);
      }

      this.renderAdded();
      this.renderAvailable();
      this.setupTypehead();
      this.connectSortables();
      this.hideSpinner();
      return this;
    },

    save: function(e) {
      var ids = _.map(this.$el.find('.added-posts li'), function(el, idx) { return $(el).data('id'); });
      var s = '[' + shortcode_string + ' ids="' + ids.join(',') + '" name="' + this.name + '"]';
      this.editor.insertContent(s);
      this.editor.focus();
      this.close();
    },

    renderAvailable: function() {
      if (this.posts.length <= 0)
        return false;

      this.$el.find('.loading').remove();

      var tmpl = _.template($('#lroundups-post-tmpl').html()),
          content = tmpl({ posts: this.posts });
      this.$el.find('.available-posts').html(content);

      if (typeof this.lastQuery != 'undefined' && this.lastQuery) {
        this.queryCallback(this.lastQuery);
      }
    },

    renderAdded: function() {
      if (this.addedPosts.length <= 0) {
        this.$el.find('.added-posts').html('<li class="no-posts">No items have been added.</li>')
        return false;
      }

      var tmpl = _.template($('#lroundups-post-tmpl').html()),
          content = tmpl({ posts: this.addedPosts });
      this.$el.find('.added-posts').html(content);
    },

    connectSortables: function() {
      var self = this;

      this.$el.find('.sortable').sortable({
        connectWith: '.connected',
        placeholder: "ui-state-highlight"
      });

      this.$el.find('.sortable').on('sortstart', function(event, ui) {
        $('body').addClass('sorting');
      });
      this.$el.find('.sortable').on('sortdeactivate', function(event, ui) {
        $('body').removeClass('sorting');
      });

      this.$el.find('.sortable').on('sortreceive', function(event, ui) {
        var target = $(event.target),
            item =  ui.item,
            post;

        if (target.hasClass('added-posts')) {
          target.find('.no-posts').remove();
          post = self.posts.findWhere({ "ID": item.data('id') });
          self.addedPosts.add([post]);
          self.posts.remove([post]);
          self.renderAdded();
        } else if (target.hasClass('available-posts')) {
          post = self.addedPosts.findWhere({ "ID": item.data('id') });
          self.posts.add([post]);
          self.addedPosts.remove([post]);
        }
      });
    },

    addPost: function(event) {
      var self = this,
          target = $(event.currentTarget);

      var postToAdd = self.posts.findWhere({ "ID": target.data('id') });
      self.addedPosts.add([postToAdd]);
      return false;
    },

    removePost: function(event) {
      var target = $(event.currentTarget),
          post = this.addedPosts.findWhere({ "ID": target.data('id') }),
          item = target.closest('li');

      this.posts.add([post]);
      this.addedPosts.remove([post]);
      this.renderAvailable();
      item.remove();
      return false;
    },

    setupTypehead: function() {
      var self = this;

      self.$el.find('.typeahead').typeahead({
        minLength: 3,
        highlight: true
      }, {
        name: 'posts',
        source: self.queryCallback.bind(self)
      });

      self.$el.find('.typeahead').removeAttr('disabled');

      self.$el.find('.typeahead').on('input', function() {
        if ($(this).val() == '') {
          self.$el.find('.available-posts li').show();
          self.lastQuery = null;
        }
      });
    },

    queryCallback: function(query, syncResults, asyncResults) {
      var self = this,
          results = {};

      query = query.toLowerCase();

      self.posts.each(function(post) {
        _.each(self.searchFields, function(val, idx) {
          if ( post.get(val).toLowerCase().indexOf( query ) >= 0 ) {
            if ( ! results[post.get('ID')] ) {
              results[post.get('ID')] = post;
            }
          }
        });
      });

      self.filterList(results);
      self.lastQuery = query;
    },

    filterList: function(results) {
      var self = this,
          ids = _.keys(results);

      self.$el.find('.available-posts li').hide();
      self.$el.find('.available-posts li').each(function() {
        if (_.indexOf(ids, String($(this).data('id'))) >= 0) {
          $(this).show();
        }
      });
    }

  });

  /* TinyMCE editable roundup blocks */
  wp.mce.roundup_block = {

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
      wp.mce.roundup_block.fetchStories(tinyMCE.activeEditor, values);
    },

    fetchStories: function(editor, values) {
      var self = this,
          existingIds = [];

      self.values = values;
      self.editor = editor;

      if (typeof values.ids !== 'undefined') {
        existingIds = values.ids.split(',');
      }

      self.setupModal();

      $.ajax({
        url: ajaxurl,
        dataType: 'json',
        method: 'post',
        data: {
          action: 'roundup_block_posts',
          existingIds: existingIds,
          security: LR.ajax_nonce
        },
        success: function(data) {
          self.modal.posts.reset(data);
          //self.setupModal.call(self, data);
        }
      });
    },

    setupModal: function(data) {
      this.modal = new RoundupBlockModal();

      // Posts available to select
      this.modal.posts = new Backbone.Collection(data);
      this.modal.posts.comparator = 'order';
      this.modal.posts.on('reset', this.modal.render.bind(this.modal));

      // Collection to track posts added to this block
      this.modal.addedPosts = new Backbone.Collection();

      // The name and ids attributes of the shortcode passed to the modal
      this.modal.name = this.values.name;
      this.modal.ids = this.values.ids;

      // The TinyMCE editor
      this.modal.editor = this.editor;

      // Render and open
      this.modal.render();
    }
  };

  $(document).ready(function() {
    $.getScript(LR.plugin_url + '/js/vendor/typeahead.js/dist/typeahead.jquery.min.js', function() {
      wp.mce.views.register(shortcode_string, wp.mce.roundup_block);
    });
  });

})();
