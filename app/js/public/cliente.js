//base location
var baseUri = $('base').attr('href').replace('/app/','');
$(function(){
    //se browser é <> IE
    if ( !$.browser.msie ) {
        $('.hide-elem').hide();
    }     
    
    //autocompleta endereço
    $('#cliente_cep').live('keyup',function(e){
        if (e.shiftKey || e.ctrlKey || e.altKey) { // if shift, ctrl or alt keys held down 
            e.preventDefault();         // Prevent character input 
        } else { 
            var n = e.keyCode; 
            if (!((n == 8)              // backspace 
                || (n == 46)                // delete 
                //|| (n >= 35 && n <= 40)     // arrow keys/home/end 
                || (n >= 48 && n <= 57)     // numbers on keyboard 
                || (n >= 96 && n <= 105))   // number on keypad 
            ) { 
                e.preventDefault();     // Prevent character input 
                return false;
            } 
        }         
        //consulta CEP webservices
        var cep = $.trim($('#cliente_cep').val()).replace('_','');
        if(cep.length >= 9){
            $('#cliente_cep').blur();
            var cep = $.trim($('#cliente_cep').val());
            var url = baseUri+'/cep/getcep/';    
            $.post(url,{
                cep:cep
            },
            function (data) {
                if(data != '-1'){
                    data = $.parseJSON(data);
                    data = data.rs[0];
                    $('#cliente_bairro').val(data.bairro);
                    $('#cliente_cidade').val(data.cidade);
                    $('#cliente_uf').val(data.uf);
                    $('#cliente_cep').removeClass('invalid');
                    $('.hide-elem').fadeIn(500);
                    $('#cliente_num').focus();                    
                    if(data.cep_unico != 1){
                        $('#cliente_rua').val(data.endereco);
                    }else{
                        $('#cliente_rua').val('CEP único - informe o nome da rua');
                        $('#cliente_rua').focus();
                        $('#cliente_rua').select();
                    }                    
                }
                else{
                    $('#cliente_cep').addClass('invalid');    
                    $('#cliente_cep').focus();  
                    $('.hide-elem').fadeOut();
                }
            })             
        }
    })  
    //verifica se o nome está incompleto
    $('#cliente_nome').live('change',function(e){
        valid = true;
        var elm = $('#cliente_nome');
        elm.removeClass('invalid').parent().find('span').html('');
        e.preventDefault();
        var nome = $.trim( elm.val() );
        var url = baseUri + '/cliente/checkNome/';
        $.post(url,{
            nome:nome
        },function(data){
            if(data == 1){
                $('html, body').animate({
                    scrollTop: elm.offset().top - 300
                }, 800,function(){
                    elm.addClass('invalid').parent().find('span').html('* Nome Incompleto');
                    elm.focus();  
                    valid = false;
                });                 
            }
        })
    })
    
    //verifica existencia de cadastro com mesmo CPF
    $('#cliente_cpf').live('change',function(e){
        valid = true;
        var elm = $('#cliente_cpf');
        elm.removeClass('invalid').parent().find('span').html('');
        e.preventDefault();
        var cpf = elm.val();
        var url = baseUri + '/cliente/checkPreExistCPF/';
        $.post(url,{
            cliente_cpf:cpf
        },function(data){
            if(data == 2){
                $('html, body').animate({
                    scrollTop: elm.offset().top - 300
                }, 800,function(){
                    elm.addClass('invalid').parent().find('span').html('* CPF Inválido');
                    elm.focus();  
                    elm.val('');
                    valid = false;
                });                 
            }
            if(data == 1){
                $('html, body').animate({
                    scrollTop: elm.offset().top - 300
                }, 800,function(){
                    elm.addClass('invalid').parent().find('span').html('* CPF já cadastrado');
                    elm.focus();
                    elm.val('');
                    valid = false;                    
                });                 
            }
        })
    })
    //verifica existencia de cadastro com mesmo Email
    $('#cliente_email').live('change',function(e){
        valid = true;
        var elm = $('#cliente_email');
        elm.removeClass('invalid').parent().find('span').html('');
        e.preventDefault();
        var email = elm.val();
        var url = baseUri + '/cliente/checkPreExistEmail/';
        $.post(url,{
            cliente_email:email
        },function(data){
            if(data == 2){
                $('html, body').animate({
                    scrollTop: elm.offset().top - 300
                }, 800,function(){
                    elm.addClass('invalid').parent().find('span').html('* E-mail Inválido');
                    elm.focus();  
                    elm.val('');
                    valid = false;
                });                 
            }
            if(data == 1){
                $('html, body').animate({
                    scrollTop: elm.offset().top - 300
                }, 800,function(){
                    elm.addClass('invalid').parent().find('span').html('* E-mail já cadastrado');
                    elm.focus();
                    elm.val('');
                    valid = false;                    
                });                 
            }
        })
    })
    //remove addr
    var eid;
    $('#btn-remove-confirm').live('click',function(){
        window.location = baseUri + '/cliente/enderecoRemove/'+eid+'/';
    })
    $('.addr-remove').live('click',function(){
        var url = baseUri + '/cliente/enderecoVSpedido/';    
        eid = $(this).attr('id');
        $.post(url,{
            eid:eid
        },
        function(data) {
            if(data == 1){
                $('#modal-remove-confirm').modal('show');
            }else{
                $('#modal-remove').modal('show');
            }
        })    
    })    
})
