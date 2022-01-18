$(document).ready(function() {
    document.getElementById('wishlist-button').onclick = function (event) {
        var gameID = $(this).val();

        $.ajax({
            type: 'DELETE',
            url: '/user/wishlist/' + gameID,
            contentType: 'application/json;charset=utf-8'
        })
            .done(function(data) {
                /*$.ajax({
                    url: '/user/wishlist/',
                    type: 'GET',
                    contentType: 'application/json;charset=utf-8'
                })*/
                console.log("AJAX");
            })
            .fail(function(error) {
                console.log(error);
            });

        // stop the form from submitting the normal way and refreshing the page
        event.preventDefault();
    }
});