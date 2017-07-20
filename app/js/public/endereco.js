//base location
var baseUri = $('base').attr('href').replace('/app/','');
$(function(){
    //se browser é <> IE
    if ( !$.browser.msie ) {
        $('.hide-elem').hide();
    }
    //autocompleta endereço
    $('#endereco_cep').live('keyup',function(e){
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
        var cep = $.trim($('#endereco_cep').val()).replace('_','');
        if(cep.length >= 9){
            $('#endereco_cep').blur();
            var cep = $.trim($('#endereco_cep').val());
            var url = baseUri+'/cep/getcep/';    
            $.post(url,{
                cep:cep
            },
            function (data) {
                if(data != -1){
                    data = $.parseJSON(data);
                    data = data.rs[0];
                    $('#endereco_rua').val(data.endereco);
                    $('#endereco_bairro').val(data.bairro);
                    $('#endereco_cidade').val(data.cidade);
                    $('#endereco_uf').val(data.uf.toUpperCase());
                    $('#endereco_cep').removeClass('invalid');
                    $('.hide-elem').fadeIn(500);
                    $('#endereco_num').focus();                    
                }
                else{
                    $('#endereco_cep').addClass('invalid');    
                    $('#endereco_cep').focus();  
                    $('.hide-elem').fadeOut();
                }
            })             
        }
    })  
})
