//Favorite Listener
$('.js-talk-favorite').on('click', function(e) {
  var talk = new Talk($(this).data('id'), $(this));
  e.preventDefault();
  talk.favorite();
});

//Select Listener
$('.js-talk-select').on('click', function(e) {
  var talk = new Talk($(this).data('id'), $(this));
  e.preventDefault();
  talk.select();
});

//Rating Listeners
//Yes No Rating listener
$('.js-talk-rating-yes-no').on('click', function(e) {
  var id = $(this).data('id');
  var talk = new Talk($(this).data('id'), $(this), $(this).data());
  var rating = $(this).data('rating');
  e.preventDefault();
  if (talk.rate(rating)) {
    if (rating === -1) {
      $('#talk-downvote-' + id + ' i').addClass('text-red-dark');
      $('#talk-upvote-' + id + ' i').removeClass('text-green-dark');
    } else  if (rating === 1) {
      $('#talk-upvote-' + id + ' i').addClass('text-green-dark');
      $('#talk-downvote-' + id + ' i').removeClass('text-red-dark');
    } else if (rating === 0) {
      $('#talk-upvote-' + id + ' i').removeClass('text-green-dark');
      $('#talk-downvote-' + id + ' i').removeClass('text-red-dark');
    }
  }
});

//One to Ten Rating Listener.
$('.js-talk-rating-one-to-ten').on('change', function () {
  var talk = new Talk($(this).data('id'), $(this));
  talk.rate($(this).val())
});
