function Talk(id, $el) {
  this.$el = $el;
  this.id = id;
  this.baseUrl = '/admin/talks/';
};

Talk.prototype.favorite = function() {
  var _this = this;
  var url = this.baseUrl + this.id + '/favorite';
  var data = { id: this.id };

  if (this.$el.find('i').hasClass('admin-icon--selected')) {
    data.delete = true;
  }

  $.ajax({
    type: "POST",
    url: url,
    data: data,
    success: function() {
      _this.$el.find('i').toggleClass('admin-icon--selected');
    },
    error: _this.onError
  });
};

Talk.prototype.select = function() {
  var _this = this;
  var url = this.baseUrl + this.id + '/select';
  var data = { id: this.id };

  if (this.$el.find('i').hasClass('admin-icon--selected')) {
    data.delete = true;
  }

  $.ajax({
    type: "POST",
    url: url,
    data: data,
    success: function() {
      _this.$el.find('i').toggleClass('admin-icon--selected');
    },
    error: _this.onError
  });
};

Talk.prototype.rate = function(rating) {
  var _this = this;
  var url = this.baseUrl + this.id + '/rate';
  var data = { id: this.id, rating: rating };

  if (this.$el.find('i').hasClass('admin-icon--selected')) {
    data.rating = 0;
  }

  $.ajax({
    type: "POST",
    url: url,
    data: data,
    success: function() {
      if (data.rating === -1) {
        $('#talk-downvote-' + _this.id + ' i').addClass('admin-icon--selected');
        $('#talk-upvote-' + _this.id + ' i').removeClass('admin-icon--selected');
      }

      if (data.rating === 1) {
        $('#talk-upvote-' + _this.id + ' i').addClass('admin-icon--selected');
        $('#talk-downvote-' + _this.id + ' i').removeClass('admin-icon--selected');
      }

      if (data.rating === 0) {
        $('#talk-upvote-' + _this.id + ' i').removeClass('admin-icon--selected');
        $('#talk-downvote-' + _this.id + ' i').removeClass('admin-icon--selected');
      }
    },
    error: _this.onError
  });
};

Talk.prototype.onError = function(xhr, status, errorMessage) {
  console.log(status + ': ' + errorMessage);
};

// Add Listeners
$('.js-talk-rating').on('click', function(e) {
    var talk = new Talk($(this).data('id'), $(this));
    var rating = $(this).data('rating');
    e.preventDefault();
    talk.rate(rating);
});

$('.js-talk-favorite').on('click', function(e) {
    var talk = new Talk($(this).data('id'), $(this));
    e.preventDefault();
    talk.favorite();
});

$('.js-talk-select').on('click', function(e) {
    var talk = new Talk($(this).data('id'), $(this));
    e.preventDefault();
    talk.select();
});