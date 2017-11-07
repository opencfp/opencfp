function Talk(id, $el) {
  this.$el = $el;
  this.id = id;
  this.baseUrl = '/admin/talks/';
};

Talk.prototype.favorite = function() {
  var _this = this;
  var url = this.baseUrl + this.id + '/favorite';
  var data = { id: this.id };

  if (this.$el.find('i').hasClass('text-orange-dark')) {
    data.delete = true;
  }

  $.ajax({
    type: "POST",
    url: url,
    data: data,
    success: function() {
      _this.$el.find('i').toggleClass('text-orange-dark');
    },
    error: _this.onError
  });
};

Talk.prototype.select = function() {
  var _this = this;
  var url = this.baseUrl + this.id + '/select';
  var data = { id: this.id };

  if (this.$el.find('i').hasClass('text-indigo-dark')) {
    data.delete = true;
  }

  $.ajax({
    type: "POST",
    url: url,
    data: data,
    success: function() {
      _this.$el.find('i').toggleClass('text-indigo-dark');
    },
    error: _this.onError
  });
};

Talk.prototype.rate = function(rating) {
  var _this = this;
  var url = this.baseUrl + this.id + '/rate';
  var data = { id: this.id, rating: rating };

  if (this.$el.find('i').hasClass('selected')) {
    data.rating = 0;
  }

  $.ajax({
    type: "POST",
    url: url,
    data: data,
    success: function() {
      if (data.rating === -1) {
        console.log('here')
        $('#talk-downvote-' + _this.id + ' i').addClass('text-red-dark');
        $('#talk-upvote-' + _this.id + ' i').removeClass('text-green-dark');
      }

      if (data.rating === 1) {
        $('#talk-upvote-' + _this.id + ' i').addClass('text-green-dark');
        $('#talk-downvote-' + _this.id + ' i').removeClass('text-red-dark');
      }

      if (data.rating === 0) {
        $('#talk-upvote-' + _this.id + ' i').removeClass('text-green-dark');
        $('#talk-downvote-' + _this.id + ' i').removeClass('text-red-dark');
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
