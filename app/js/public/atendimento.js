$(function(){
    //baseUri
    var baseUri = $('base').attr('href').replace('/app/',''); 
    //hide form busca
    $('.form-busca').hide();
})
function messageOk()
{
    $(' <span> Recebemos sua mensagem e retornaremos em breve! Obrigado</span>')
    .insertAfter('.message_default i')
    $('.message_default').addClass('well').addClass('well-small');
    $('.message_default').fadeIn();
    $('#f-atd').fadeOut(1000);
    setTimeout(function(){
        $('#f-atd').fadeIn(3000,function(){
            $('.message_default').slideUp(500);
        });
    },5000)
}
function messageError()
{
    $('.message_default').removeClass('well').removeClass('well-small');
    $(' <span> Houve um erro ao enviar a mensagem. Por favor tente novamente mais tarde!</span>')
    .insertAfter('.message_default i')
    $('.message_default').addClass('alert').addClass('alert-danger');
    $('.message_default').fadeIn();
    $('#f-atd').fadeOut(1000);
    setTimeout(function(){
        $('#f-atd').fadeIn(3000,function(){
            $('.message_default').slideUp(500);
        });
    },5000)             
}