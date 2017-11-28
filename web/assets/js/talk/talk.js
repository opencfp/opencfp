function Talk(id, $el) {
  this.$el = $el;
  this.id = id;
  if (location.href.includes('/admin/')) {
    url = '/admin/talks/';
  }
  if (location.href.includes('/reviewer/')) {
    url = '/reviewer/talks/';
  }
  this.baseUrl = url;
}

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
      return true;
    },
    error: _this.onError
  });
  return true;
};

Talk.prototype.onError = function(xhr, status, errorMessage) {
  console.log(status + ': ' + errorMessage);
};
