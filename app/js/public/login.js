$(function(){
    //identificacao login-cadastro
    $('.cad').live('click',function(){
        $(this).each(function(){
            if($(this).attr('checked') == 'checked'){
                if($(this).attr('id') == 'cadastrar'){
                    $('#cliente_password').removeAttr('required');
                    $('#cliente_password').attr('disabled','disabled');
                }else{
                    $('#cliente_password').removeAttr('disabled');
                    $('#cliente_password').attr('required');
                }
            }
        })
    }) 
}) 


