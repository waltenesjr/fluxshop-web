$(function(){
    //baseUri
    $('head').append('<script src="js/default/baseuri.js" type="text/javascript"></script>');
    $('#btn-add').live('click',function(){
        $('#f-item').submit();
    })
    //remover item
    $('.remove').live('click',function(e){
        e.preventDefault();
        var id = $(this).attr('id');
        $('#modal-remove').modal('show');
        var url = baseUri +'/admin/retirada/remover/'+id+'/';
        $('#btn-remove').attr('href',url);
    })      

    if ( !$.browser.msie ) {
        $('.hidden').hide();
    }
    $('#retirada_cep').live('keyup',function(e){
        if (e.shiftKey || e.ctrlKey || e.altKey) { 
            e.preventDefault();         
        } else { 
            var n = e.keyCode; 
            if (!((n == 8)              
                || (n == 46)             
                //|| (n >= 35 && n <= 40)
                || (n >= 48 && n <= 57)  
                || (n >= 96 && n <= 105))
            ) { 
                e.preventDefault();     
                return false;
            } 
        }         
        var cep = $.trim($('#retirada_cep').val()).replace('_','');
        if(cep.length >= 9){
            $('#retirada_cep').blur();
            var baseUri = $('base').attr('href').replace('/app/','');
            var url = baseUri+'/cep/getcep/';   
            
            $.post(url,{
                cep:cep
            },
            function (data) {
                if(data != -1){
                    data = $.parseJSON(data);
                    data = data.rs[0];
                    $('#retirada_bairro').val(data.bairro);
                    $('#retirada_cidade').val(data.cidade);
                    $('#retirada_uf').val(data.uf);
                    $('#retirada_cep').removeClass('invalid');
                    $('.hidden').fadeIn(500);
                    $('#retirada_num').focus();
                    if(data.cep_unico != 1){
                        $('#retirada_rua').val(data.endereco);
                    }else{
                        $('#retirada_rua').val('Cep Único - Informe o nome da rua');
                        $('#retirada_rua').focus();
                        $('#retirada_rua').select();
                    }                    
                    
                }
                else{
                    $('#retirada_cep').addClass('invalid');    
                    $('#retirada_cep').focus();  
                    $('.hidden').fadeOut();
                }
            })             
        }
    }) 
    
})
