(function($){
  var scrollheight = 0;

$(function(){
  scrollheight = $(window).scrollTop();

  $('.input-sortable .panel-body').sortable({
    items : '.parent-sortable'
  });
  
  $('.fdl_modal').on('show.bs.modal', function (e) {
    scrollheight = $(window).scrollTop();
    $('html').addClass('modal-open');
    $('.rex-page').css('margin-top',-scrollheight);
  }).on('hide.bs.modal', function (e) {
    $('html').removeClass('modal-open');
    setTimeout(function(){
      $('.rex-page').css('margin-top','');
      $('body,html').scrollTop(scrollheight);
    },500);
  });
});})(jQuery);