// $('table .href').dblclick(function(){
//     window.location = $(this).attr('data-href');
//     return false;
// });

var touchtime = 0;
$("table .href").on("click", function() {
    if (touchtime == 0) {
        // set first click
        touchtime = new Date().getTime();
    } else {
        // compare first click to this click and see if they occurred within double click threshold
        if (((new Date().getTime()) - touchtime) < 800) {
            // double click occurred
            window.location = $(this).attr('data-href');
            return false;
            touchtime = 0;
        } else {
            // not a double click so set as a new first click
            touchtime = new Date().getTime();
        }
    }
});
