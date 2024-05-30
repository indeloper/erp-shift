var show = function (state) {
  document.getElementById('modalForm').style.display = state;
  document.getElementById('filter').style.display = state;
}


// popover
 $('[data-toggle="popover"]').popover({
   container: 'body'
 });

 $('body').on('click', function (e) {
         if ($(e.target).data('toggle') !== 'popover'
             && $(e.target).parents('[data-toggle="popover"]').length === 0
             && $(e.target).parents('.popover.in').length === 0) {
             $('[data-toggle="popover"]').popover('hide');
         }
     });
