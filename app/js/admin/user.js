$(function(){
    //baseUri
    $('head').append('<script src="js/default/baseuri.js" type="text/javascript"></script>');
    //stupidtable
    if($(".table").length >= 1){
        $(".table").stupidtable();
    }
    //editar
    $('.edit').live('click',function(){
        var id = $(this).attr('id');
        window.location = baseUri+'/admin/usuario/editar/'+id+'/';
    })
    //cancel
    $('.cancel').live('click',function(){
        window.location = baseUri+'/admin/usuario/';
    })
    //remove
    $('.remove').live('click',function(){
        var id = $(this).attr('id');
        $('#modal-remove').modal('show');
        var url = baseUri +'/admin/usuario/remover/'+id+'/';
        $('#btn-remove').attr('href',url);
    })        
})

function validaAdd()
{
    if($.trim($('#user_name').val()) == "")
    {
        $('#user_name').addClass('invalid');
        $('#user_name').focus();
        return false;
    }
    $('#user_name').removeClass('invalid');
    
    if($.trim($('#user_email').val()) == "")
    {
        $('#user_email').addClass('invalid');
        $('#user_email').focus();
        return false;
    }
    
    var er = new RegExp(/^[A-Za-z0-9_\-\.]+@[A-Za-z0-9_\-\.]{2,}\.[A-Za-z0-9]{2,}(\.[A-Za-z0-9])?/);
    if (!er.test($.trim( $.trim($('#user_email').val()) ))){
        $('#user_email').addClass('invalid');
        $('#user_email').focus();
        notify('<h1>E-mail inválido!</h1>');
        return false;                    
    }    
    $('#user_email').removeClass('invalid');
    
    if($.trim($('#user_login').val()) == "")
    {
        $('#user_login').addClass('invalid');
        $('#user_login').focus();
        return false;
    }
    $('#user_login').removeClass('invalid');
    
    
    if($.trim($('#user_password').val()) == "")
    {
        $('#user_password').addClass('invalid');
        $('#user_password').focus();
        return false;
    }
    $('#user_password').removeClass('invalid');
    
    if($.trim($('#user_passwordr').val()) == "")
    {
        $('#user_passwordr').addClass('invalid');
        $('#user_passwordr').focus();
        return false;
    }
    $('#user_passwordr').removeClass('invalid');
    
    if($.trim($('#user_passwordr').val()) != $.trim($('#user_password').val())){
        $('#user_passwordr').addClass('invalid');   
        $('#user_password').addClass('invalid');   
        notify('<h1>Senhas não conferem!</h1>');
        return false;
    }
}
function validaEdit()
{
    if($.trim($('#user_name').val()) == "")
    {
        $('#user_name').addClass('invalid');
        $('#user_name').focus();
        return false;
    }
    $('#user_name').removeClass('invalid');
    
    if($.trim($('#user_email').val()) == "")
    {
        $('#user_email').addClass('invalid');
        $('#user_email').focus();
        return false;
    }
    
    var er = new RegExp(/^[A-Za-z0-9_\-\.]+@[A-Za-z0-9_\-\.]{2,}\.[A-Za-z0-9]{2,}(\.[A-Za-z0-9])?/);
    if (!er.test($.trim( $.trim($('#user_email').val()) ))){
        $('#user_email').addClass('invalid');
        $('#user_email').focus();
        notify('<h1>E-mail inválido!</h1>');
        return false;                    
    }    
    $('#user_email').removeClass('invalid');
    
    if($.trim($('#user_login').val()) == "")
    {
        $('#user_login').addClass('invalid');
        $('#user_login').focus();
        return false;
    }
    $('#user_login').removeClass('invalid');
    
    if($.trim($('#user_password').val()) != ""){    
        if($.trim($('#user_password').val()) == "")
        {
            $('#user_password').addClass('invalid');
            $('#user_password').focus();
            return false;
        }
        $('#user_password').removeClass('invalid');
    
        if($.trim($('#user_passwordr').val()) == "")
        {
            $('#user_passwordr').addClass('invalid');
            $('#user_passwordr').focus();
            return false;
        }
        $('#user_passwordr').removeClass('invalid');
    
        if($.trim($('#user_passwordr').val()) != $.trim($('#user_password').val())){
            $('#user_passwordr').addClass('invalid');   
            $('#user_password').addClass('invalid');   
            notify('<h1>Senhas não conferem!</h1>');
            return false;
        }
    }

}


