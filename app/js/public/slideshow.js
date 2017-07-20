$(function(){
    $('#carousel').carousel({
        interval: 4000
    });
    $('.carousel-inner').find('.item').first().addClass('active');
    $('.carousel-indicators').find('li').first().addClass('active');
    $('.carousel-control.left').live('click',function(){
        $('#carousel').carousel('prev')
    })
    $('.carousel-control.right').live('click',function(){
        $('#carousel').carousel('next')
    })
    $('#carousel').find('.carousel-caption h4').each(function(){
        if($(this).text() == ""){
            $(this).remove()
        }
    })
    $('#carousel').find('.carousel-caption p').each(function(){
        if($(this).text() == ""){
            $(this).remove()
        }
    })
    $('#carousel').bind('slid',function(){
        $('.carousel-caption h4').animate({
            left: "15px"
        }, 350);
        $('.carousel-caption p').animate({
            top: "50px"
        }, 350)
    })
    $('#carousel').bind('slide',function(){
        $('.carousel-caption h4').animate({
            left: "-800px"
        } , "fast");
        $('.carousel-caption p').animate({
            top: "-150px"
        },"fast")
    })    
})