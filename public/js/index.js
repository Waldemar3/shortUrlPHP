$(document).ready(function(){
    $('button').click(function(){
        const url = $('input').val();
        
        $.ajax({
            url: '/shorten-url.php',
            method: 'post',
            dataType: 'text',
            data: {url},
            success: function(code){
                 $('#originalUrl').text(url);
                 $('#shortUrl').text(window.location.origin + '/' + code);
            },
            error: function(xhr) {
              alert(xhr.responseText);
            },
        });
    });
});