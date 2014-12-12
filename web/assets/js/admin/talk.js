function Talk(id, $el) {
  this.$el = $el;
  this.id = id;
  this.baseUrl = '/admin/talks/';
};

var queryParams = queryString.parse(location.search);

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

  $.ajax({
    type: "POST",
    url: url,
    data: data,
    success: function() {
      if (rating === -1) {
        $('#talk-downvote-' + _this.id + ' i').addClass('admin-icon--selected');
        $('#talk-upvote-' + _this.id + ' i').removeClass('admin-icon--selected');
      }

      if (rating === 1) {
        $('#talk-upvote-' + _this.id + ' i').addClass('admin-icon--selected');
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

$('.sort').on('click', function(e) {
    var $cell = $(this);
    var sort;

    if (!queryParams.hasOwnProperty('sort')) {
      sort = 'DESC';
    }

    if (queryParams.sort == 'ASC') {
      sort = 'DESC';
    }

    if (queryParams.sort == 'DESC') {
      sort = 'ASC';
    }

    queryParams.sort = sort;
    queryParams.order_by = $cell.data('field');

    location.href = location.pathname + '?' + queryString.stringify(queryParams);
});

$(function() {
  if (queryParams.hasOwnProperty('sort') && queryParams.hasOwnProperty('order_by')) {
    $('.sort[data-field="' + queryParams.order_by + '"]').addClass('sort--' + queryParams.sort.toLowerCase());
  }
});